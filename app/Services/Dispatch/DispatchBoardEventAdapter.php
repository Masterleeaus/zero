<?php

declare(strict_types=1);

namespace App\Services\Dispatch;

use App\Contracts\SchedulableEntity;
use App\Models\Inspection\InspectionInstance;
use App\Models\Work\ChecklistRun;
use App\Models\Work\ServiceJob;
use App\Models\Work\ServicePlanVisit;
use App\Services\Scheduling\SchedulingSurfaceProvider;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * DispatchBoardEventAdapter — maps schedulable entities into DispatchBoardCardDTO
 * objects for the EasyDispatch dispatch board.
 *
 * Ensures that ServiceJob, ServicePlanVisit, InspectionInstance, and ChecklistRun
 * all appear consistently on the dispatch board.
 */
class DispatchBoardEventAdapter
{
    /**
     * @var array<class-string, string>
     */
    private const CATEGORY_MAP = [
        ServiceJob::class         => 'Job',
        ServicePlanVisit::class   => 'Visit',
        InspectionInstance::class => 'Inspection',
        ChecklistRun::class       => 'Checklist',
    ];

    public function __construct(
        private readonly SchedulingSurfaceProvider $surfaceProvider,
    ) {}

    /**
     * Get all dispatch board cards for a date range.
     *
     * @return Collection<int, DispatchBoardCardDTO>
     */
    public function getCardsForRange(Carbon $from, Carbon $to): Collection
    {
        return $this->surfaceProvider
            ->getEventsForRange($from, $to)
            ->map(fn ($dto) => $this->toCard($dto));
    }

    /**
     * Get dispatch board cards assigned to a specific user.
     *
     * @return Collection<int, DispatchBoardCardDTO>
     */
    public function getCardsForUser(int $userId): Collection
    {
        return $this->surfaceProvider
            ->getEventsForUser($userId)
            ->map(fn ($dto) => $this->toCard($dto));
    }

    /**
     * Get dispatch board cards for a premises.
     *
     * @return Collection<int, DispatchBoardCardDTO>
     */
    public function getCardsForPremises(int $premisesId): Collection
    {
        return $this->surfaceProvider
            ->getEventsForPremises($premisesId)
            ->map(fn ($dto) => $this->toCard($dto));
    }

    /**
     * Convert a single SchedulableEntity directly to a DispatchBoardCardDTO.
     *
     * Used when an entity is created/updated and needs to be pushed to the board.
     */
    public function fromEntity(SchedulableEntity $entity, ?int $premisesId = null, ?int $customerId = null): DispatchBoardCardDTO
    {
        /** @var \Illuminate\Database\Eloquent\Model&SchedulableEntity $entity */
        $entityType = $entity->getSchedulableType();
        $category   = self::CATEGORY_MAP[$entityType] ?? 'Event';

        return new DispatchBoardCardDTO(
            key:            $entityType . ':' . $entity->getKey(),
            entityType:     $entityType,
            entityId:       (int) $entity->getKey(),
            title:          $entity->getSchedulableTitle(),
            category:       $category,
            scheduledStart: $entity->getScheduledStart(),
            scheduledEnd:   $entity->getScheduledEnd(),
            assignedUserId: $entity->getAssignedUserId(),
            status:         $entity->getSchedulableStatus(),
            premisesId:     $premisesId,
            customerId:     $customerId,
        );
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function toCard(\App\Services\Scheduling\ScheduledEventDTO $dto): DispatchBoardCardDTO
    {
        $category = self::CATEGORY_MAP[$dto->entityType] ?? 'Event';

        return new DispatchBoardCardDTO(
            key:            $dto->key,
            entityType:     $dto->entityType,
            entityId:       $dto->entityId,
            title:          $dto->title,
            category:       $category,
            scheduledStart: $dto->scheduledStart,
            scheduledEnd:   $dto->scheduledEnd,
            assignedUserId: $dto->assignedUserId,
            status:         $dto->status,
            premisesId:     $dto->premisesId,
            customerId:     $dto->customerId,
        );
    }
}
