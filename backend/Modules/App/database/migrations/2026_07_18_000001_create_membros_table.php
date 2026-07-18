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
        Schema::create('membros', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome', 120);
            $table->binary('foto')->nullable();
            $table->string('foto_tipo', 50)->nullable();
            $table->string('foto_nome')->nullable();
            $table->date('data_nascimento');
            $table->boolean('ignorar_ano')->default(false);
            $table->string('whatsapp', 20)->nullable();
            $table->boolean('ativo')->default(true);
            $table->text('observacoes')->nullable();
            $table->timestampTz('criado_em')->useCurrent();
            $table->timestampTz('atualizado_em')->useCurrent();

            $table->index('data_nascimento', 'idx_membros_nascimento');
        });

        // `atualizado_em` é mantido via trigger (Postgres não suporta
        // "ON UPDATE CURRENT_TIMESTAMP" nativamente como o MySQL).
        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION set_atualizado_em()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.atualizado_em = NOW();
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER trg_membros_atualizado_em
            BEFORE UPDATE ON membros
            FOR EACH ROW
            EXECUTE FUNCTION set_atualizado_em();
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trg_membros_atualizado_em ON membros');
        DB::statement('DROP FUNCTION IF EXISTS set_atualizado_em()');

        Schema::dropIfExists('membros');
    }
};
