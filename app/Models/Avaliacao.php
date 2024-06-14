<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avaliacao extends Model
{
    use HasFactory;

    protected $fillable = [
        'av_id_produto',
        'av_id_usuario',
        'av_nota',
        'av_comentario',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'av_id_produto');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'av_id_usuario');
    }
}
