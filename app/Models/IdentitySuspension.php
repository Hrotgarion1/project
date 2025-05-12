<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentitySuspension extends Model
{
    use HasFactory;

    protected $fillable = ['identity_id', 'user_id', 'role_type', 'is_inviter'];
}
