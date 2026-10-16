<?php

namespace App\Support;

/**
 * Brazilian phone numbers, stored as DDD + number digits only (10 or 11 digits).
 */
class Phone
{
    /**
     * Valid Brazilian area codes (DDD).
     */
    public const AREA_CODES = [
        11, 12, 13, 14, 15, 16, 17, 18, 19,
        21, 22, 24, 27, 28,
        31, 32, 33, 34, 35, 37, 38,
        41, 42, 43, 44, 45, 46, 47, 48, 49,
        51, 53, 54, 55,
        61, 62, 63, 64, 65, 66, 67, 68, 69,
        71, 73, 74, 75, 77, 79,
        81, 82, 83, 84, 85, 86, 87, 88, 89,
        91, 92, 93, 94, 95, 96, 97, 98, 99,
    ];

    /**
     * Strip the mask and the +55 country code (as copied from WhatsApp).
     * "55" alone is also a valid DDD, so it is only removed from numbers longer than 11 digits.
     */
    public static function normalize(?string $value): ?string
    {
        $digits = preg_replace('/\D/', '', $value ?? '') ?? '';

        if (strlen($digits) > 11 && str_starts_with($digits, '55')) {
            $digits = substr($digits, 2);
        }

        return $digits === '' ? null : $digits;
    }

    /**
     * Whether normalized digits form a real Brazilian mobile (11) or landline (10) number.
     */
    public static function isValid(string $digits): bool
    {
        if (! preg_match('/^\d{10,11}$/', $digits)) {
            return false;
        }

        if (! in_array((int) substr($digits, 0, 2), self::AREA_CODES, true)) {
            return false;
        }

        $first = $digits[2];

        return strlen($digits) === 11
            ? $first === '9'                      // mobile: 9XXXX-XXXX
            : in_array($first, ['2', '3', '4', '5'], true); // landline: 2XXX-XXXX to 5XXX-XXXX
    }
}
