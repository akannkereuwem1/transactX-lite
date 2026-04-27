<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'TransactX Lite API',
    version: '1.0.0',
    description: 'Backend fintech wallet API — virtual accounts, double-entry ledger, idempotent webhooks.'
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST . '/api',
    description: 'API server'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token',
    description: 'Enter your Sanctum bearer token. Example: 1|abcdef...'
)]
#[OA\Tag(name: 'Auth', description: 'Registration, login and logout')]
#[OA\Tag(name: 'Wallet', description: 'Wallet balance and transaction history')]
#[OA\Tag(name: 'Webhooks', description: 'Paystack webhook receiver')]
class SwaggerInfo
{
}
