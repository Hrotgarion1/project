<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invitacion extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'invitaciones';

    protected $fillable = [
        'invitador_id',
        'invitado_id',
        'identity_id',
        'role',
        'status',
        'token',
    ];

    public function invitador()
    {
        return $this->belongsTo(User::class, 'invitador_id');
    }

    public function invitado()
    {
        return $this->belongsTo(User::class, 'invitado_id');
    }

    public function identity()
    {
        return $this->belongsTo(Identity::class, 'identity_id');
    }
}
