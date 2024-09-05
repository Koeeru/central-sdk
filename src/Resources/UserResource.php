<?php

namespace Koeeru\Central\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'firstName' => $this->resource->firstName,
            'lastName' => $this->resource->lastName,
            'email' => $this->resource->email,
            'language' => $this->resource->language,
            'is_immutable' => $this->resource->is_immutable,
            'avatar' => $this->resource->avatar_url,
            'created_at' => Carbon::parse($this->resource->created_at)->format('Y-m-d'),
            'role' => $this->role,
            'permission' => $this->permission,
            'companies' => $this->companies,
        ];
    }
}
