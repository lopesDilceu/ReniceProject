<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = [
        'co_id_produto',
        'co_quantidade',
        'co_preco_unitario',
        'co_fornecedor',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'co_id_produto');
    }
}
