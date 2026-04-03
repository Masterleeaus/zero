<?php
declare(strict_types=1);
namespace App\Events\Work;
use App\Models\Work\FieldServiceProject;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class FieldServiceProjectCompleted
{
    use Dispatchable, SerializesModels;
    public function __construct(public readonly FieldServiceProject $project) {}
}
