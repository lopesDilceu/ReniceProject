<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('us_id'); // Usando 'us_id' como nome da coluna para o ID
            $table->string('name'); // Nome do usuário
            $table->string('us_cpf', 11); // CPF do usuário
            $table->date('us_data_nasc'); // Data de nascimento do usuário
            $table->string('email')->unique(); // Email do usuário
            $table->string('password'); // Senha do usuário
            $table->timestamp('us_data_criacao')->useCurrent(); // Data de criação com valor padrão como o timestamp atual
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });  
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
    }
};
