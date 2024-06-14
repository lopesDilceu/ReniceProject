<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_nome',
        'pr_descricao',
        'pr_preco',
    ];

    public function avaliacoes()
    {
        return $this->hasMany(Avaliacao::class, 'av_id_produto');
    }

    public function itensVenda()
    {
        return $this->hasMany(ItensVenda::class, 'iv_id_produto');
    }

    public function estoque()
    {
        return $this->hasOne(Estoque::class, 'es_id_produto');
    }

    public function compras()
    {
        return $this->hasMany(Compra::class, 'co_id_produto');
    }

    public function itensCarrinho()
    {
        return $this->hasMany(ItensCarrinho::class, 'ic_id_produto');
    }
}
