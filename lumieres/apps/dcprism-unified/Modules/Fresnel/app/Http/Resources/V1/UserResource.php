<?php

namespace Modules\Fresnel\app\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->when(
                $request->user()?->id === $this->id || $request->user()?->can('view-user-emails'),
                $this->email
            ),
            'avatar_url' => $this->avatar_url,
            'role' => $this->when($this->role, $this->role),
            'permissions' => $this->when(
                $request->includePermissions ?? false,
                method_exists($this->resource, 'getAllPermissions') ? $this->getAllPermissions()->pluck('name') : []
            ),
            'is_active' => $this->is_active ?? true,
            'last_seen_at' => $this->last_seen_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            
            // Hide sensitive information
            'email_verified_at' => $this->when(
                $request->user()?->id === $this->id,
                $this->email_verified_at?->toISOString()
            ),
        ];
    }
}
