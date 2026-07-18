<?php

namespace Modules\App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateAniversario extends Model
{
    use HasUuids;

    protected $table = 'templates_aniversario';

    const CREATED_AT = 'criado_em';

    const UPDATED_AT = null;

    protected $fillable = [
        'nome',
        'tipo',
        'conteudo',
        'arte_base',
        'arte_base_tipo',
        'arte_base_nome',
        'padrao',
        'ativo',
    ];

    protected $hidden = [
        'arte_base',
    ];

    protected function casts(): array
    {
        return [
            'padrao' => 'boolean',
            'ativo' => 'boolean',
        ];
    }

    public function ocorrencias(): HasMany
    {
        return $this->hasMany(OcorrenciaAniversario::class, 'template_id');
    }
}
