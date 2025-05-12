<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentitySlugHistory extends Model
{
    use HasFactory;

    protected $fillable = ['identity_id', 'slug'];

    public function identity()
    {
        return $this->belongsTo(Identity::class, 'identity_id');
    }
}
