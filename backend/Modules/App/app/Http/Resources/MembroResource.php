<?php

namespace Modules\App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MembroResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'foto_tipo' => $this->foto_tipo,
            'foto_nome' => $this->foto_nome,
            'data_nascimento' => $this->data_nascimento,
            'ignorar_ano' => $this->ignorar_ano,
            'whatsapp' => $this->whatsapp,
            'ativo' => $this->ativo,
            'observacoes' => $this->observacoes,
            'criado_em' => $this->criado_em,
            'atualizado_em' => $this->atualizado_em,
        ];
    }
}
