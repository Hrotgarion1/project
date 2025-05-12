<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_id',
        'identity_id',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function identity()
    {
        return $this->belongsTo(Identity::class, 'identity_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user')
                    ->withPivot('identity_id')
                    ->withTimestamps();
    }
}