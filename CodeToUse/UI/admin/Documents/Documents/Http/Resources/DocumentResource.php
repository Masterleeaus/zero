<?php

namespace Modules\Documents\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'title' => $this->title,
            'type' => $this->type,
            'status' => $this->status,
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
