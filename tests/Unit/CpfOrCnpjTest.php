<?php

use App\Rules\CpfOrCnpj;

it('accepts valid CPFs', function (string $cpf) {
    expect(CpfOrCnpj::isValidCpf($cpf))->toBeTrue();
})->with(['52998224725', '11144477735']);

it('rejects invalid CPFs', function (string $cpf) {
    expect(CpfOrCnpj::isValidCpf($cpf))->toBeFalse();
})->with([
    'wrong check digit' => '52998224724',
    'repeated digits' => '11111111111',
    'too short' => '5299822472',
    'with letters' => '5299822472A',
]);

it('accepts valid numeric and alphanumeric CNPJs', function (string $cnpj) {
    expect(CpfOrCnpj::isValidCnpj($cnpj))->toBeTrue();
})->with([
    'numeric' => '11222333000181',
    'alphanumeric (Receita example)' => '12ABC34501DE35',
]);

it('rejects invalid CNPJs', function (string $cnpj) {
    expect(CpfOrCnpj::isValidCnpj($cnpj))->toBeFalse();
})->with([
    'wrong check digit' => '11222333000182',
    'alphanumeric wrong check digit' => '12ABC34501DE36',
    'letter in check digits' => '12ABC34501DE3A',
    'repeated digits' => '00000000000000',
    'lowercase' => '12abc34501de35',
]);
