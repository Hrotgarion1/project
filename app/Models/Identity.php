<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Identity extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'identity_type_id', 'type', 'email', 'name', 'address', 'phone', 'documents', 'status',
        'handled_by', 'taken_at', 'suspension_reason', 'suspend_reason_code',
        'delete_reason_code', 'notes', 'requested_changes', 'has_updates', 'slug',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'has_updates' => 'boolean',
        'requested_changes' => 'array',
    ];

    /**
     * Order of status values for sorting.
     */
    public const STATUS_ORDER = [
        'pending' => 1,
        'in_progress' => 2,
        'waiting' => 3,
        'approved' => 4,
        'rejected' => 5,
        'suspended' => 6,
        'deleted' => 7,
    ];

    /**
     * Scope to order identities by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderedByStatus($query)
    {
        if (DB::getDriverName() === 'mysql') {
            return $query->orderByRaw("FIELD(status, 'pending', 'in_progress', 'waiting', 'approved', 'rejected', 'suspended', 'deleted')");
        }

        $caseStatement = collect(self::STATUS_ORDER)
            ->map(fn($order, $status) => "WHEN '$status' THEN $order")
            ->implode(' ');
        return $query->orderByRaw("CASE status $caseStatement END");
    }

    /**
     * Get the user that owns the identity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that handles the identity.
     */
    public function handledBy()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Check if the identity is pending.
     *
     * @return bool
     */
    public function is_pending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get the metas associated with the identity.
     */
    public function metas(): HasMany
    {
        return $this->hasMany(IdentityMeta::class, 'identity_id', 'id');
    }

    /**
     * Get the documents associated with the identity.
     */
    public function identityDocuments()
    {
        return $this->hasMany(IdentityDocument::class, 'identity_id', 'id');
    }

    /**
     * Get the change requests associated with the identity.
     */
    public function change_requests()
    {
        return $this->morphMany(IdentityChangeRequest::class, 'requestable')->orderBy('sent_at', 'desc');
    }

    /**
     * Get the identity type associated with the identity.
     */
    public function type()
    {
        return $this->belongsTo(IdentityType::class, 'identity_type_id');
    }

    /**
     * Get the invitations associated with the identity.
     */
    public function invitations()
    {
        return $this->hasMany(Invitacion::class, 'identity_id');
    }

    /**
     * Relacion para el componente para las imagenes
     */
    public function media()
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('position');
    }

    /**
     * Get the slug history associated with the identity.
     */
    public function slugHistory(): HasMany
    {
        return $this->hasMany(IdentitySlugHistory::class, 'identity_id', 'id');
    }

    /**
     * Boot the model to handle slug generation.
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($identity) {
            if (!$identity->slug) {
                $identity->slug = $identity->generateUniqueSlug();
            }
        });

        static::updating(function ($identity) {
            if ($identity->isDirty(['type', 'email'])) {
                $oldSlug = $identity->getOriginal('slug');
                $newSlug = $identity->generateUniqueSlug();
                $identity->slug = $newSlug;

                // Guardar el slug antiguo en el historial
                if ($oldSlug && $oldSlug !== $newSlug) {
                    $identity->slugHistory()->create(['slug' => $oldSlug]);
                }
            }
        });
    }

    /**
     * Generate a unique slug based on type and email.
     *
     * @return string
     */
    public function generateUniqueSlug()
    {
        $baseSlug = Str::slug("{$this->type}-" . str_replace('@', '.', $this->email));
        $slug = $baseSlug;
        $counter = 1;
        while (self::where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }
        return $slug;
    }

    /**
     * Use slug as the route key name.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}