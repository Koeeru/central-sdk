<?php

namespace Koeeru\Central\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->externalId,
            'name' => $this->resource->name,
            'subDomain' => $this->getSubdomain(),
            'subDomainText' => $this->getSubdomain(),
            'color' => sprintf('#%06X', mt_rand(0, 0xFFFFFF)),
            'created_at' => $this->resource->createdAt,
            'updated_at' => $this->resource->updatedAt,
        ];
    }
}
