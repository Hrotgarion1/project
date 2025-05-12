<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IdentityDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'identity_id',
        'name',
        'type',
        'path',
        'is_required',
        'is_uploaded_by_user',
        'active',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'is_required' => 'boolean',
        'is_uploaded_by_user' => 'boolean',
    ];

    public function identity()
    {
        return $this->belongsTo(Identity::class);
    }
}