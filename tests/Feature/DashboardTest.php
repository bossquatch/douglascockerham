<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guests are redirected to the public instructor administration page', function () {
    $this->get('/dashboard')->assertRedirect('/em/training/admin');
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs($user = User::factory()->create());

    $this->followingRedirects()->get('/dashboard')
        ->assertOk()
        ->assertSeeText('Instructor capability review')
        ->assertSeeText('Region 7');
});
