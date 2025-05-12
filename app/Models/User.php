<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Invitacion;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'address',
        'zip',
        'phone',
        'email',
        'password',
        'pais_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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
        ];
    }

    public function pais()
    {
        return $this->belongsTo(Paises::class, 'pais_id', 'id');
    }

    /**
     * Get the invitations received by the user.
     */
    public function invitaciones_recibidas(): HasMany
    {
        return $this->hasMany(Invitacion::class, 'invitado_id');
    }

    /**
     * Get the invitations sent by the user.
     */
    public function invitaciones_enviadas(): HasMany
    {
        return $this->hasMany(Invitacion::class, 'invitador_id');
    }

    /**
     * Get all invitations related to the user.
     */
    public function invitaciones(): HasMany
    {
        return $this->hasMany(Invitacion::class, 'invitado_id')
                    ->orWhere('invitador_id', $this->id);
    }

    public function identities() {
        return $this->hasMany(Identity::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_user')
                    ->withPivot('identity_id')
                    ->withTimestamps();
    }

    public function preferences()
    {
        return $this->hasOne(UserPreference::class);
    }
}
