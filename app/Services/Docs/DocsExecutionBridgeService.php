<?php

declare(strict_types=1);

namespace App\Services\Docs;

use App\Events\Docs\DocumentsInjectedForJob;
use App\Events\Docs\MandatoryDocumentAcknowledged;
use App\Models\Inspection\InspectionInstance;
use App\Models\Premises\DocumentInjectionRule;
use App\Models\Premises\FacilityDocument;
use App\Models\Premises\InspectionInjectedDocument;
use App\Models\Premises\JobInjectedDocument;
use App\Models\User;
use App\Models\Work\ServiceJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * DocsExecutionBridgeService
 *
 * Injects relevant procedure documents, safety instructions, compliance
 * checklists, and regulatory requirements into service jobs and inspections
 * at the point of execution.
 *
 * Rule-based injection is synchronous.
 * AI relevance scoring is deferred (queued via DocumentSearchService).
 */
class DocsExecutionBridgeService
{
    public function __construct(
        private readonly DocumentSearchService $searchService,
    ) {}

    /**
     * Inject documents for a service job.
     * Returns the collection of JobInjectedDocument records created.
     */
    public function injectForJob(ServiceJob $job): Collection
    {
        $injected = $this->applyRuleBasedInjection($job);

        if ($injected->isNotEmpty()) {
            DocumentsInjectedForJob::dispatch($job, $injected);
        }

        return $injected;
    }

    /**
     * Inject documents for an inspection instance.
     * Returns the collection of InspectionInjectedDocument records created.
     */
    public function injectForInspection(InspectionInstance $inspection): Collection
    {
        return $this->applyRuleBasedInjection($inspection);
    }

    /**
     * Run rule-based injection for a job or inspection context.
     *
     * Matches DocumentInjectionRule records against the context's attributes:
     *   - job_type_id  → rule_type=job_type
     *   - asset_type   (via site assets on the premises) → rule_type=asset_type
     *   - service_type → rule_type=service_type
     *   - access_level → rule_type=access_level
     *   - premises zone / maintenance_zone → rule_type=premises_zone
     */
    public function applyRuleBasedInjection(Model $context): Collection
    {
        $companyId = $context->company_id;

        if (! $companyId) {
            return collect();
        }

        $ruleValues = $this->extractRuleValues($context);

        if (empty($ruleValues)) {
            return collect();
        }

        /** @var Collection<DocumentInjectionRule> $matchedRules */
        $matchedRules = DocumentInjectionRule::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where(static function ($q) use ($ruleValues) {
                foreach ($ruleValues as $type => $value) {
                    if ($value === null || $value === '') {
                        continue;
                    }
                    $q->orWhere(static function ($sub) use ($type, $value) {
                        $sub->where('rule_type', $type)
                            ->where('rule_value', (string) $value);
                    });
                }
            })
            ->with('document')
            ->get();

        $results = collect();

        foreach ($matchedRules as $rule) {
            if (! $rule->document) {
                continue;
            }

            $pivot = $this->persistInjectedDocument(
                context: $context,
                document: $rule->document,
                source: 'rule',
                isMandatory: $rule->is_mandatory,
            );

            if ($pivot !== null) {
                $results->push($pivot);
            }
        }

        return $results;
    }

    /**
     * Apply AI relevance scoring to a set of candidate documents.
     *
     * Scores are fetched from the DocumentSearchService (which can use
     * the Entity/Anthropic driver). Each scored document above the threshold
     * is persisted as an ai_relevance injection.
     *
     * @param  Collection<FacilityDocument>  $candidates
     */
    public function applyAIRelevanceScoring(Model $context, Collection $candidates): Collection
    {
        if ($candidates->isEmpty()) {
            return collect();
        }

        $contextSummary = $this->buildContextSummary($context);
        $results        = collect();

        foreach ($candidates as $document) {
            try {
                $score = $this->searchService->scoreRelevance($document, $contextSummary);

                if ($score >= 0.5) {
                    $pivot = $this->persistInjectedDocument(
                        context: $context,
                        document: $document,
                        source: 'ai_relevance',
                        score: $score,
                        isMandatory: $document->is_mandatory,
                    );

                    if ($pivot !== null) {
                        $results->push($pivot);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('DocsExecutionBridge: AI scoring failed for document ' . $document->id, [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Mark a document as acknowledged by a user within a context (job or inspection).
     */
    public function acknowledgeDocument(
        int $documentId,
        int $contextId,
        string $contextType,
        User $user,
    ): void {
        if ($contextType === ServiceJob::class || $contextType === 'job') {
            $pivot = JobInjectedDocument::query()
                ->where('job_id', $contextId)
                ->where('document_id', $documentId)
                ->first();
        } else {
            $pivot = InspectionInjectedDocument::query()
                ->where('inspection_instance_id', $contextId)
                ->where('document_id', $documentId)
                ->first();
        }

        if (! $pivot || $pivot->acknowledged_at) {
            return;
        }

        $pivot->update([
            'acknowledged_by' => $user->id,
            'acknowledged_at' => now(),
        ]);

        if ($pivot->is_mandatory) {
            MandatoryDocumentAcknowledged::dispatch($pivot, $user);
        }
    }

    /**
     * Return mandatory injected documents that have NOT been acknowledged.
     */
    public function getMandatoryUnacknowledged(Model $context): Collection
    {
        if ($context instanceof ServiceJob) {
            return JobInjectedDocument::query()
                ->where('job_id', $context->id)
                ->where('is_mandatory', true)
                ->whereNull('acknowledged_at')
                ->with('document')
                ->get();
        }

        if ($context instanceof InspectionInstance) {
            return InspectionInjectedDocument::query()
                ->where('inspection_instance_id', $context->id)
                ->where('is_mandatory', true)
                ->whereNull('acknowledged_at')
                ->with('document')
                ->get();
        }

        return collect();
    }

    // ── Internal helpers ──────────────────────────────────────────────────────

    /**
     * Build a map of rule_type => rule_value from the context model.
     *
     * @return array<string, string|int|null>
     */
    private function extractRuleValues(Model $context): array
    {
        $values = [];

        if ($context instanceof ServiceJob) {
            if ($context->job_type_id) {
                $values['job_type'] = (string) $context->job_type_id;
            }

            if ($context->premises_id) {
                $premises = $context->premises;

                if ($premises) {
                    if ($premises->access_level) {
                        $values['access_level'] = (string) $premises->access_level;
                    }
                    if ($premises->maintenance_zone) {
                        $values['premises_zone'] = (string) $premises->maintenance_zone;
                    }

                    // Asset types on this premises
                    $assetTypes = $premises->siteAssets()
                        ->whereNotNull('asset_type')
                        ->distinct()
                        ->pluck('asset_type');

                    foreach ($assetTypes as $assetType) {
                        $values['asset_type'] = $assetType;
                        break; // handled below via loop — first match is enough for extraction
                    }
                }
            }
        } elseif ($context instanceof InspectionInstance) {
            if ($context->inspection_template_id) {
                $values['job_type'] = 'inspection_' . $context->inspection_template_id;
            }

            if ($context->scope_type && $context->scope_id) {
                // If scoped to a premises, pull access_level / zone
                if (str_contains($context->scope_type, 'Premises')) {
                    $premises = \App\Models\Premises\Premises::find($context->scope_id);
                    if ($premises) {
                        if ($premises->access_level) {
                            $values['access_level'] = (string) $premises->access_level;
                        }
                        if ($premises->maintenance_zone) {
                            $values['premises_zone'] = (string) $premises->maintenance_zone;
                        }
                    }
                }
            }
        }

        return $values;
    }

    /**
     * Persist a JobInjectedDocument or InspectionInjectedDocument pivot record.
     * Returns null if the record already exists (deduplication).
     */
    private function persistInjectedDocument(
        Model $context,
        FacilityDocument $document,
        string $source,
        float $score = 0.0,
        bool $isMandatory = false,
    ): JobInjectedDocument|InspectionInjectedDocument|null {
        if ($context instanceof ServiceJob) {
            $existing = JobInjectedDocument::query()
                ->where('job_id', $context->id)
                ->where('document_id', $document->id)
                ->first();

            if ($existing) {
                return null;
            }

            return JobInjectedDocument::create([
                'job_id'           => $context->id,
                'document_id'      => $document->id,
                'injection_source' => $source,
                'relevance_score'  => $score > 0 ? $score : null,
                'injected_at'      => now(),
                'is_mandatory'     => $isMandatory || $document->is_mandatory,
            ]);
        }

        if ($context instanceof InspectionInstance) {
            $existing = InspectionInjectedDocument::query()
                ->where('inspection_instance_id', $context->id)
                ->where('document_id', $document->id)
                ->first();

            if ($existing) {
                return null;
            }

            return InspectionInjectedDocument::create([
                'inspection_instance_id' => $context->id,
                'document_id'            => $document->id,
                'injection_source'       => $source,
                'relevance_score'        => $score > 0 ? $score : null,
                'injected_at'            => now(),
                'is_mandatory'           => $isMandatory || $document->is_mandatory,
            ]);
        }

        return null;
    }

    /**
     * Build a human-readable context summary string for AI scoring.
     */
    private function buildContextSummary(Model $context): string
    {
        if ($context instanceof ServiceJob) {
            return implode(' ', array_filter([
                'Job:',
                $context->title,
                $context->jobType?->name,
                $context->premises?->name,
                $context->notes,
            ]));
        }

        if ($context instanceof InspectionInstance) {
            return implode(' ', array_filter([
                'Inspection:',
                $context->title,
                $context->inspection_type,
                $context->notes,
            ]));
        }

        return '';
    }
}
