<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentitySuspensionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_type',
        'is_inviter',
        'view',
        'controller',
        'function',
        'allowed',
    ];

    protected $casts = [
        'is_inviter' => 'boolean',
        'allowed' => 'boolean',
    ];

    public function identity()
    {
        return $this->belongsTo(Identity::class, 'identity_id');
    }
}
