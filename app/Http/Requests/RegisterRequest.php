<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    protected $errorBag = 'register';

    public function authorize(): bool { return true; }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $name = $this->input('name');
        $nameRules = [
            'required',
            'string',
            'max:80',
            'regex:/^[A-Za-z0-9\-]+$/',
        ];

        if (in_array($name, ['9Mint', 'Vlas'], true)) {
            $nameRules[] = Rule::unique('users', 'name')->where(fn ($q) => $q->whereNotNull('email'));
        } else {
            $nameRules[] = 'unique:users,name';
        }

        return [
            'name' => $nameRules,
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
        ];
    }
}
