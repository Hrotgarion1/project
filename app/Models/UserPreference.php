<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_bg_color',
        'chart_proposed_color',
        'chart_verified_color',
        'text_color',
        'text_font',
    ];

    protected $attributes = [
        'card_bg_color' => '#ffffff',
        'chart_proposed_color' => '#FFD700', // Equivalente a rgb(255, 215, 0)
        'chart_verified_color' => '#FFD700',
        'text_color' => '#000000',
        'text_font' => 'Inter',
    ];
}
