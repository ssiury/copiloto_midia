<?php

namespace Modules\App\Interfaces;

use Illuminate\Http\JsonResponse;
use Modules\App\Http\Requests\AtualizarMembroRequest;
use Modules\App\Http\Requests\CriarMembroRequest;
use Modules\App\Http\Requests\ListarMembrosRequest;

interface MembroControllerInterface
{
    /**
     * Lista membros, com busca opcional por nome.
     *
     * @param  ListarMembrosRequest  $request  Requisição validada com o termo de busca opcional
     * @return JsonResponse Lista de membros formatada como recurso JSON
     */
    public function index(ListarMembrosRequest $request): JsonResponse;

    /**
     * Cria um novo membro.
     *
     * @param  CriarMembroRequest  $request  Requisição validada com os dados do membro
     * @return JsonResponse Membro criado, formatado como recurso JSON
     */
    public function store(CriarMembroRequest $request): JsonResponse;

    /**
     * Atualiza um membro existente.
     *
     * @param  AtualizarMembroRequest  $request  Requisição validada com os dados do membro
     * @param  string  $id  ID (UUID) do membro a ser atualizado
     * @return JsonResponse Membro atualizado, formatado como recurso JSON
     */
    public function update(AtualizarMembroRequest $request, string $id): JsonResponse;

    /**
     * Desativa (soft delete) um membro existente.
     *
     * @param  string  $id  ID (UUID) do membro a ser desativado
     * @return JsonResponse Membro desativado, formatado como recurso JSON
     */
    public function destroy(string $id): JsonResponse;
}
