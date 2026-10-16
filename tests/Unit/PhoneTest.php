<?php

use App\Support\Phone;

it('normalizes masks and the +55 country code', function (?string $input, ?string $expected) {
    expect(Phone::normalize($input))->toBe($expected);
})->with([
    'mobile with mask' => ['(11) 98765-4321', '11987654321'],
    'pasted from WhatsApp' => ['+55 11 98765-4321', '11987654321'],
    'landline with country code' => ['+55 (11) 3456-7890', '1134567890'],
    'DDD 55 is kept' => ['(55) 99876-5432', '55998765432'],
    'empty' => ['', null],
    'only symbols' => ['() -', null],
    'null' => [null, null],
]);

it('accepts real Brazilian numbers', function (string $digits) {
    expect(Phone::isValid($digits))->toBeTrue();
})->with([
    'São Paulo mobile' => '11987654321',
    'Santa Maria mobile (DDD 55)' => '55998765432',
    'Manaus mobile' => '92991234567',
    'landline' => '1134567890',
]);

it('rejects fake or malformed numbers', function (string $digits) {
    expect(Phone::isValid($digits))->toBeFalse();
})->with([
    'all zeros' => '00000000000',
    'nonexistent DDD 20' => '20987654321',
    'mobile without 9' => '11887654321',
    'landline starting with 9' => '1194567890',
    'too short' => '119876543',
    'too long' => '119876543210',
]);
