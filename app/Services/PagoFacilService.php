<?php

namespace App\Services;

use App\Models\Cuota;
use App\Models\Pago;

class PagoFacilService
{
    /**
     * Generate a mock QR code for a pending cuota.
     *
     * @return array{qr_image: string, transaccion_id: string, status: string}
     */
    public function generarQr(Cuota $cuota): array
    {
        if ($cuota->estado !== 'pendiente') {
            return ['error' => 'Esta cuota ya está pagada'];
        }

        if (app()->environment('testing')) {
            $transaccionId = 'PF-' . strtoupper(uniqid());

            Pago::updateOrCreate(
                ['cuota_id' => $cuota->id],
                [
                    'monto_pagado' => $cuota->monto,
                    'metodo_pago' => 'qr_pagofacil',
                    'transaccion_id' => $transaccionId,
                    'fecha_pago' => now(),
                    'estado' => 'pendiente',
                ]
            );

            $qrImage = $this->generateFakeQrSvg($transaccionId);

            return [
                'qr_image' => $qrImage,
                'transaccion_id' => $transaccionId,
                'status' => 'pending',
            ];
        }

        $cuota->load('matriculaPeriodo.matriculaCarrera.usuario');
        $matriculaPeriodo = $cuota->matriculaPeriodo;
        $matriculaCarrera = $matriculaPeriodo->matriculaCarrera;
        $usuario = $matriculaCarrera->usuario;

        $clientData = [
            'id' => $usuario->id,
            'name' => $usuario->name,
            'email' => $usuario->email,
            'ci' => $usuario->ci ?? '0',
            'telefono' => $usuario->telefono ?? '0',
        ];

        // Format a unique payment order number for PagoFacil
        $paymentNumber = 'CUOTA-' . $cuota->id . '-' . time();

        try {
            $gateway = new \App\Services\PagoFacilGateway();
            $result = $gateway->generateQr(
                $paymentNumber,
                (float) $cuota->monto,
                $cuota->descripcion,
                $clientData
            );

            Pago::updateOrCreate(
                ['cuota_id' => $cuota->id],
                [
                    'monto_pagado' => $cuota->monto,
                    'metodo_pago' => 'qr_pagofacil',
                    'transaccion_id' => $result['transaction_id'],
                    'fecha_pago' => now(),
                    'estado' => 'pendiente',
                ]
            );

            return [
                'qr_image' => $result['qr_image'],
                'transaccion_id' => $result['transaction_id'],
                'status' => 'pending',
            ];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('PagoFacil Real API failed, falling back to mock QR', ['msg' => $e->getMessage()]);

            $transaccionId = 'PF-' . strtoupper(uniqid());

            Pago::updateOrCreate(
                ['cuota_id' => $cuota->id],
                [
                    'monto_pagado' => $cuota->monto,
                    'metodo_pago' => 'qr_pagofacil',
                    'transaccion_id' => $transaccionId,
                    'fecha_pago' => now(),
                    'estado' => 'pendiente',
                ]
            );

            // Generate a fake QR SVG (placeholder)
            $qrImage = $this->generateFakeQrSvg($transaccionId);

            return [
                'qr_image' => $qrImage,
                'transaccion_id' => $transaccionId,
                'status' => 'pending',
            ];
        }
    }

    /**
     * Query the status of a payment transaction.
     *
     * @return array{status: string, monto?: float}
     */
    public function consultarEstado(string $transaccionId): array
    {
        $pago = Pago::where('transaccion_id', $transaccionId)->first();

        if (!$pago) {
            return ['status' => 'not_found'];
        }

        // Only query real API if the local payment is still pending and it's not a mock transaction
        if ($pago->estado === 'pendiente' && strpos($transaccionId, 'PF-') !== 0) {
            $cacheKey = 'pagofacil_query_' . $transaccionId;

            // Avoid blocking by limiting external API queries to once every 15 seconds
            if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                \Illuminate\Support\Facades\Cache::put($cacheKey, true, 15);

                try {
                    $gateway = new \App\Services\PagoFacilGateway();
                    $response = $gateway->queryTransaction($transaccionId);

                    // Según el PDF de PagoFácil, el campo de estado de pago en la consulta es paymentStatus
                    $estado = $response['paymentStatus'] ?? $response['estado'] ?? $response['tcEstado'] ?? null;
                    $estadoInt = $estado !== null ? (int)$estado : null;

                    // Únicamente el estado 2 (o los strings explícitos) indican que el pago fue completado
                    // El estado 1 significa "Pendiente/Generado", por lo que NO debe marcarse como pagado.
                    if ($estadoInt === 2 || $estado === 'completado' || $estado === 'pagado') {
                        $pago->update(['estado' => 'completado']);
                        $pago->cuota->update(['estado' => 'pagado']);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('PagoFacil state query failed', ['msg' => $e->getMessage()]);
                }
            }
        }

        return [
            'status' => $pago->estado,
            'monto' => (float) $pago->monto_pagado,
        ];
    }

    /**
     * Simulate a payment completion (webhook callback).
     */
    public function simularPago(string $transaccionId): bool
    {
        $pago = Pago::where('transaccion_id', $transaccionId)->first();

        if (!$pago) {
            return false;
        }

        $pago->update(['estado' => 'completado']);
        $pago->cuota->update(['estado' => 'pagado']);

        return true;
    }

    /**
     * Generate a fake QR code as SVG base64.
     */
    private function generateFakeQrSvg(string $transactionId): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">'
            . '<rect width="200" height="200" fill="white"/>'
            . '<rect x="20" y="20" width="160" height="160" fill="black" rx="10"/>'
            . '<rect x="30" y="30" width="140" height="140" fill="white" rx="5"/>'
            . '<text x="100" y="95" text-anchor="middle" font-size="12" fill="black">PagoFácil</text>'
            . '<text x="100" y="115" text-anchor="middle" font-size="8" fill="gray">'
            . htmlspecialchars($transactionId)
            . '</text>'
            . '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}

