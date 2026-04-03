<?php
declare(strict_types=1);
namespace App\Listeners\Work;
use App\Events\Work\FieldServiceProjectCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
class FieldServiceProjectCreatedListener implements ShouldQueue
{
    public string $queue = 'default';
    public function handle(FieldServiceProjectCreated $event): void
    {
        // Project created - log/notify as needed
    }
}
