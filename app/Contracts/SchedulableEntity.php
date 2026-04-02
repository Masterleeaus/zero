<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * SchedulableEntity — shared interface for entities that appear on scheduling surfaces.
 *
 * Implemented by:
 *   - ServiceJob        (execution anchor)
 *   - ServicePlanVisit  (visit occurrence anchor)
 *   - InspectionInstance (compliance anchor — via InspectionSchedule)
 *   - ChecklistRun      (optional scheduled workflow execution)
 *
 * Stage E — Calendar Surface Unification
 *
 * Implementing models should expose these attributes/accessors so that the
 * WorkCore job board, FSM dispatch board, and Business Suite calendar surface
 * can render them through a unified interface without knowing the concrete type.
 */
interface SchedulableEntity
{
    /**
     * ISO-8601 datetime string (or null) for when the entity is scheduled to begin.
     */
    public function getScheduledStart(): ?string;

    /**
     * ISO-8601 datetime string (or null) for when the entity is scheduled to end.
     */
    public function getScheduledEnd(): ?string;

    /**
     * The ID of the user assigned to execute this entity (or null).
     */
    public function getAssignedUserId(): ?int;

    /**
     * Current status string (e.g. pending | scheduled | in_progress | completed | cancelled).
     */
    public function getSchedulableStatus(): string;

    /**
     * Priority label or numeric value for display ordering (or null).
     */
    public function getSchedulablePriority(): string|int|null;

    /**
     * Human-readable label for display on scheduling surfaces.
     */
    public function getSchedulableTitle(): string;

    /**
     * Fully-qualified class name of this schedulable entity (used for polymorphic routing).
     */
    public function getSchedulableType(): string;
}
