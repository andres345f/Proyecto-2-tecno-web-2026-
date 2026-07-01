<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Cuota;
use Symfony\Component\HttpFoundation\Response;

class BlockForOverdueCuotas
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->is_estudiante) {
            // Check if the current route is allowed
            $allowedRoutes = [
                'pagos.index',
                'api.pagos.generar-qr',
                'api.pagos.estado',
                'logout',
            ];

            if ($request->route() && $request->routeIs($allowedRoutes)) {
                return $next($request);
            }

            // Check if the user has any overdue cuota
            $hasOverdue = Cuota::whereHas('matriculaPeriodo.matriculaCarrera', function ($query) use ($user) {
                $query->where('usuario_id', $user->id);
            })
            ->where('estado', '!=', 'pagado')
            ->where(function ($q) {
                $q->where('estado', 'vencido')
                  ->orWhere('fecha_vencimiento', '<', now()->toDateString());
            })
            ->exists();

            if ($hasOverdue) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'payment_required',
                        'message' => 'Tiene cuotas vencidas pendientes de pago.',
                        'redirect' => route('pagos.index'),
                    ], 403);
                }

                return redirect()->route('pagos.index')->withErrors([
                    'payment_required' => 'Debes regularizar tus cuotas vencidas antes de poder navegar en la plataforma.'
                ]);
            }
        }

        return $next($request);
    }
}
