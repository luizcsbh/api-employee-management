<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $company = $this->route('company'); // Obter a instância da empresa pela rota
        
        return [
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'cnpj' => 'sometimes|required|string|size:14|unique:companies,cnpj,' . $company->id,
            'opening_date' => 'sometimes|required|date',
            'email' => 'sometimes|required|email|unique:companies,email,' . $company->id,
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function messages()
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.max' => 'O nome deve ter no máximo 255 caracteres.',
            'address.required' => 'O endereço é obrigatório.',
            'address.max' => 'O endereço deve ter no máximo 255 caracteres.',
            'cnpj.required' => 'O CNPJ é obrigatório.',
            'cnpj.unique' => 'O CNPJ já está em uso.',
            'cnpj.size' => 'O CNPJ deve conter exatamente 14 caracteres.',
            'opening_date.required' => 'A data de abertura é obrigatória.',
            'opening_date.date' => 'A data de abertura deve estar em um formato válido.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser válido.',
            'email.max' => 'O e-mail deve ter no máximo 255 caracteres.',
            'email.unique' => 'O e-mail já está em uso.',
        ];
    }
}
