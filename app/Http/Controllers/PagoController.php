<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerarQrRequest;
use App\Models\Cuota;
use App\Services\PagoFacilService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PagoController extends Controller
{
    public function __construct(
        private PagoFacilService $pagoService
    ) {
    }

    /**
     * Display student's cuotas with payment status.
     */
    public function index(Request $request): Response
    {
        // Actualizar cuotas que ya pasaron su fecha de vencimiento y no han sido pagadas
        \App\Models\Cuota::where('estado', '!=', 'pagado')
            ->where('fecha_vencimiento', '<', now()->toDateString())
            ->where('estado', '!=', 'vencido')
            ->update(['estado' => 'vencido']);

        $user = $request->user();

        if ($user->is_propietario || $user->is_director || $user->is_secretaria) {
            $search = strtolower(request('search'));
            $metodo = request('metodo_pago');
            $estado = request('estado');

            $query = \App\Models\Pago::with([
                'cuota.matriculaPeriodo.matriculaCarrera.usuario',
                'cuota.matriculaPeriodo.matriculaCarrera.ofertaAcademica',
                'cuota.matriculaPeriodo.periodoAcademico'
            ]);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(transaccion_id) LIKE ?', ["%{$search}%"])
                        ->orWhereHas('cuota.matriculaPeriodo.matriculaCarrera.usuario', function ($q) use ($search) {
                            $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                                ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                                ->orWhereRaw('LOWER(codigo_estudiante) LIKE ?', ["%{$search}%"]);
                        });
                });
            }

            if ($metodo) {
                $query->where('metodo_pago', $metodo);
            }

            if ($estado) {
                $query->where('estado', $estado);
            }

            $pagos = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

            return Inertia::render('Pago/AdminIndex', [
                'pagos' => $pagos,
                'filters' => request()->only(['search', 'metodo_pago', 'estado']),
            ]);
        }

        $cuotas = Cuota::whereHas('matriculaPeriodo.matriculaCarrera', function ($query) use ($user) {
            $query->where('usuario_id', $user->id);
        })->with(['matriculaPeriodo.periodoAcademico', 'pago'])
            ->orderBy('fecha_vencimiento')
            ->get();

        return Inertia::render('Pago/Index', [
            'cuotas' => $cuotas,
        ]);
    }

    /**
     * Generate a mock QR code for a pending cuota.
     */
    public function generarQr(GenerarQrRequest $request): JsonResponse
    {
        $cuota = Cuota::findOrFail($request->cuota_id);
        $result = $this->pagoService->generarQr($cuota);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json($result);
    }

    /**
     * Poll payment status for a transaction.
     */
    public function consultarEstado(string $transaccion): JsonResponse
    {
        $result = $this->pagoService->consultarEstado($transaccion);

        return response()->json($result);
    }

    /**
     * Handle webhook from PagoFácil (simulated).
     */
    public function webhook(Request $request): JsonResponse
    {
        $request->validate([
            'transaccion_id' => 'required|string',
        ]);

        $result = $this->pagoService->simularPago($request->transaccion_id);

        if (!$result) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        return response()->json(['message' => 'Payment processed']);
    }

    /**
     * Handle real webhook callback from PagoFácil.
     */
    public function pagofacilCallback(Request $request): JsonResponse
    {
        // if (config('pagofacil.enable_logs', true)) {
        //     \Illuminate\Support\Facades\Log::info('PagoFacil Webhook Callback received', $request->all());
        // }

        $pedidoId = $request->input('tcPedidoID') ?? $request->input('PedidoID');
        $transaccionId = $request->input('tcTransaccionID') ?? $request->input('Identificador');
        $estado = $request->input('tcEstado') ?? $request->input('Estado');

        if (!$pedidoId && !$transaccionId) {
            return response()->json(['message' => 'Invalid parameters'], 400);
        }

        $pago = null;
        if ($transaccionId) {
            $pago = \App\Models\Pago::where('transaccion_id', $transaccionId)->first();
        }
        if (!$pago && $pedidoId) {
            if (preg_match('/^CUOTA-(\d+)/', $pedidoId, $matches)) {
                $cuotaId = $matches[1];
                $pago = \App\Models\Pago::where('cuota_id', $cuotaId)->first();
            }
        }

        if (!$pago) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // PagoFacil uses 2 for successful/completed payments
        if ($estado == 2 || strtolower((string) $estado) === 'completado' || strtolower((string) $estado) === 'pagado') {
            $pago->update(['estado' => 'completado']);
            $pago->cuota->update(['estado' => 'pagado']);
            return response()->json([
                'error' => 0,
                'status' => 1,
                'message' => 'Payment completed successfully',
                'values' => true
            ]);
        }

        return response()->json([
            'error' => 0,
            'status' => 1,
            'message' => 'Payment status updated: ' . $estado,
            'values' => true
        ]);
    }
}
