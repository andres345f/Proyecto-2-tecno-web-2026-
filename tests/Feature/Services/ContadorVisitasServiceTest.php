<?php

use App\Models\User;
use App\Models\Visita;
use App\Services\ContadorVisitasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

describe('ContadorVisitasService', function () {

    test('service can be resolved from container', function () {
        $service = app(ContadorVisitasService::class);
        $this->assertInstanceOf(ContadorVisitasService::class, $service);
    });

    test('registrarVisita creates visita record for anonymous user', function () {
        $request = Request::create('/api/visitas', 'POST', [
            'url' => '/dashboard',
        ]);

        $service = app(ContadorVisitasService::class);
        $service->registrarVisita($request, '/dashboard');

        $this->assertDatabaseHas('visitas', [
            'url' => '/dashboard',
            'usuario_id' => null,
        ]);
    });

    test('registrarVisita creates visita record for authenticated user', function () {
        $user = User::factory()->create();

        $request = Request::create('/api/visitas', 'POST', [
            'url' => '/aulas',
        ]);
        $request->setUserResolver(fn () => $user);

        $service = app(ContadorVisitasService::class);
        $service->registrarVisita($request, '/aulas');

        $this->assertDatabaseHas('visitas', [
            'url' => '/aulas',
            'usuario_id' => $user->id,
        ]);
    });

    test('registrarVisita captures ip address', function () {
        $request = Request::create('/api/visitas', 'POST', [
            'url' => '/test',
        ]);
        $request->server->set('REMOTE_ADDR', '192.168.1.1');

        $service = app(ContadorVisitasService::class);
        $service->registrarVisita($request, '/test');

        $visita = Visita::where('url', '/test')->first();
        $this->assertEquals('192.168.1.1', $visita->ip_address);
    });

    test('registrarVisita captures user agent', function () {
        $request = Request::create('/api/visitas', 'POST', [
            'url' => '/test',
        ], [], [], [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 Test Browser',
        ]);

        $service = app(ContadorVisitasService::class);
        $service->registrarVisita($request, '/test');

        $visita = Visita::where('url', '/test')->first();
        $this->assertEquals('Mozilla/5.0 Test Browser', $visita->user_agent);
    });

    test('registrarVisita handles null user gracefully', function () {
        $request = Request::create('/api/visitas', 'POST', [
            'url' => '/guest',
        ]);
        // No user set

        $service = app(ContadorVisitasService::class);
        $service->registrarVisita($request, '/guest');

        $visita = Visita::where('url', '/guest')->first();
        $this->assertNull($visita->usuario_id);
    });

    test('registrarVisita creates multiple records for same url', function () {
        $request = Request::create('/api/visitas', 'POST', [
            'url' => '/popular',
        ]);

        $service = app(ContadorVisitasService::class);
        $service->registrarVisita($request, '/popular');
        $service->registrarVisita($request, '/popular');
        $service->registrarVisita($request, '/popular');

        $count = Visita::where('url', '/popular')->count();
        $this->assertEquals(3, $count);
    });
});
