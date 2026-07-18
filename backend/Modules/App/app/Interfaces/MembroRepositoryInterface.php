<?php

namespace Modules\App\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Modules\App\Models\Membro;

interface MembroRepositoryInterface
{
    /**
     * Busca um membro pelo ID.
     *
     * @param  string  $id  ID (UUID) do membro
     * @return Membro|null O membro encontrado, ou null se não existir
     */
    public function buscarPorId(string $id): ?Membro;

    /**
     * Lista membros, com busca opcional por nome, ordenados por dia/mês de aniversário.
     *
     * @param  string|null  $busca  Termo de busca por nome (opcional)
     * @return Collection<int, Membro> Coleção de membros encontrados
     */
    public function listar(?string $busca = null): Collection;

    /**
     * Cria um novo membro.
     *
     * @param  array  $dados  Dados do membro a ser criado
     * @return Membro O membro criado
     */
    public function criar(array $dados): Membro;

    /**
     * Atualiza os dados de um membro existente.
     *
     * @param  Membro  $membro  Membro a ser atualizado
     * @param  array  $dados  Dados a serem atualizados
     * @return Membro O membro atualizado
     */
    public function atualizar(Membro $membro, array $dados): Membro;

    /**
     * Marca o membro como inativo (soft delete) em vez de remover a linha — ver docs/midia-igreja.md, seção "Soft Delete".
     *
     * @param  Membro  $membro  Membro a ser desativado
     * @return bool Se a operação foi bem-sucedida
     */
    public function deletar(Membro $membro): bool;
}
