<?php

namespace Modules\Documents\Entities;
use App\Models\BaseModel;
use App\Models\User;
use App\Traits\HasCompany;

class Template extends BaseModel
{
    // region Properties
    use HasCompany;

    protected $table = 'letter_templates';

    protected $fillable = [
        'title', 'description','category'
    ];


    //endregion



    //endregion

    //region Custom Attributes

    /* ---------- */

    public function getEmployeeVariablesAttribute(): array
    {
        $employee = new User();
        return $employee->getVariables();
    }

    //endregion

    //region Relations

    /* ---------- */

    //endregion

    //region Custom Functions


    protected static function booted(): void
    {
        static::creating(function (self $tpl) {
            if ($tpl->position === null) {
                try {
                    $tenantId = documents_tenant_id();
                    if ($tenantId !== null) {
                        $max = self::query()->withoutGlobalScopes()->where('tenant_id', $tenantId)->max('position');
                        $tpl->position = ($max ?? 0) + 1;
                    }
                } catch (\Throwable $e) {}
            }
        });
    }
}
