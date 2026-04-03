<?php
declare(strict_types=1);
namespace App\Events\Work;
use App\Models\Work\FieldServiceProject;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class FieldServiceProjectJobLinked
{
    use Dispatchable, SerializesModels;
    public function __construct(public readonly FieldServiceProject $project, public readonly ServiceJob $job) {}
}
