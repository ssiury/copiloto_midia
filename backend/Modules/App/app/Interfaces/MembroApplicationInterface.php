<?php

namespace Modules\App\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Modules\App\Application\Data\MembroData;
use Modules\App\Models\Membro;

interface MembroApplicationInterface
{
    /**
     * Lista membros, com busca opcional por nome.
     *
     * @param  string|null  $busca  Termo de busca por nome (opcional)
     * @return Collection<int, Membro> Coleção de membros encontrados
     */
    public function listar(?string $busca = null): Collection;

    /**
     * Cria um novo membro.
     *
     * @param  MembroData  $dados  Dados do membro a ser criado
     * @return Membro O membro criado
     */
    public function criar(MembroData $dados): Membro;

    /**
     * Atualiza os dados de um membro existente.
     *
     * @param  string  $id  ID (UUID) do membro a ser atualizado
     * @param  MembroData  $dados  Dados a serem atualizados
     * @return Membro O membro atualizado
     */
    public function atualizar(string $id, MembroData $dados): Membro;

    /**
     * Marca o membro como inativo (soft delete) — ver docs/midia-igreja.md, seção "Soft Delete".
     *
     * @param  string  $id  ID (UUID) do membro a ser desativado
     * @return Membro O membro desativado
     */
    public function deletar(string $id): Membro;
}
