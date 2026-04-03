<?php

declare(strict_types=1);

namespace App\Events\Team;

use App\Models\Team\TechnicianSkill;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SkillAssigned
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly TechnicianSkill $technicianSkill) {}
}
