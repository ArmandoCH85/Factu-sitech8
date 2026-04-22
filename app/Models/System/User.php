<?php

namespace App\Models\System;

use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
// ── AGREGADO (RECIENTE) ──────────────────────────────────────
// Estos 'use' permiten a Laravel saber cómo procesar los correos
// y los tokens necesarios para cambiar la contraseña.
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Notifications\Notifiable;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    // CanResetPassword: Activa las funciones internas de resetear clave.
    // Notifiable: Permite que este modelo pueda enviar emails.
    use Authenticatable, Authorizable, UsesSystemConnection, CanResetPassword, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'whatsapp_number', 'address_contact', 'introduction',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    
    /**
     * 
     * Retorna nombre de la conexión
     *
     * @return string
     */
    public function getDbConnectionName()
    {
        return $this->getConnection()->getName();
    }

}
