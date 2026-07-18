<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ocorrencias_aniversario', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('membro_id')->constrained('membros')->cascadeOnDelete();
            $table->smallInteger('ano');
            $table->date('data_aniversario');
            $table->enum('status', [
                'pendente',
                'gerando',
                'aguardando_aprovacao',
                'aprovado',
                'publicado',
                'ignorado',
            ])->default('pendente');
            $table->foreignUuid('template_id')->nullable()->constrained('templates_aniversario')->nullOnDelete();
            $table->binary('arte')->nullable();
            $table->string('arte_tipo', 50)->nullable();
            $table->string('arte_nome')->nullable();
            $table->integer('arte_tamanho_bytes')->nullable();
            $table->text('legenda_instagram')->nullable();
            $table->text('mensagem_whatsapp')->nullable();
            $table->timestampTz('gerado_em')->nullable();
            $table->timestampTz('aprovado_em')->nullable();
            $table->foreignUuid('aprovado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('criado_em')->useCurrent();

            $table->unique(['membro_id', 'ano'], 'uq_ocorrencias_membro_ano');
            $table->index(['data_aniversario', 'status'], 'idx_ocorrencias_data_status');
        });

        // Array nativo do Postgres — sem equivalente direto no Schema Builder.
        DB::statement('ALTER TABLE ocorrencias_aniversario ADD COLUMN hashtags TEXT[] NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ocorrencias_aniversario');
    }
};
