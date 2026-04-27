<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaystackService
{
    protected string $secretKey;
    protected string $baseUrl;
    protected bool $stubMode;

    public function __construct()
    {
        $this->secretKey = config('paystack.secret_key', '');
        $this->baseUrl = config('paystack.base_url', 'https://api.paystack.co');
        $this->stubMode = config('paystack.stub', false) || empty($this->secretKey);
    }

    /**
     * Create or fetch a customer on Paystack.
     *
     * @param User $user
     * @return string customer_code
     * @throws RuntimeException
     */
    public function createOrFetchCustomer(User $user): string
    {
        if ($this->stubMode) {
            return "CUS_stub_{$user->id}";
        }

        $response = Http::withToken($this->secretKey)
            ->baseUrl($this->baseUrl)
            ->post('/customer', [
                'email' => $user->email,
                'first_name' => $this->extractFirstName($user->name),
                'last_name' => $this->extractLastName($user->name),
            ]);

        if (! $response->successful()) {
            Log::error('Paystack create/fetch customer failed', [
                'user_id' => $user->id,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            throw new RuntimeException('Failed to create or fetch Paystack customer: ' . $response->body());
        }

        return $response->json('data.customer_code');
    }

    /**
     * Create a Dedicated Virtual Account for the user.
     *
     * @param User $user
     * @return array
     * @throws RuntimeException
     */
    public function createDedicatedVirtualAccount(User $user): array
    {
        if ($this->stubMode) {
            return $this->generateStubAccount($user);
        }

        $customerCode = $this->createOrFetchCustomer($user);
        
        $preferredBank = config('paystack.preferred_bank', 'wema-bank');

        $response = Http::withToken($this->secretKey)
            ->baseUrl($this->baseUrl)
            ->post('/dedicated_account', [
                'customer' => $customerCode,
                'preferred_bank' => $preferredBank,
            ]);

        if (! $response->successful()) {
            Log::error('Paystack create dedicated virtual account failed', [
                'user_id' => $user->id,
                'customer_code' => $customerCode,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            throw new RuntimeException('Failed to create Paystack dedicated virtual account: ' . $response->body());
        }

        $data = $response->json('data');

        return [
            'account_number' => $data['account_number'],
            'bank_name' => $data['bank']['name'] ?? $preferredBank,
            'account_name' => $data['account_name'],
            'provider_reference' => (string) $data['id'],
        ];
    }

    /**
     * Generate a deterministic stub virtual account.
     *
     * @param User $user
     * @return array
     */
    protected function generateStubAccount(User $user): array
    {
        $accountNumber = str_pad((string) ($user->id * 7 + 1000000000), 10, '0', STR_PAD_LEFT);

        return [
            'account_number' => $accountNumber,
            'bank_name' => 'Wema Bank (Stub)',
            'account_name' => strtoupper($user->name),
            'provider_reference' => 'stub_dva_' . $user->id,
        ];
    }

    /**
     * Extract the first name from a full name.
     *
     * @param string $fullName
     * @return string
     */
    protected function extractFirstName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));
        return $parts[0] ?: 'User';
    }

    /**
     * Extract the last name from a full name.
     *
     * @param string $fullName
     * @return string
     */
    protected function extractLastName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));
        if (count($parts) > 1) {
            array_shift($parts);
            return implode(' ', $parts);
        }
        
        return $parts[0] ?: 'User';
    }
}
