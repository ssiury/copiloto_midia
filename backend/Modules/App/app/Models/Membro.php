<?php

namespace Modules\App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membro extends Model
{
    use HasUuids;

    const CREATED_AT = 'criado_em';

    const UPDATED_AT = 'atualizado_em';

    protected $fillable = [
        'nome',
        'foto',
        'foto_tipo',
        'foto_nome',
        'data_nascimento',
        'ignorar_ano',
        'whatsapp',
        'ativo',
        'observacoes',
    ];

    // Binário grande — nunca sai por padrão em respostas JSON. Servir via
    // endpoint dedicado (GET /membros/:id/foto), não inline.
    protected $hidden = [
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date',
            'ignorar_ano' => 'boolean',
            'ativo' => 'boolean',
        ];
    }

    public function ocorrencias(): HasMany
    {
        return $this->hasMany(OcorrenciaAniversario::class, 'membro_id');
    }
}
