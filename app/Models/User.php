<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'apl_response.users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'balneario_id',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'emial_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => 'string',
    ];

    public function balneairo()
    {
        return $this->belongsTo(Balneario::class, 'balneario_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'apl_response.relations', 'user_id', 'role_id')
        ->withTimestamps();
    }

    public function brazaletesVerificados()
    {
        return $this->hasMany(Brazalete::class, 'checador_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'sueperadmin' ||
        $this->roles()->where('nombre', 'superadmin')->exists();
    }
    
    public function isAdmin(): bool
    {
        return $this->role === 'admin' ||
        $this->roles()->where('nombre', 'admin')->exists();
    }

    public function isChecador(): bool
    {
        return $this->role === 'checador' ||
        $this->roles()->where('nombre', 'checador')->exists();
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }
}
