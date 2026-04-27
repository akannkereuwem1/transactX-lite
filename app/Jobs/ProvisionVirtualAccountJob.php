<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\PaystackService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProvisionVirtualAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 120, 180];

    /**
     * Create a new job instance.
     */
    public function __construct(protected User $user)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(PaystackService $paystack): void
    {
        if ($this->user->virtualAccount()->exists()) {
            Log::info('Virtual account already exists for user, skipping provisioning.', [
                'user_id' => $this->user->id,
            ]);
            return;
        }

        try {
            $accountData = $paystack->createDedicatedVirtualAccount($this->user);

            $this->user->virtualAccount()->create([
                'account_number' => $accountData['account_number'],
                'bank_name' => $accountData['bank_name'],
                'account_name' => $accountData['account_name'],
                'provider' => 'paystack',
                'provider_reference' => $accountData['provider_reference'],
                'currency' => 'NGN',
                'is_active' => true,
            ]);

            Log::info('Successfully provisioned virtual account for user.', [
                'user_id' => $this->user->id,
                'account_number' => $accountData['account_number'],
                'bank_name' => $accountData['bank_name'],
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to provision virtual account for user.', [
                'user_id' => $this->user->id,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
