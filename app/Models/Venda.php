<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    use HasFactory;

    protected $fillable = [
        've_id_usuario',
        've_status',
        've_total',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 've_id_usuario');
    }

    public function itensVenda()
    {
        return $this->hasMany(ItensVenda::class, 'iv_id_venda');
    }
}

