<?php
declare(strict_types=1);
namespace App\Events\Work;
use App\Models\Work\ServicePlanVisit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class PortalVisitConfirmed
{
    use Dispatchable, SerializesModels;
    public function __construct(public readonly ServicePlanVisit $visit) {}
}
