<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a CPF (11 digits) or a CNPJ (14 chars, numeric or the alphanumeric
 * format issued since July 2026). Expects the value without mask.
 */
class CpfOrCnpj implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = is_string($value) ? strtoupper($value) : '';

        $valid = match (strlen($value)) {
            11 => self::isValidCpf($value),
            14 => self::isValidCnpj($value),
            default => false,
        };

        if (! $valid) {
            $fail('O :attribute informado não é um CPF ou CNPJ válido.');
        }
    }

    public static function isValidCpf(string $cpf): bool
    {
        if (! preg_match('/^\d{11}$/', $cpf) || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        foreach ([9, 10] as $position) {
            $sum = 0;
            for ($i = 0; $i < $position; $i++) {
                $sum += (int) $cpf[$i] * ($position + 1 - $i);
            }

            $digit = ($sum * 10) % 11 % 10;

            if ($digit !== (int) $cpf[$position]) {
                return false;
            }
        }

        return true;
    }

    public static function isValidCnpj(string $cnpj): bool
    {
        if (! preg_match('/^[0-9A-Z]{12}\d{2}$/', $cnpj) || preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        foreach ([12, 13] as $position) {
            $sum = 0;
            $weight = $position - 7; // 5 for the first check digit, 6 for the second

            for ($i = 0; $i < $position; $i++) {
                $sum += (ord($cnpj[$i]) - 48) * $weight;
                $weight = $weight === 2 ? 9 : $weight - 1;
            }

            $remainder = $sum % 11;
            $digit = $remainder < 2 ? 0 : 11 - $remainder;

            if ($digit !== (int) $cnpj[$position]) {
                return false;
            }
        }

        return true;
    }
}
