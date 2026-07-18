<?php

namespace Modules\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\App\Casts\PostgresTextArray;

class OcorrenciaAniversario extends Model
{
    use HasUuids;

    protected $table = 'ocorrencias_aniversario';

    const CREATED_AT = 'criado_em';

    const UPDATED_AT = null;

    protected $fillable = [
        'membro_id',
        'ano',
        'data_aniversario',
        'status',
        'template_id',
        'arte',
        'arte_tipo',
        'arte_nome',
        'arte_tamanho_bytes',
        'legenda_instagram',
        'mensagem_whatsapp',
        'hashtags',
        'gerado_em',
        'aprovado_em',
        'aprovado_por',
    ];

    protected $hidden = [
        'arte',
    ];

    protected function casts(): array
    {
        return [
            'data_aniversario' => 'date',
            'hashtags' => PostgresTextArray::class,
            'gerado_em' => 'datetime',
            'aprovado_em' => 'datetime',
        ];
    }

    public function membro(): BelongsTo
    {
        return $this->belongsTo(Membro::class, 'membro_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TemplateAniversario::class, 'template_id');
    }

    public function aprovadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    public function publicacoes(): HasMany
    {
        return $this->hasMany(PublicacaoAniversario::class, 'ocorrencia_id');
    }
}
