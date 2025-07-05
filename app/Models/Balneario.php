<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balneario extends Model
{
    protected $table = 'apl_response.balnearios';
        //Relacion de los srevicios (Muchos a muchos)
        public function servicios()
        {
            return $this->belongToMany(Servicio::class, 'apl_response_balnearios_servicios', 'balneario_id', 'servicio_id')
            ->withTimestamps();
        }

        //Relaciono con usuarios (uno a muchos)
        public function users()
        {
            return $this->hasMany(user::class, 'balneario_id');
        }
    
}
