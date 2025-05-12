<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IdentityActionReason extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['action_type', 'code', 'title', 'description'];

    public static function getReasons($actionType)
    {
        return self::where('action_type', $actionType)->get(['code', 'title', 'description']);
    }
}
