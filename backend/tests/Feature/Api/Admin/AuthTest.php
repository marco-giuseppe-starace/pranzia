<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('logs in with valid credentials and returns a token', function () {
    User::factory()->create(['email' => 'staff@pranzia.test', 'password' => Hash::make('secret123')]);

    $response = $this->postJson('/api/admin/login', [
        'email' => 'staff@pranzia.test',
        'password' => 'secret123',
    ]);

    $response->assertOk()->assertJsonStructure(['token']);
});

it('rejects invalid credentials', function () {
    User::factory()->create(['email' => 'staff@pranzia.test', 'password' => Hash::make('secret123')]);

    $this->postJson('/api/admin/login', [
        'email' => 'staff@pranzia.test',
        'password' => 'wrong-password',
    ])->assertUnprocessable();
});

it('logs out and revokes the current token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/admin/logout');

    $response->assertOk();
    expect($user->tokens()->count())->toBe(0);
});
