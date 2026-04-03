<?php

namespace App\Console\Commands;

use App\Helper\Common;
use App\Helper\Files;
use App\Models\Service Job;
use App\Models\TaskComment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ConvertTaskBase64Images extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service jobs:convert-base64-images {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert base64 images in service job descriptions and service job comments to uploaded files';

    const FILE_PATH = 'quill-images';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Running in DRY-RUN mode. No changes will be made.');
        }

        $this->info('Starting to process service jobs and service job comments...');

        $processedTasksCount = 0;
        $updatedTasksCount = 0;
        $processedCommentsCount = 0;
        $updatedCommentsCount = 0;
        $errorCount = 0;

        // Process service jobs in chunks to avoid memory issues
        $this->info("\n--- Processing Service Jobs ---");
        Service Job::whereNotNull('description')
            ->where('description', 'like', '%data:image%')
            ->chunk(50, function ($service jobs) use (&$processedTasksCount, &$updatedTasksCount, &$errorCount, $dryRun) {
                foreach ($service jobs as $service job) {
                    $processedTasksCount++;

                    try {
                        $updated = $this->processTask($service job, $dryRun);

                        if ($updated) {
                            $updatedTasksCount++;
                            $this->info("Processed service job ID: {$service job->id} - Updated");
                        } else {
                            $this->line("Processed service job ID: {$service job->id} - No changes needed");
                        }
                    } catch (\Exception $e) {
                        $errorCount++;
                        $this->error("Error processing service job ID: {$service job->id} - {$e->getMessage()}");
                    }
                }
            });

        // Process service job comments in chunks
        $this->info("\n--- Processing Service Job Comments ---");
        TaskComment::whereNotNull('comment')
            ->where('comment', 'like', '%data:image%')
            ->with('service job') // Load service job relationship to get company_id
            ->chunk(50, function ($comments) use (&$processedCommentsCount, &$updatedCommentsCount, &$errorCount, $dryRun) {
                foreach ($comments as $comment) {
                    $processedCommentsCount++;

                    try {
                        $updated = $this->processTaskComment($comment, $dryRun);

                        if ($updated) {
                            $updatedCommentsCount++;
                            $this->info("Processed comment ID: {$comment->id} - Updated");
                        } else {
                            $this->line("Processed comment ID: {$comment->id} - No changes needed");
                        }
                    } catch (\Exception $e) {
                        $errorCount++;
                        $this->error("Error processing comment ID: {$comment->id} - {$e->getMessage()}");
                    }
                }
            });

        $this->info("\n=== Summary ===");
        $this->info("Service Jobs processed: {$processedTasksCount}");
        $this->info("Service Jobs updated: {$updatedTasksCount}");
        $this->info("Comments processed: {$processedCommentsCount}");
        $this->info("Comments updated: {$updatedCommentsCount}");
        $this->info("Total errors: {$errorCount}");

        if ($dryRun) {
            $this->warn("\nThis was a DRY-RUN. No changes were made.");
        }

        return Command::SUCCESS;
    }

    /**
     * Process a single service job
     *
     * @param Service Job $service job
     * @param bool $dryRun
     * @return bool
     */
    protected function processTask(Service Job $service job, bool $dryRun): bool
    {
        $description = $service job->description;

        if (empty($description) || strpos($description, 'data:image') === false) {
            return false;
        }

        // Use regex to find all base64 images in the description
        // This is more reliable than DOMDocument for potentially malformed HTML
        $pattern = '/<img[^>]+src=["\'](data:image\/(\w+);base64,([^"\']+))["\'][^>]*>/i';
        $updated = false;
        $modifiedDescription = $description;

        preg_match_all($pattern, $description, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        if (empty($matches)) {
            return false;
        }

        // Process matches in reverse order to maintain offsets
        $matches = array_reverse($matches);

        foreach ($matches as $match) {
            $fullMatch = $match[0][0];
            $fullSrc = $match[1][0];
            $imageType = $match[2][0];
            $base64Data = $match[3][0];

            $this->line("  Found base64 image (type: {$imageType}) in service job ID: {$service job->id}");

            if (!$dryRun) {
                // Decode base64 data
                $imageData = base64_decode($base64Data, true);

                if ($imageData === false) {
                    $this->warn("  Failed to decode base64 data for service job ID: {$service job->id}");
                    continue;
                }

                // Generate filename
                $extension = $imageType === 'jpeg' ? 'jpg' : $imageType;
                $tempFilename = "temp_image_" . uniqid() . ".{$extension}";

                // Create temp directory if it doesn't exist
                $tempDir = public_path(Files::UPLOAD_FOLDER . '/temp');
                if (!file_exists($tempDir)) {
                    mkdir($tempDir, 0775, true);
                }

                // Save to temp file
                $tempPath = $tempDir . '/' . $tempFilename;
                file_put_contents($tempPath, $imageData);

                // Create UploadedFile instance to match ImageController pattern
                $uploadedFile = new UploadedFile(
                    $tempPath,
                    "image.{$extension}",
                    "image/{$imageType}",
                    null,
                    true // test mode
                );

                // Use the same upload method as ImageController
                $filename = Files::uploadLocalOrS3($uploadedFile, self::FILE_PATH);

                // Clean up temp file
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }

                // Update file storage record with company_id if available
                if ($service job->company_id) {
                    $fileStorage = \App\Models\FileStorage::where('filename', $filename)->first();
                    if ($fileStorage) {
                        $fileStorage->company_id = $service job->company_id;
                        $fileStorage->save();
                    }
                }

                // Generate encrypted filename for URL (same as ImageController)
                $encrypted = Common::encryptDecrypt($filename);
                $newUrl = route('image.getImage', $encrypted);

                // Replace the full img tag's src attribute
                $newImgTag = preg_replace(
                    '/src=["\']data:image\/\w+;base64,[^"\']+["\']/i',
                    'src="' . $newUrl . '"',
                    $fullMatch
                );

                // Replace in description
                $modifiedDescription = str_replace($fullMatch, $newImgTag, $modifiedDescription);

                $this->line("  Uploaded image: {$filename}");
            } else {
                $this->line("  [DRY-RUN] Would upload base64 image");
            }

            $updated = true;
        }

        // Update service job description if changes were made
        if ($updated && !$dryRun && $modifiedDescription !== $description) {
            $service job->description = $modifiedDescription;
            $service job->save();
        }

        return $updated;
    }

    /**
     * Process a single service job comment
     *
     * @param TaskComment $comment
     * @param bool $dryRun
     * @return bool
     */
    protected function processTaskComment(TaskComment $comment, bool $dryRun): bool
    {
        $commentText = $comment->comment;

        if (empty($commentText) || strpos($commentText, 'data:image') === false) {
            return false;
        }

        // Use regex to find all base64 images in the comment
        $pattern = '/<img[^>]+src=["\'](data:image\/(\w+);base64,([^"\']+))["\'][^>]*>/i';
        $updated = false;
        $modifiedComment = $commentText;

        preg_match_all($pattern, $commentText, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        if (empty($matches)) {
            return false;
        }

        // Process matches in reverse order to maintain offsets
        $matches = array_reverse($matches);

        // Get company_id from related service job
        $companyId = $comment->service job ? $comment->service job->company_id : null;

        foreach ($matches as $match) {
            $fullMatch = $match[0][0];
            $fullSrc = $match[1][0];
            $imageType = $match[2][0];
            $base64Data = $match[3][0];

            $this->line("  Found base64 image (type: {$imageType}) in comment ID: {$comment->id}");

            if (!$dryRun) {
                // Decode base64 data
                $imageData = base64_decode($base64Data, true);

                if ($imageData === false) {
                    $this->warn("  Failed to decode base64 data for comment ID: {$comment->id}");
                    continue;
                }

                // Generate filename
                $extension = $imageType === 'jpeg' ? 'jpg' : $imageType;
                $tempFilename = "temp_image_" . uniqid() . ".{$extension}";

                // Create temp directory if it doesn't exist
                $tempDir = public_path(Files::UPLOAD_FOLDER . '/temp');
                if (!file_exists($tempDir)) {
                    mkdir($tempDir, 0775, true);
                }

                // Save to temp file
                $tempPath = $tempDir . '/' . $tempFilename;
                file_put_contents($tempPath, $imageData);

                // Create UploadedFile instance to match ImageController pattern
                $uploadedFile = new UploadedFile(
                    $tempPath,
                    "image.{$extension}",
                    "image/{$imageType}",
                    null,
                    true // test mode
                );

                // Use the same upload method as ImageController
                $filename = Files::uploadLocalOrS3($uploadedFile, self::FILE_PATH);

                // Clean up temp file
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }

                // Update file storage record with company_id if available
                if ($companyId) {
                    $fileStorage = \App\Models\FileStorage::where('filename', $filename)->first();
                    if ($fileStorage) {
                        $fileStorage->company_id = $companyId;
                        $fileStorage->save();
                    }
                }

                // Generate encrypted filename for URL (same as ImageController)
                $encrypted = Common::encryptDecrypt($filename);
                $newUrl = route('image.getImage', $encrypted);

                // Replace the full img tag's src attribute
                $newImgTag = preg_replace(
                    '/src=["\']data:image\/\w+;base64,[^"\']+["\']/i',
                    'src="' . $newUrl . '"',
                    $fullMatch
                );

                // Replace in comment
                $modifiedComment = str_replace($fullMatch, $newImgTag, $modifiedComment);

                $this->line("  Uploaded image: {$filename}");
            } else {
                $this->line("  [DRY-RUN] Would upload base64 image");
            }

            $updated = true;
        }

        // Update comment if changes were made
        if ($updated && !$dryRun && $modifiedComment !== $commentText) {
            $comment->comment = $modifiedComment;
            $comment->save();
        }

        return $updated;
    }
}
