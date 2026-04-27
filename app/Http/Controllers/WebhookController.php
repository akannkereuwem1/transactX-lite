<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class WebhookController extends Controller
{
    #[OA\Post(
        path: '/webhooks/paystack',
        summary: 'Receive Paystack webhook',
        description: 'Receives Paystack event notifications. Verifies HMAC SHA512 signature, checks idempotency, and dispatches deposit processing. (Implemented in Module 4)',
        tags: ['Webhooks'],
        parameters: [
            new OA\Parameter(
                name: 'X-Paystack-Signature',
                in: 'header',
                required: true,
                description: 'HMAC SHA512 signature of the raw request body',
                schema: new OA\Schema(type: 'string')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Paystack event payload',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'event', type: 'string', example: 'charge.success'),
                    new OA\Property(property: 'data', type: 'object', properties: [
                        new OA\Property(property: 'reference', type: 'string', example: 'paystack_ref_xyz'),
                        new OA\Property(property: 'amount', type: 'integer', example: 500000),
                        new OA\Property(property: 'customer', type: 'object', properties: [
                            new OA\Property(property: 'email', type: 'string', example: 'ada@example.com'),
                        ]),
                    ]),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Webhook received',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Webhook received.'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid signature',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Invalid signature.'),
                    ]
                )
            ),
        ]
    )]
    public function paystack()
    {
        return response()->json(['message' => 'Coming in Module 4.']);
    }
}
