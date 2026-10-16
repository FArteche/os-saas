<?php

use App\Models\User;

/*
 * Guards the Brazilian Portuguese wording of the most common messages: the
 * laravel-lang pt_BR files ship some European Portuguese phrases, and
 * `artisan lang:update` could bring them back.
 */

it('shows registration errors in Brazilian Portuguese', function () {
    User::factory()->create(['email' => 'dup@example.test']);

    $this->post(route('register.store'), [
        'name' => '',
        'email' => 'dup@example.test',
        'password' => 'x',
        'password_confirmation' => 'y',
    ])->assertSessionHasErrors([
        'name' => 'O campo nome é obrigatório.',
        'email' => 'Este e-mail já está em uso.',
        'password' => 'A confirmação de senha não confere.',
    ]);
});

it('shows a friendly login failure message', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong'])
        ->assertSessionHasErrors(['email' => 'E-mail ou senha incorretos.']);
});

it('uses Brazilian wording in the account emails', function () {
    expect(__('If you did not create an account, no further action is required.'))
        ->toBe('Se você não criou uma conta, ignore este e-mail.')
        ->and(__('Reset Password'))->toBe('Redefinir senha');
});
