<?php

namespace Modules\App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\App\Interfaces\MembroRepositoryInterface;
use Modules\App\Models\Membro;

class MembroRepository implements MembroRepositoryInterface
{
    public function buscarPorId(string $id): ?Membro
    {
        return Membro::find($id);
    }

    public function listar(?string $busca = null): Collection
    {
        return Membro::query()
            ->select([
                'id',
                'nome',
                'foto_tipo',
                'foto_nome',
                'data_nascimento',
                'ignorar_ano',
                'whatsapp',
                'ativo',
                'observacoes',
                'criado_em',
                'atualizado_em',
            ])
            ->when($busca, fn ($q) => $q->where('nome', 'like', "%{$busca}%"))
            ->orderByRaw("TO_CHAR(data_nascimento, 'MM-DD')")
            ->get();
    }
    public function paginar(int $porPagina = 15, ?string $busca = null, ?bool $ativo = null): LengthAwarePaginator
    {
        return Membro::query()
            ->select([
                'id',
                'nome',
                'foto_tipo',
                'foto_nome',
                'data_nascimento',
                'ignorar_ano',
                'whatsapp',
                'ativo',
                'observacoes',
                'criado_em',
                'atualizado_em',
            ])
            ->when($busca !== null, fn ($query) => $query->where('nome', 'like', "%{$busca}%"))
            ->when($ativo !== null, fn ($query) => $query->where('ativo', $ativo))
            ->orderBy('nome')
            ->paginate($porPagina);
    }

    public function criar(array $dados): Membro
    {
        return Membro::create($dados);
    }

    public function atualizar(Membro $membro, array $dados): Membro
    {
        $membro->update($dados);

        return $membro->refresh();
    }

    public function deletar(Membro $membro): bool
    {
        return $membro->update(['ativo' => false]);
    }
}
