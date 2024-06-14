<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrinho extends Model
{
    use HasFactory;

    protected $fillable = [
        'ca_id_usuario',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'ca_id_usuario');
    }

    public function itensCarrinho()
    {
        return $this->hasMany(ItensCarrinho::class, 'ic_id_carrinho');
    }
}
