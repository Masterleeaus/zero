<?php

namespace Modules\FacilityManagement\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class AiClient
{
    public function generate(string $purpose, array $context): string
    {
        $provider = Config::get('facility.ai.provider', 'openai');
        if ($provider === 'openai') {
            $text = $this->callOpenAI($purpose, $context);
            if ($text) return $text;
        }
        return $this->fallback($purpose, $context);
    }

    protected function callOpenAI(string $purpose, array $context): ?string
    {
        $key = Config::get('facility.ai.openai.api_key');
        if (!$key) return null;
        $base = rtrim(Config::get('facility.ai.openai.base_url', 'https://api.openai.com/v1'), '/');
        $model = Config::get('facility.ai.openai.model', 'gpt-4o-mini');

        $system = 'You are a facilities operations assistant. Output concise, actionable bullet points.';
        $user = 'Purpose: ' . $purpose . "\nContext JSON: " . json_encode($context);

        try {
            $resp = Http::withToken($key)->acceptJson()->post($base.'/chat/completions',[
                'model'=>$model,
                'messages'=>[
                    ['role'=>'system','content'=>$system],
                    ['role'=>'user','content'=>$user],
                ],
                'temperature'=>0.2,
            ])->throw()->json();
            return Arr::get($resp,'choices.0.message.content');
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function fallback(string $purpose, array $context): string
    {
        $title = $context['title'] ?? ($context['name'] ?? 'Item');
        switch ($purpose) {
            case 'inspection-checklist':
                return "[DRAFT INSPECTION CHECKLIST] \n- Visual inspection\n- Safety devices check\n- Cleanliness & access\n- Functional tests\n- Photos & notes";
            case 'pm-plan':
                return "[DRAFT PM PLAN] \n- Interval: Quarterly\n- Tasks: Inspect → Service → Test\n- Spares: List SKUs\n- Risks: Top 3 + mitigations";
            case 'unit-description':
                return f"[DRAFT UNIT DESCRIPTION] {title}\n- Size, layout, amenities\n- Condition & recent works\n- Access & parking";
            case 'doc-summary':
                return "[DRAFT DOC SUMMARY] \n- Document type & scope\n- Issue/expiry dates\n- Outstanding actions";
            default:
                return "[DRAFT] Provide more context.";
        }
    }
}
