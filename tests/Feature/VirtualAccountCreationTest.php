<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Jobs\ProvisionVirtualAccountJob;
use Illuminate\Support\Facades\Queue;

class VirtualAccountCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function registration_dispatches_provision_virtual_account_job()
    {
        Queue::fake();

        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertCreated();
        $user = User::first();
        Queue::assertPushed(ProvisionVirtualAccountJob::class, function ($job) use ($user) {
            return $job->user->is($user);
        });
    }
}
