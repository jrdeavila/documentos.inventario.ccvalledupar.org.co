<?php

namespace App\Http\Resources;

use App\MregEstIncritosStatus;
use App\Services\MregEstInscritosQuery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MregEstInscritoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = array_first(array_filter(MregEstIncritosStatus::cases(), fn($status) => $status->name === $this->status));
        return [
            'id' => $this->id,
            'status' => $status->value,
            'organization' => $this->organization,
            'name' => $this->name,
            ...(isset($this->establishments) ? ['establishments' => MregEstInscritoResource::collection($this->establishments)] : []),
        ];
    }
}
