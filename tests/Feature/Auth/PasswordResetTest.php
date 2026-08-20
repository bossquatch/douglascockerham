<?php

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\ResetPassword;
use App\Models\User;
use App\Notifications\NextGenResetPassword;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Notification::assertSentTo($user, NextGenResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Notification::assertSentTo($user, NextGenResetPassword::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Notification::assertSentTo($user, NextGenResetPassword::class, function ($notification) use ($user) {
        $response = Livewire::test(ResetPassword::class, ['token' => $notification->token])
            ->set('email', $user->email)
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('resetPassword');

        $response
            ->assertHasNoErrors()
            ->assertRedirect(route('login', absolute: false));

        return true;
    });
});

test('password reset email uses NextGenEM branding and the Region 7 logo', function () {
    $user = User::factory()->make(['name' => 'Doug']);
    $notification = new NextGenResetPassword('test-token');
    $mail = $notification->toMail($user);

    expect($mail->subject)->toBe('Reset your NextGenEM password')
        ->and($mail->view)->toBe([
            'html' => 'mail.auth.reset-password',
            'text' => 'mail.auth.reset-password-text',
        ])
        ->and($mail->viewData['resetUrl'])->toContain('/reset-password/test-token')
        ->and(view('mail.auth.reset-password', $mail->viewData)->render())
        ->toContain('NextGenEM')
        ->toContain('region-7-emergency-management-shield.webp')
        ->not->toContain('Laravel');
});
