<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('templates_aniversario', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome', 80);
            $table->enum('tipo', ['legenda_ig', 'story', 'post', 'whatsapp', 'arte']);
            $table->text('conteudo');
            $table->binary('arte_base')->nullable();
            $table->string('arte_base_tipo', 50)->nullable();
            $table->string('arte_base_nome')->nullable();
            $table->boolean('padrao')->default(false);
            $table->boolean('ativo')->default(true);
            $table->timestampTz('criado_em')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates_aniversario');
    }
};
