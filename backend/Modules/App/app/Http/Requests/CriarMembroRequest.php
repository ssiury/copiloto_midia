<?php

namespace Modules\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CriarMembroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:120'],
            'data_nascimento' => ['required', 'date'],
            'ignorar_ano' => ['boolean'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'ativo' => ['boolean'],
            'observacoes' => ['nullable', 'string'],
            'foto' => ['nullable', 'string'],
            'foto_tipo' => ['nullable', 'string', 'max:50', 'required_with:foto'],
            'foto_nome' => ['nullable', 'string', 'max:255'],
        ];
    }
}
