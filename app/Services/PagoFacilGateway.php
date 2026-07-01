<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class PagoFacilGateway
{
    protected string $baseUrl;
    protected ?string $tokenService;
    protected ?string $tokenSecret;
    protected int $currency;
    protected string $callbackUrl;
    protected int $timeout;
    protected bool $enableLogs;

    public function __construct()
    {
        $this->baseUrl = config('pagofacil.base_url');
        $this->tokenService = config('pagofacil.token_service');
        $this->tokenSecret = config('pagofacil.token_secret');
        $this->currency = (int) config('pagofacil.currency', 2);
        $this->callbackUrl = config('pagofacil.callback_url');
        $this->timeout = (int) config('pagofacil.timeout', 30);
        $this->enableLogs = (bool) config('pagofacil.enable_logs', true);
    }

    public function generateQr(string $paymentNumber, float $amount, string $concepto, array $clientData): array
    {
        $accessToken = $this->obtenerToken();

        $client = new Client();

        // Determinar dinámicamente el ID del método de pago QR
        $paymentMethodId = 4; // fallback
        try {
            $servicesResponse = $client->post($this->baseUrl . '/list-enabled-services', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'timeout' => $this->timeout,
            ]);
            $servicesData = json_decode($servicesResponse->getBody()->getContents(), true);
            if (isset($servicesData['values']) && is_array($servicesData['values'])) {
                foreach ($servicesData['values'] as $service) {
                    if (isset($service['paymentMethodId']) && isset($service['paymentMethodName'])) {
                        if (stripos($service['paymentMethodName'], 'QR') !== false) {
                            $paymentMethodId = (int) $service['paymentMethodId'];
                            break;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            //if ($this->enableLogs) {
            //  Log::warning('No se pudo listar los servicios habilitados de PagoFacil, usando ID por defecto (4)', ['msg' => $e->getMessage()]);
            //}
        }

        $response = $client->post($this->baseUrl . '/generate-qr', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'json' => [
                'paymentMethod' => $paymentMethodId,
                'clientName' => $clientData['name'],
                'documentType' => 1,
                'documentId' => (string) ($clientData['ci'] ?? '0'),
                'phoneNumber' => (string) ($clientData['telefono'] ?? '0'),
                'email' => $clientData['email'],
                'paymentNumber' => $paymentNumber,
                'amount' => $amount,
                'currency' => $this->currency,
                'clientCode' => (string) $clientData['id'],
                'callbackUrl' => $this->callbackUrl,
                'orderDetail' => [
                    [
                        'serial' => 1,
                        'product' => $concepto,
                        'quantity' => 1,
                        'price' => $amount,
                        'discount' => 0,
                        'total' => $amount
                    ]
                ]
            ],
            'timeout' => $this->timeout,
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($result['values'])) {
            // if ($this->enableLogs) {
            //   Log::error('PagoFacil response invalid or error', ['result' => $result]);
            // }
            throw new \Exception('Respuesta inválida de PagoFácil');
        }

        $values = $result['values'];

        $qrBase64 = $values['qrImage'] ?? $values['qrBase64'] ?? null;
        $transactionId = $values['transactionId']
            ?? $values['idTransaccion']
            ?? $values['codigoTransaccion']
            ?? $values['id']
            ?? null;

        if (!$qrBase64 || !$transactionId) {
            throw new \Exception('No se pudo obtener el QR de PagoFácil. Campos recibidos: ' . implode(', ', array_keys($values)));
        }

        return [
            'qr_image' => 'data:image/png;base64,' . $qrBase64,
            'transaction_id' => $transactionId,
        ];
    }

    public function queryTransaction(string $transactionId): array
    {
        $accessToken = $this->obtenerToken();

        $client = new Client();
        $response = $client->post($this->baseUrl . '/query-transaction', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'json' => ['pagofacilTransactionId' => $transactionId],
            'http_errors' => false,
            'timeout' => 5,
            'connect_timeout' => 3,
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Respuesta de consulta de transacción inválida de PagoFácil');
        }

        if (isset($result['error']) && $result['error'] != 0) {
            throw new \Exception($result['message'] ?? 'Error al consultar transacción en PagoFácil');
        }

        return $result['values'] ?? [];
    }

    private function obtenerToken(): string
    {
        $client = new Client();
        $response = $client->post($this->baseUrl . '/login', [
            'headers' => [
                'Accept' => 'application/json',
                'tcTokenService' => $this->tokenService,
                'tcTokenSecret' => $this->tokenSecret,
            ],
            'timeout' => $this->timeout,
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        // if ($this->enableLogs) {
        //     Log::info('PagoFacil token OK');
        // }

        $token = $result['values']['accessToken'] ?? null;

        if (!$token) {
            throw new \Exception('No se pudo obtener el token de PagoFácil');
        }

        return $token;
    }
}