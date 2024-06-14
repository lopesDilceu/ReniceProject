<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Endereco extends Model
{
    use HasFactory;

    protected $fillable = [
        'en_usuario_id',
        'en_logradouro',
        'en_cidade',
        'en_estado',
        'en_numero',
        'en_cep',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'en_usuario_id');
    }
}

