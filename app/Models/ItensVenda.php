<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItensVenda extends Model
{
    use HasFactory;

    protected $table = 'itensvenda';
    protected $primaryKey = 'iv_id';
    public $timestamps = false;
    protected $fillable = [
        'iv_id_venda',
        'iv_id_produto',
        'iv_quantidade',
        'iv_preco_unitario',
    ];

    public function venda()
    {
        return $this->belongsTo(Venda::class, 'iv_id_venda');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'iv_id_produto');
    }
}

