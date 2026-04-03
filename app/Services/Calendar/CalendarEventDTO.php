<?php

declare(strict_types=1);

namespace App\Services\Calendar;

/**
 * CalendarEventDTO — normalized calendar event for the Business Suite calendar.
 *
 * Produced by BusinessSuiteCalendarAdapter from ScheduledEventDTO objects.
 */
final class CalendarEventDTO
{
    public function __construct(
        /** Unique key — mirrors ScheduledEventDTO::$key */
        public readonly string $key,

        /** Human-readable event title */
        public readonly string $title,

        /** ISO-8601 start datetime */
        public readonly ?string $start,

        /** ISO-8601 end datetime */
        public readonly ?string $end,

        /** Source entity type (FQCN) */
        public readonly string $entityType,

        /** Source entity primary key */
        public readonly int $entityId,

        /** CSS colour class or hex colour for calendar rendering */
        public readonly string $color,

        /** Current status */
        public readonly string $status,

        /** Optional user ID for assignment display */
        public readonly ?int $assignedUserId = null,

        /** Optional premises ID */
        public readonly ?int $premisesId = null,

        /** Optional customer ID */
        public readonly ?int $customerId = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key'              => $this->key,
            'title'            => $this->title,
            'start'            => $this->start,
            'end'              => $this->end,
            'entity_type'      => $this->entityType,
            'entity_id'        => $this->entityId,
            'color'            => $this->color,
            'status'           => $this->status,
            'assigned_user_id' => $this->assignedUserId,
            'premises_id'      => $this->premisesId,
            'customer_id'      => $this->customerId,
        ];
    }
}
