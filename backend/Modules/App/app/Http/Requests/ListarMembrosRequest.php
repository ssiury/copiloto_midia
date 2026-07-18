<?php

namespace Modules\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListarMembrosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'busca' => ['nullable', 'string', 'max:120'],
        ];
    }
}
