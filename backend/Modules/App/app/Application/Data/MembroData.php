<?php

namespace Modules\App\Application\Data;

final readonly class MembroData
{
    public function __construct(
        public string $nome,
        public string $dataNascimento,
        public bool $ignorarAno,
        public ?string $whatsapp,
        public bool $ativo,
        public ?string $observacoes,
        public ?string $foto = null,
        public ?string $fotoTipo = null,
        public ?string $fotoNome = null,
    ) {
    }
}
