<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

describe('LandingController', function () {

    test('landing page loads successfully without authentication', function () {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Landing')
        );
    });

    test('landing page contains institution name', function () {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Landing')
            ->has('institutionName')
        );
    });

    test('landing page is accessible to unauthenticated users', function () {
        // Ensure no user is authenticated
        auth()->logout();

        $response = $this->get('/');

        $response->assertStatus(200);
    });

    test('landing page renders with correct props', function () {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Landing')
            ->where('institutionName', 'Instituto Educativo Futuro')
            ->has('features')
            ->has('tagline')
        );
    });
});
