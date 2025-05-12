<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IdentityMeta extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'identity_meta';

    protected $fillable = ['identity_id', 'key', 'value', 'type'];

    protected $casts = [
        'value' => 'array', // Para manejar valores complejos en JSON
    ];

    public function identity()
    {
        return $this->belongsTo(Identity::class,  'identity_id', 'id');
    }
}
