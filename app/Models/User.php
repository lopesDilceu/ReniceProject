<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'us_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'us_cpf',
        'us_data_nasc',
        'email',
        'password',
        'us_adm',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function telefones()
    {
        return $this->hasMany(Telefone::class, 'te_us_id');
    }

    public function enderecos()
    {
        return $this->hasMany(Endereco::class, 'en_usuario_id');
    }

    public function vendas()
    {
        return $this->hasMany(Venda::class, 've_id_usuario');
    }

    public function avaliacoes()
    {
        return $this->hasMany(Avaliacao::class, 'av_id_usuario');
    }

    public function carrinho()
    {
        return $this->hasOne(Carrinho::class, 'ca_id_usuario');
    }
}
