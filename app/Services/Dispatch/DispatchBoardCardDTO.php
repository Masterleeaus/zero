<?php

declare(strict_types=1);

namespace App\Services\Dispatch;

/**
 * DispatchBoardCardDTO — normalized card representation for the EasyDispatch
 * dispatch board.
 *
 * Produced by DispatchBoardEventAdapter from schedulable entities so that
 * the board can render jobs, visits, inspections, and checklists uniformly.
 */
final class DispatchBoardCardDTO
{
    public function __construct(
        /** Unique card key — "{type}:{id}" */
        public readonly string $key,

        /** Entity type (FQCN) */
        public readonly string $entityType,

        /** Primary key of the source entity */
        public readonly int $entityId,

        /** Card title for dispatch board display */
        public readonly string $title,

        /** Card category label (e.g. "Job", "Visit", "Inspection", "Checklist") */
        public readonly string $category,

        /** ISO-8601 scheduled start datetime or null */
        public readonly ?string $scheduledStart,

        /** ISO-8601 scheduled end datetime or null */
        public readonly ?string $scheduledEnd,

        /** Assigned user ID or null */
        public readonly ?int $assignedUserId,

        /** Status string */
        public readonly string $status,

        /** Optional premises ID for geo-routing */
        public readonly ?int $premisesId = null,

        /** Optional customer ID */
        public readonly ?int $customerId = null,

        /** Optional route stop ID when part of a dispatch route */
        public readonly ?int $routeStopId = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key'              => $this->key,
            'entity_type'      => $this->entityType,
            'entity_id'        => $this->entityId,
            'title'            => $this->title,
            'category'         => $this->category,
            'scheduled_start'  => $this->scheduledStart,
            'scheduled_end'    => $this->scheduledEnd,
            'assigned_user_id' => $this->assignedUserId,
            'status'           => $this->status,
            'premises_id'      => $this->premisesId,
            'customer_id'      => $this->customerId,
            'route_stop_id'    => $this->routeStopId,
        ];
    }
}
