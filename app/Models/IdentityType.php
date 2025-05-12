<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentityType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['type', 'required_documents', 'terms_and_conditions'];

    protected $casts = [
        'required_documents' => 'array', // Automáticamente convierte el JSON a array
    ];

    /**
     * Get the identities associated with this type.
     */
    public function identities()
    {
        return $this->hasMany(Identity::class, 'identity_type_id');
    }
}