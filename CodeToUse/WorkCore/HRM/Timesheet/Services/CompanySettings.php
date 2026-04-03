<?php

namespace Modules\Timesheet\Services;

use Modules\Timesheet\Entities\TimesheetCompanySetting;

class CompanySettings
{
    public function get(int $companyId, string $key, $default = null)
    {
        $row = TimesheetCompanySetting::query()
            ->where('company_id', $companyId)
            ->where('key', $key)
            ->first();

        if (!$row) {
            return $default;
        }

        return $row->value ?? $default;
    }

    public function set(int $companyId, string $key, $value): void
    {
        TimesheetCompanySetting::query()->updateOrCreate(
            ['company_id' => $companyId, 'key' => $key],
            ['value' => is_scalar($value) || $value === null ? (string) $value : json_encode($value)]
        );
    }

    public function bool(int $companyId, string $key, bool $default = false): bool
    {
        $v = $this->get($companyId, $key, $default ? '1' : '0');
        return in_array((string) $v, ['1', 'true', 'yes', 'on'], true);
    }

    public function int(int $companyId, string $key, int $default = 0): int
    {
        return (int) $this->get($companyId, $key, (string) $default);
    }
}
