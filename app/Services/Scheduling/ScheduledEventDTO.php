<?php

declare(strict_types=1);

namespace App\Services\Scheduling;

/**
 * Normalized scheduling surface event object.
 *
 * Produced by SchedulingSurfaceProvider from any SchedulableEntity implementation
 * so that dispatch boards, calendars, and CRM timelines can render events
 * without knowing the concrete entity type.
 */
final class ScheduledEventDTO
{
    public function __construct(
        /** Unique string key — "{type}:{id}" */
        public readonly string $key,

        /** Concrete entity type (FQCN) */
        public readonly string $entityType,

        /** Primary key of the source entity */
        public readonly int $entityId,

        /** Human-readable label for display */
        public readonly string $title,

        /** ISO-8601 start datetime or null */
        public readonly ?string $scheduledStart,

        /** ISO-8601 end datetime or null */
        public readonly ?string $scheduledEnd,

        /** Assigned user ID or null */
        public readonly ?int $assignedUserId,

        /** Status string from the source entity */
        public readonly string $status,

        /** Priority label/number or null */
        public readonly string|int|null $priority,

        /** Optional premises_id for surface filtering */
        public readonly ?int $premisesId = null,

        /** Optional customer_id for surface filtering */
        public readonly ?int $customerId = null,
    ) {}

    /**
     * Serialize to an array suitable for JSON responses.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key'             => $this->key,
            'entity_type'     => $this->entityType,
            'entity_id'       => $this->entityId,
            'title'           => $this->title,
            'scheduled_start' => $this->scheduledStart,
            'scheduled_end'   => $this->scheduledEnd,
            'assigned_user_id' => $this->assignedUserId,
            'status'          => $this->status,
            'priority'        => $this->priority,
            'premises_id'     => $this->premisesId,
            'customer_id'     => $this->customerId,
        ];
    }
}
