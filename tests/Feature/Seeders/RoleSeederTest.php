<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

describe('Role Seeding', function () {

    it('creates propietario user via seeder', function () {
        Artisan::call('db:seed', ['--class' => 'RoleSeeder']);

        $user = User::where('email', 'propietario@edu.com')->first();

        expect($user)->not->toBeNull()
            ->and($user->is_propietario)->toBeTrue()
            ->and($user->is_director)->toBeFalse()
            ->and($user->is_secretaria)->toBeFalse()
            ->and($user->is_profesor)->toBeFalse()
            ->and($user->is_estudiante)->toBeFalse()
            ->and($user->is_activo)->toBeTrue();
    });

    it('creates director user via seeder', function () {
        Artisan::call('db:seed', ['--class' => 'RoleSeeder']);

        $user = User::where('email', 'director@edu.com')->first();

        expect($user)->not->toBeNull()
            ->and($user->is_director)->toBeTrue()
            ->and($user->is_activo)->toBeTrue();
    });

    it('creates secretaria user via seeder', function () {
        Artisan::call('db:seed', ['--class' => 'RoleSeeder']);

        $user = User::where('email', 'secretaria@edu.com')->first();

        expect($user)->not->toBeNull()
            ->and($user->is_secretaria)->toBeTrue()
            ->and($user->is_activo)->toBeTrue();
    });

    it('creates profesor user via seeder', function () {
        Artisan::call('db:seed', ['--class' => 'RoleSeeder']);

        $user = User::where('email', 'juan@doc.com')->first();

        expect($user)->not->toBeNull()
            ->and($user->name)->toBe('Prof. Juan Martínez')
            ->and($user->is_profesor)->toBeTrue()
            ->and($user->is_activo)->toBeTrue();
    });

    it('creates 3 estudiante users via seeder', function () {
        Artisan::call('db:seed', ['--class' => 'RoleSeeder']);

        $students = User::where('is_estudiante', true)->get();

        expect($students->count())->toBe(3)
            ->and($students->pluck('email'))->toContain('carlos@est.com')
            ->and($students->pluck('email'))->toContain('ana@est.com')
            ->and($students->pluck('email'))->toContain('david@est.com');
    });

    it('creates all 7 users via seeder', function () {
        Artisan::call('db:seed', ['--class' => 'RoleSeeder']);

        $totalUsers = User::count();

        expect($totalUsers)->toBe(7);
    });

    it('users have correct names', function () {
        Artisan::call('db:seed', ['--class' => 'RoleSeeder']);

        expect(User::where('email', 'propietario@edu.com')->first()->name)->toBe('Roberto García')
            ->and(User::where('email', 'director@edu.com')->first()->name)->toBe('María López')
            ->and(User::where('email', 'secretaria@edu.com')->first()->name)->toBe('Laura Fernández')
            ->and(User::where('email', 'juan@doc.com')->first()->name)->toBe('Prof. Juan Martínez')
            ->and(User::where('email', 'carlos@est.com')->first()->name)->toBe('Carlos Gómez')
            ->and(User::where('email', 'ana@est.com')->first()->name)->toBe('Ana Pérez')
            ->and(User::where('email', 'david@est.com')->first()->name)->toBe('David Rojas');
    });

    it('users have encrypted passwords', function () {
        Artisan::call('db:seed', ['--class' => 'RoleSeeder']);

        $user = User::where('email', 'propietario@edu.com')->first();

        expect(Hash::check('password', $user->password))->toBeTrue();
    });
});
