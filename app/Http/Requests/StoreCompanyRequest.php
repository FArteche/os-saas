<?php

namespace App\Http\Requests;

use App\Enums\Niche;
use App\Rules\BrazilianPhone;
use App\Rules\CpfOrCnpj;
use App\Support\Phone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    /**
     * Strip masks before validating: we store documents and phones unformatted.
     */
    protected function prepareForValidation(): void
    {
        $phone = $this->input('phone');

        $this->merge([
            'document' => $this->unmask('document', '/[^0-9A-Za-z]/', uppercase: true),
            'phone' => is_string($phone) ? Phone::normalize($phone) : null,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'document' => ['nullable', 'string', new CpfOrCnpj],
            'phone' => ['nullable', 'string', new BrazilianPhone],
            'niche' => ['required', Rule::enum(Niche::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome da empresa',
            'document' => 'CPF/CNPJ',
            'phone' => 'telefone',
            'niche' => 'ramo de atuação',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Informe o :attribute.',
            'max' => 'O :attribute deve ter no máximo :max caracteres.',
            'niche.enum' => 'Escolha um ramo de atuação válido.',
        ];
    }

    private function unmask(string $key, string $pattern, bool $uppercase = false): ?string
    {
        $value = $this->input($key);

        if (! is_string($value)) {
            return null;
        }

        $clean = preg_replace($pattern, '', $value) ?? '';

        if ($clean === '') {
            return null;
        }

        return $uppercase ? strtoupper($clean) : $clean;
    }
}
