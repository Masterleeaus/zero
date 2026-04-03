<?php

namespace App\Extensions\AIImagePro\System\Jobs;

use App\Extensions\AIImagePro\System\Models\AiImageProModel;
use App\Services\Ai\AIImageClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PollImageGenerationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 20; // Maximum attempts

    public int $backoff = 15; // Seconds between retries

    public function __construct(
        private int $recordId,
        private readonly string $requestId
    ) {}

    public function handle(): void
    {
        $record = AiImageProModel::find($this->recordId);
        if (! $record) {
            return;
        }

        $result = AIImageClient::checkStatus($this->requestId, $record->model);

        if (isset($result['image']['url'])) {
            $this->handleCompleted($record, $result);

            return;
        }

        if (isset($result['status']) && $result['status'] === 'FAILED') {
            $error = is_array($result['error']) ? collect($result['error'])->pluck('msg')->join('; ') : ($result['error'] ?? null);

            if ($error !== 'Request is still in progress') {
                $record->markAsFailed($error ?? __('Image generation failed.'));

                return;
            }
        }

        if ($this->attempts() >= $this->tries) {
            $record->markAsFailed(__('Maximum polling attempts reached.'));

            return;
        }

        $this->release($this->backoff);
    }

    private function handleCompleted(AiImageProModel $record, array $result): void
    {
        $imageUrl = $result['image']['url'];
        $imageData = file_get_contents($imageUrl);

        if ($imageData === false) {
            $record->markAsFailed(__('Failed to download generated image.'));

            return;
        }

        $extension = mimeToExtension($result['image']['content_type'] ?? 'image/png');
        $name = uniqid('img_', true) . '.' . $extension;
        $directory = $record->user_id
            ? "media/images/u-{$record->user_id}"
            : 'guest';

        $filename = "{$directory}/{$name}";
        Storage::disk('public')->put($filename, $imageData);

        $record->saveDimensions($filename);

        $record->markAsCompleted(
            ["/uploads/{$filename}"],
            [
                'model'  => $record->model,
                'count'  => $record->params['image_count'] ?? 1,
                'params' => $record->params,
            ]
        );
    }
}
