<?php

namespace Modules\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicacaoAniversario extends Model
{
    use HasUuids;

    protected $table = 'publicacoes_aniversario';

    const CREATED_AT = 'criado_em';

    const UPDATED_AT = null;

    protected $fillable = [
        'ocorrencia_id',
        'canal',
        'status',
        'agendado_para',
        'publicado_em',
        'id_externo',
        'erro',
        'publicado_por',
    ];

    protected function casts(): array
    {
        return [
            'agendado_para' => 'datetime',
            'publicado_em' => 'datetime',
        ];
    }

    public function ocorrencia(): BelongsTo
    {
        return $this->belongsTo(OcorrenciaAniversario::class, 'ocorrencia_id');
    }

    public function publicadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publicado_por');
    }
}
