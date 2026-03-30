<?php

namespace Modules\Workflow\Triggers\Contracts;

interface TriggerInterface
{
    /** Unique trigger key (e.g. inspection.completed or eloquent.created). */
    public function key(): string;

    /** Human label. */
    public function label(): string;

    /** Return an array schema describing available payload fields. */
    public function schema(): array;

    /** Return an example payload. */
    public function sample(): array;
}
