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
        Schema::create('publicacoes_aniversario', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ocorrencia_id')->constrained('ocorrencias_aniversario')->cascadeOnDelete();
            $table->enum('canal', [
                'instagram_post',
                'instagram_story',
                'whatsapp_grupo',
                'whatsapp_direto',
            ]);
            $table->enum('status', ['agendado', 'publicando', 'publicado', 'falhou'])->default('agendado');
            $table->timestampTz('agendado_para')->nullable();
            $table->timestampTz('publicado_em')->nullable();
            $table->text('id_externo')->nullable();
            $table->text('erro')->nullable();
            $table->foreignUuid('publicado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('criado_em')->useCurrent();

            $table->index('ocorrencia_id', 'idx_publicacoes_ocorrencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publicacoes_aniversario');
    }
};
