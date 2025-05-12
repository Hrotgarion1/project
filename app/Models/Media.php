<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

   protected $fillable = [
        'file_path',
        'file_name',
        'file_type',
        'folder',
        'position',
        'mediable_id',
        'mediable_type',
        'is_youtube',
        'youtube_id',
        'role',
    ];

    protected $appends = ['is_youtube']; // Añadir is_youtube a los atributos serializados

    public function mediable()
    {
        return $this->morphTo();
    }

    public function getUrlAttribute()
    {
        if ($this->is_youtube) {
            return "https://www.youtube.com/watch?v={$this->youtube_id}";
        }
        return Storage::disk('public')->url($this->file_path);
    }

    public function getIsImageAttribute()
    {
        return !$this->is_youtube && str_starts_with($this->file_type, 'image/');
    }

    public function getIsPdfAttribute()
    {
        return !$this->is_youtube && $this->file_type === 'application/pdf';
    }

    public function getIsYoutubeAttribute()
    {
        return !empty($this->youtube_id);
    }
}