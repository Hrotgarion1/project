<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentityChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = ['requestable_type', 'requestable_id', 'message', 'sent_at', 'sent_by', 'seen_at'];

    protected $casts = [
        'sent_at' => 'datetime',
        'seen_at' => 'datetime',
    ];

    public function requestable()
    {
        return $this->morphTo();
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function is_unseen()
    {
        return is_null($this->seen_at);
    }
}
