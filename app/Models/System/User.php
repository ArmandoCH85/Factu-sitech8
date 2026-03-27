<?php

namespace App\Models\System;

use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'reseller_id', 'api_token', 'status', 'module_permissions',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'status' => 'boolean',
        'module_permissions' => 'array',
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

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reseller_id');
    }

    public function administrators(): HasMany
    {
        return $this->hasMany(self::class, 'reseller_id');
    }

    /**
     * Usuario principal reseller (sin reseller_id) tiene acceso total al panel sistema.
     */
    public function canAccessSystemModule(string $moduleKey): bool
    {
        if ($this->reseller_id === null) {
            return true;
        }

        $perms = $this->module_permissions ?? [];

        return is_array($perms) && in_array($moduleKey, $perms, true);
    }

    /**
     * Primer segmento de ruta del panel (p. ej. payment-orders) frente a permisos guardados.
     */
    public function canAccessSystemPath(?string $firstPathSegment): bool
    {
        if ($this->reseller_id === null) {
            return true;
        }

        $firstPathSegment = $firstPathSegment ?? '';

        if ($firstPathSegment === 'admin-reseller') {
            return false;
        }

        // Subadministradores reseller: misma gestión de clientes (listado, modal, tablas, guardado) que el administrador principal
        if ($firstPathSegment === 'clients') {
            return true;
        }

        $alwaysAllowed = ['', 'dashboard', 'phone', 'users', 'guest-register'];
        if (in_array($firstPathSegment, $alwaysAllowed, true)) {
            return true;
        }

        return $this->canAccessSystemModule($firstPathSegment);
    }

}
