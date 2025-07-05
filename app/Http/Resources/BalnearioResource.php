<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BalnearioResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'nombre_completo' => $this->nombre_completo,
            'horario_apertura' => $this->horario_apertura,
            'horario_cierre' => $this->horario_cierre,
            'servicios' => ServicioResource::collection($this->whenLoaded('servicios')),
            'checadores' => UserResources::collection($this->whenLoaded('users')),
        ];
    }
}
