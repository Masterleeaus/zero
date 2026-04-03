<?php

namespace App\Titan\Signals;

use Illuminate\Support\Facades\DB;

class SignalValidator
{
    public function __construct(
        protected ?SignalRegistry $registry = null,
    ) {
        $this->registry ??= app(SignalRegistry::class);
    }

    public function validate(array $signal): array
    {
        $errors = [];
        $warnings = [];
        $type = (string) ($signal['type'] ?? '');
        $definition = $this->registry->definitionFor($type);

        foreach (['company_id', 'type', 'kind', 'payload'] as $required) {
            if (! array_key_exists($required, $signal) || $signal[$required] === null || $signal[$required] === '') {
                $errors[] = "Missing required field: {$required}";
            }
        }

        if ($type !== '' && $definition === []) {
            $warnings[] = 'Signal type is not registered in titan_signal registry.';
        }

        $allowedSeverities = $this->registry->allowedSeverities($type);
        if (! empty($signal['severity']) && ! in_array($signal['severity'], $allowedSeverities, true)) {
            $errors[] = 'Invalid severity supplied for signal type.';
        }

        if (! empty($signal['user_id']) && empty(data_get($signal, 'meta.permission_checked'))) {
            $warnings[] = 'Authorization not explicitly confirmed.';
        }

        if (! empty($signal['process_id'])) {
            $duplicate = DB::table('tz_signals')
                ->where('process_id', $signal['process_id'])
                ->where('type', $type)
                ->exists();

            if ($duplicate) {
                $warnings[] = 'Similar signal already exists for this process.';
            }
        }

        $requiredPayloadFields = array_values(array_unique(array_merge(
            (array) data_get($signal, 'meta.required_payload_fields', []),
            $this->registry->requiredPayloadFields($type)
        )));

        foreach ($requiredPayloadFields as $field) {
            if (data_get($signal, 'payload.'.$field) === null) {
                $errors[] = "Missing required payload field: {$field}";
            }
        }

        $allowedStates = (array) data_get($definition, 'allowed_process_states', []);
        if ($allowedStates !== [] && ! empty($signal['process_id'])) {
            $process = DB::table('tz_processes')->where('id', $signal['process_id'])->first();
            if ($process && ! in_array((string) $process->current_state, $allowedStates, true)) {
                $warnings[] = 'Signal arrived from unexpected process state.';
            }
        }

        if ($type === 'invoice.overdue' && ((int) data_get($signal, 'payload.amount_cents', 0)) <= 0) {
            $warnings[] = 'Overdue invoice signal has no positive amount.';
        }

        if ($type === 'job.completed' && empty(data_get($signal, 'payload.completed_at'))) {
            $warnings[] = 'Completed job signal should include completed_at.';
        }

        if ($type === 'quote.accepted' && empty(data_get($signal, 'payload.customer_id'))) {
            $warnings[] = 'Accepted quote should carry customer_id for booking handoff.';
        }

        return [
            'result' => $errors ? 'REJECTED' : ($warnings ? 'APPROVED_WITH_WARNINGS' : 'APPROVED'),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }
}
