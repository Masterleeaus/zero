<?php
declare(strict_types=1);
namespace App\Listeners\Work;
use App\Events\Work\FieldServiceProjectCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
class FieldServiceProjectCompletedListener implements ShouldQueue
{
    public string $queue = 'default';
    public function handle(FieldServiceProjectCompleted $event): void
    {
        // Project completed - log/notify as needed
    }
}
