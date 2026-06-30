<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_propietario',
        'is_director',
        'is_secretaria',
        'is_profesor',
        'is_estudiante',
        'codigo_estudiante',
        'is_activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_propietario' => 'boolean',
            'is_director' => 'boolean',
            'is_secretaria' => 'boolean',
            'is_profesor' => 'boolean',
            'is_estudiante' => 'boolean',
            'is_activo' => 'boolean',
        ];
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->{"is_{$role}"} ?? false;
    }

    /**
     * Check if user is propietario
     */
    public function is_propietario(): bool
    {
        return $this->is_propietario;
    }

    /**
     * Check if user is director
     */
    public function is_director(): bool
    {
        return $this->is_director;
    }

    /**
     * Check if user is secretaria
     */
    public function is_secretaria(): bool
    {
        return $this->is_secretaria;
    }

    /**
     * Check if user is profesor
     */
    public function is_profesor(): bool
    {
        return $this->is_profesor;
    }

    /**
     * Check if user is estudiante
     */
    public function is_estudiante(): bool
    {
        return $this->is_estudiante;
    }

    /**
     * Check if user is active
     */
    public function is_active(): bool
    {
        return $this->is_activo;
    }

    /**
     * Get all roles for the user
     */
    public function getRoles(): array
    {
        $roles = [];
        if ($this->is_propietario) {
            $roles[] = 'propietario';
        }
        if ($this->is_director) {
            $roles[] = 'director';
        }
        if ($this->is_secretaria) {
            $roles[] = 'secretaria';
        }
        if ($this->is_profesor) {
            $roles[] = 'profesor';
        }
        if ($this->is_estudiante) {
            $roles[] = 'estudiante';
        }

        return $roles;
    }

    /**
     * Get primary role (first role in priority order)
     */
    public function getPrimaryRole(): string
    {
        if ($this->is_propietario) {
            return 'propietario';
        }
        if ($this->is_director) {
            return 'director';
        }
        if ($this->is_secretaria) {
            return 'secretaria';
        }
        if ($this->is_profesor) {
            return 'profesor';
        }
        if ($this->is_estudiante) {
            return 'estudiante';
        }

        return 'sin_rol';
    }

    /**
     * Check if user has multiple roles
     */
    public function hasMultipleRoles(): bool
    {
        return count($this->getRoles()) > 1;
    }

    public function gruposDocente(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GrupoPeriodo::class, 'docente_id');
    }
}
