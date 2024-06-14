<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estoque extends Model
{
    use HasFactory;

    protected $table = 'estoque';
    protected $fillable = [
        'es_id_produto',
        'es_nome_produto',
        'es_quantidade',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'es_id_produto');
    }
}
