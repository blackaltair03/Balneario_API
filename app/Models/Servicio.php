<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $table = 'apl_response.servicios';

    //Relacion con balnearios(Muchos a mucho)
    
    public function balnearios()
    {
        return $this->belongsToMany(Balneario::class, 'apl_response.balnearios_servicios','servicio_id', 'balneario_id')
        ->withTimestamps();
    }
}
