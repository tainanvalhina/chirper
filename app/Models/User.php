<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
 
    //$fillable = define quais campos podem receber atribuição em massa
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    //Define quais atributos devem ser ocultados quando o Model é convertido em array ou JSON.
    protected $hidden = [
        'password',
        'remember_token',
    ];

    //O $casts permite converter automaticamente os valores dos atributos para determinados tipos.
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    
    public function chirps(): HasMany
{
    return $this->hasMany(Chirp::class);
}
}
