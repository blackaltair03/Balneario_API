<?php

namespace App\Http\Resources;

use Illuminate\Http\Json\JsonResource;
use Illuminate\Http\Request;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array 
    {
        return[
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $thi->role,
            'email_veriefed_at'=> $this->email_veriefed_at,
            'balneario_id' => $this->balneario_id,
            'balneario' => BalnearioResource::make($this->whenLoaded('balneario')),
            'roles'=> RoleResource::collection($this->whenLoaded('roles')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'update_at' => $this->update_at->format('Y-m-d H:i:s'),
            'is_admin' => $this->when($request->user()?->isAdmin(), $this->isAdmin()),
            'is_superadmin' => $this->when($request->user()?->isSuperAdmin(), $this->isSuperAdmin()),
        ];
    }
}