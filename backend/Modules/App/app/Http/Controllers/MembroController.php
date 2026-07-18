<?php

namespace Modules\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\App\Application\Data\MembroData;
use Modules\App\Http\Requests\AtualizarMembroRequest;
use Modules\App\Http\Requests\CriarMembroRequest;
use Modules\App\Http\Requests\ListarMembrosRequest;
use Modules\App\Http\Resources\MembroResource;
use Modules\App\Interfaces\MembroApplicationInterface;
use Modules\App\Interfaces\MembroControllerInterface;

class MembroController extends Controller implements MembroControllerInterface
{
    public function __construct(
        private readonly MembroApplicationInterface $membroApplication,
    ) {
    }

    public function index(ListarMembrosRequest $request): JsonResponse
    {
        $membros = $this->membroApplication->listar($request->validated('busca'));

        return MembroResource::collection($membros)
            ->additional(['meta' => (object) []])
            ->response();
    }

    public function store(CriarMembroRequest $request): JsonResponse
    {
        $membro = $this->membroApplication->criar($this->dadosDoRequest($request));

        return (new MembroResource($membro))
            ->additional(['meta' => (object) []])
            ->response()
            ->setStatusCode(201);
    }

    public function update(AtualizarMembroRequest $request, string $id): JsonResponse
    {
        $membro = $this->membroApplication->atualizar($id, $this->dadosDoRequest($request));

        return (new MembroResource($membro))
            ->additional(['meta' => (object) []])
            ->response();
    }

    public function destroy(string $id): JsonResponse
    {
        $membro = $this->membroApplication->deletar($id);

        return (new MembroResource($membro))
            ->additional(['meta' => (object) []])
            ->response();
    }

    private function dadosDoRequest(CriarMembroRequest|AtualizarMembroRequest $request): MembroData
    {
        return new MembroData(
            nome: $request->validated('nome'),
            dataNascimento: $request->validated('data_nascimento'),
            ignorarAno: $request->boolean('ignorar_ano'),
            whatsapp: $request->validated('whatsapp'),
            ativo: $request->boolean('ativo', true),
            observacoes: $request->validated('observacoes'),
            foto: $request->validated('foto'),
            fotoTipo: $request->validated('foto_tipo'),
            fotoNome: $request->validated('foto_nome'),
        );
    }
}
