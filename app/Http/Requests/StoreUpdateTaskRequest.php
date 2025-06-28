<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'data_limite' => 'nullable|date',
            'finalizado' => 'boolean',
        ];

        // Se precisar validar o id na atualização, pode adicionar assim:
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['id'] = 'required|integer|exists:tasks,id';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.string' => 'O campo nome deve ser uma string.',
            'nome.max' => 'O campo nome não pode ter mais que 255 caracteres.',
            'descricao.string' => 'O campo descrição deve ser uma string.',
            'data_limite.date' => 'O campo data limite deve ser uma data válida.',
            'finalizado.boolean' => 'O campo finalizado deve ser verdadeiro ou falso.',
            'id.required' => 'O campo id é obrigatório para atualização.',
            'id.integer' => 'O campo id deve ser um número inteiro.',
            'id.exists' => 'O id informado não existe.',
        ];
    }
}
