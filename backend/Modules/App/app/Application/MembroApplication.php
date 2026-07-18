<?php

namespace Modules\App\Application;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\App\Application\Data\MembroData;
use Modules\App\Interfaces\MembroApplicationInterface;
use Modules\App\Interfaces\MembroRepositoryInterface;
use Modules\App\Models\Membro;

class MembroApplication implements MembroApplicationInterface
{
    public function __construct(
        private readonly MembroRepositoryInterface $membros,
    ) {
    }

    /**
     * Lista membros, com busca opcional por nome.
     *
     * @param  string|null  $busca  Termo de busca por nome (opcional)
     * @return Collection<int, \Modules\App\Models\Membro>
     */
    public function listar(?string $busca = null): Collection
    {
        return $this->membros->listar($busca);
    }

    public function criar(MembroData $dados): Membro
    {
        return $this->membros->criar([
            'nome' => $dados->nome,
            'data_nascimento' => $dados->dataNascimento,
            'ignorar_ano' => $dados->ignorarAno,
            'whatsapp' => $dados->whatsapp,
            'ativo' => $dados->ativo,
            'observacoes' => $dados->observacoes,
            'foto' => $dados->foto !== null ? base64_decode($dados->foto) : null,
            'foto_tipo' => $dados->fotoTipo,
            'foto_nome' => $dados->fotoNome,
        ]);
    }

    public function atualizar(string $id, MembroData $dados): Membro
    {
        $membro = $this->membros->buscarPorId($id);

        if (! $membro) {
            throw (new ModelNotFoundException)->setModel(Membro::class, [$id]);
        }

        $camposParaAtualizar = [
            'nome' => $dados->nome,
            'data_nascimento' => $dados->dataNascimento,
            'ignorar_ano' => $dados->ignorarAno,
            'whatsapp' => $dados->whatsapp,
            'ativo' => $dados->ativo,
            'observacoes' => $dados->observacoes,
        ];

        // Só sobrescreve a foto se uma nova foi enviada — sem endpoint
        // dedicado de leitura da foto ainda, o formulário de edição não
        // tem como reenviar a foto atual, e não podemos apagá-la por omissão.
        if ($dados->foto !== null) {
            $camposParaAtualizar['foto'] = base64_decode($dados->foto);
            $camposParaAtualizar['foto_tipo'] = $dados->fotoTipo;
            $camposParaAtualizar['foto_nome'] = $dados->fotoNome;
        }

        return $this->membros->atualizar($membro, $camposParaAtualizar);
    }

    public function deletar(string $id): Membro
    {
        $membro = $this->membros->buscarPorId($id);

        if (! $membro) {
            throw (new ModelNotFoundException)->setModel(Membro::class, [$id]);
        }

        $this->membros->deletar($membro);

        return $membro;
    }
}
