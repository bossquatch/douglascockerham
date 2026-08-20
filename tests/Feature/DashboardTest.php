<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guests are redirected to login from dashboard aliases', function () {
    $this->get('/dashboard')->assertRedirect(route('login'));
    $this->get('/inventory')->assertRedirect(route('login'));
    $this->get('/region7/instructors/admin')->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs($user = User::factory()->create());

    $this->followingRedirects()->get('/dashboard')
        ->assertOk()
        ->assertSeeText('Instructor capability review')
        ->assertSeeText('Region 7');
});
