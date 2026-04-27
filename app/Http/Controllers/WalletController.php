<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class WalletController extends Controller
{
    #[OA\Get(
        path: '/wallet',
        summary: 'Get wallet balance',
        description: 'Returns the authenticated user\'s wallet balance and virtual account details. (Implemented in Module 5)',
        security: [['sanctum' => []]],
        tags: ['Wallet'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Wallet details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'currency', type: 'string', example: 'NGN'),
                            new OA\Property(property: 'balance_kobo', type: 'integer', example: 500000),
                            new OA\Property(property: 'balance_formatted', type: 'string', example: '₦5,000.00'),
                            new OA\Property(property: 'account_number', type: 'string', example: '0123456789'),
                            new OA\Property(property: 'bank_name', type: 'string', example: 'Wema Bank'),
                        ]),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
        ]
    )]
    public function show()
    {
        return response()->json(['message' => 'Coming in Module 5.']);
    }

    #[OA\Get(
        path: '/transactions',
        summary: 'Get transaction history',
        description: 'Returns a paginated list of the authenticated user\'s ledger entries. (Implemented in Module 5)',
        security: [['sanctum' => []]],
        tags: ['Wallet'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 20)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Transaction list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'reference', type: 'string', example: 'paystack_ref_xyz'),
                                new OA\Property(property: 'description', type: 'string', example: 'Deposit via virtual account'),
                                new OA\Property(property: 'type', type: 'string', example: 'credit'),
                                new OA\Property(property: 'amount_kobo', type: 'integer', example: 500000),
                                new OA\Property(property: 'amount_formatted', type: 'string', example: '₦5,000.00'),
                                new OA\Property(property: 'created_at', type: 'string', example: '2026-04-27T10:00:00Z'),
                            ]
                        )),
                        new OA\Property(property: 'meta', type: 'object', properties: [
                            new OA\Property(property: 'current_page', type: 'integer', example: 1),
                            new OA\Property(property: 'per_page', type: 'integer', example: 20),
                            new OA\Property(property: 'total', type: 'integer', example: 5),
                        ]),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
        ]
    )]
    public function transactions()
    {
        return response()->json(['message' => 'Coming in Module 5.']);
    }
}
