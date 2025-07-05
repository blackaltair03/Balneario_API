<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'requiered|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:checador,admin,superadmin',
            'balneario_id' => [
                'nullable',
                'exists:balnearios.id',
                function ($attribute, $value, $fail) {
                    if ($this->$role === 'checador' && empty($value)) {
                        $fail('Los checadores deben tener un balneario asignado');
                    }
                }
            ]
        ];
    }
}
