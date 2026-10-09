<?php

namespace Tests\Feature;

use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;

class ResendPaymentConfirmationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_whatsapp');
            $table->string('location');
            $table->foreignId('category_id')
                ->constrained('service_categories');
            $table->text('description');
            $table->unsignedTinyInteger('worker_count')->default(1);
            $table->date('preferred_date')->nullable();
            $table->decimal('estimated_total', 12, 2)->nullable();
            $table->decimal('final_total', 12, 2)->nullable();
            $table->decimal('service_fee', 12, 2)->nullable();
            $table->decimal('approved_expenses', 12, 2)->default(0);
            $table->text('expense_notes')->nullable();
            $table->decimal('worker_pool_amount', 12, 2)->nullable();
            $table->decimal('platform_amount', 12, 2)->nullable();
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->string('terms_version')->nullable();
            $table->timestamp('terms_accepted_at')->nullable();
            $table->string('payment_status')->default('unpaid');
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('payment_verified_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->string('invoice_number')->nullable();
            $table->timestamp('acknowledgement_sent_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_unpaid_service_request_cannot_resend_payment_confirmation(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill([
            'is_admin' => true,
            'is_active' => true,
        ])->save();

        $category = ServiceCategory::create([
            'name' => 'Bersih-bersih',
            'slug' => 'bersih-bersih',
            'description' => 'Test category',
        ]);

        $serviceRequest = ServiceRequest::create([
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_whatsapp' => '081234567890',
            'location' => 'Surabaya',
            'category_id' => $category->id,
            'description' => 'Automated test request',
            'status' => 'approved',
            'payment_status' => 'unpaid',
            'terms_version' => 'test-v1',
            'terms_accepted_at' => now(),
            'final_total' => 120000,
        ]);

        $this->actingAs($admin)
            ->post(route(
                'admin.service-requests.resend-payment-confirmation',
                $serviceRequest
            ))
            ->assertSessionHasErrors('payment_confirmation_email');

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'payment_status' => 'unpaid',
        ]);
    }


    public function test_admin_can_resend_confirmation_for_a_paid_request(): void
    {
        Http::fake([
            '*' => Http::response(['success' => true], 200),
        ]);

        $admin = User::factory()->create();
        $admin->forceFill([
            'is_admin' => true,
            'is_active' => true,
        ])->save();

        $category = ServiceCategory::create([
            'name' => 'Bersih-bersih',
            'slug' => 'bersih-bersih',
            'description' => 'Test category',
        ]);

        $serviceRequest = ServiceRequest::create([
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_whatsapp' => '081234567890',
            'location' => 'Surabaya',
            'category_id' => $category->id,
            'description' => 'Automated paid request',
            'status' => 'approved',
            'payment_status' => 'paid',
            'terms_version' => 'test-v1',
            'terms_accepted_at' => now(),
            'final_total' => 120000,
            'paid_at' => now(),
            'invoice_number' => 'BLH-TEST-000001',
        ]);

        $this->actingAs($admin)
            ->post(route(
                'admin.service-requests.resend-payment-confirmation',
                $serviceRequest
            ))
            ->assertRedirect()
            ->assertSessionHas(
                'success',
                'Email konfirmasi pembayaran berhasil dikirim ulang ke pelanggan.'
            );

        Http::assertSent(function (HttpRequest $request) use ($serviceRequest) {
            return $request->url() === config('services.apps_script_email.url')
                && $request['to'] === $serviceRequest->customer_email
                && $request['token'] === config('services.apps_script_email.token')
                && str_contains($request['subject'], 'BLH-TEST-000001')
                && str_contains($request['html'], 'Rp120.000');
        });

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'payment_status' => 'paid',
            'invoice_number' => 'BLH-TEST-000001',
        ]);
    }


    public function test_admin_can_verify_payment_and_send_confirmation(): void
    {
        Http::fake([
            '*' => Http::response(['success' => true], 200),
        ]);

        $admin = User::factory()->create();
        $admin->forceFill([
            'is_admin' => true,
            'is_active' => true,
        ])->save();

        $category = ServiceCategory::create([
            'name' => 'Bersih-bersih',
            'slug' => 'bersih-bersih',
            'description' => 'Test category',
        ]);

        $serviceRequest = ServiceRequest::create([
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_whatsapp' => '081234567890',
            'location' => 'Surabaya',
            'category_id' => $category->id,
            'description' => 'Automated payment verification test',
            'status' => 'approved',
            'payment_status' => 'unpaid',
            'terms_version' => 'test-v1',
            'terms_accepted_at' => now(),
            'final_total' => 120000,
        ]);

        $this->actingAs($admin)
            ->patch(
                route(
                    'admin.service-requests.verify-payment',
                    $serviceRequest
                ),
                ['payment_reference' => 'TEST-TRANSFER-001']
            )
            ->assertRedirect()
            ->assertSessionHas('success');

        $serviceRequest->refresh();

        $this->assertSame('paid', $serviceRequest->payment_status);
        $this->assertSame('TEST-TRANSFER-001', $serviceRequest->payment_reference);
        $this->assertNotNull($serviceRequest->paid_at);
        $this->assertSame($admin->id, $serviceRequest->payment_verified_by);
        $this->assertNotEmpty($serviceRequest->invoice_number);

        Http::assertSent(function (HttpRequest $request) use ($serviceRequest) {
            return $request->url() === config('services.apps_script_email.url')
                && $request['to'] === $serviceRequest->customer_email
                && $request['token'] === config('services.apps_script_email.token')
                && str_contains(
                    $request['subject'],
                    $serviceRequest->invoice_number
                );
        });
    }

    public function test_failed_confirmation_email_can_be_retried_without_changing_payment(): void
    {
        Http::fake([
            '*' => Http::sequence()
                ->push(['success' => false], 500)
                ->push(['success' => true], 200),
        ]);

        $admin = User::factory()->create();
        $admin->forceFill([
            'is_admin' => true,
            'is_active' => true,
        ])->save();

        $category = ServiceCategory::create([
            'name' => 'Bersih-bersih',
            'slug' => 'bersih-bersih',
            'description' => 'Test category',
        ]);

        $serviceRequest = ServiceRequest::create([
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_whatsapp' => '081234567890',
            'location' => 'Surabaya',
            'category_id' => $category->id,
            'description' => 'Email failure recovery test',
            'status' => 'approved',
            'payment_status' => 'unpaid',
            'terms_version' => 'test-v1',
            'terms_accepted_at' => now(),
            'final_total' => 120000,
        ]);

        // First attempt: payment verification succeeds, email delivery fails.
        $this->actingAs($admin)
            ->patch(
                route(
                    'admin.service-requests.verify-payment',
                    $serviceRequest
                ),
                ['payment_reference' => 'TEST-TRANSFER-RETRY']
            )
            ->assertRedirect();

        $serviceRequest->refresh();

        $this->assertSame('paid', $serviceRequest->payment_status);
        $this->assertSame(
            'TEST-TRANSFER-RETRY',
            $serviceRequest->payment_reference
        );
        $this->assertNotNull($serviceRequest->paid_at);
        $this->assertNotEmpty($serviceRequest->invoice_number);

        $originalInvoice = $serviceRequest->invoice_number;
        $originalPaidAt = $serviceRequest->paid_at->toDateTimeString();

        // Second attempt: resend succeeds.
        $this->post(
            route(
                'admin.service-requests.resend-payment-confirmation',
                $serviceRequest
            )
        )
            ->assertRedirect()
            ->assertSessionHas(
                'success',
                'Email konfirmasi pembayaran berhasil dikirim ulang ke pelanggan.'
            );

        $serviceRequest->refresh();

        $this->assertSame('paid', $serviceRequest->payment_status);
        $this->assertSame($originalInvoice, $serviceRequest->invoice_number);
        $this->assertSame(
            $originalPaidAt,
            $serviceRequest->paid_at->toDateTimeString()
        );
        $this->assertSame(
            'TEST-TRANSFER-RETRY',
            $serviceRequest->payment_reference
        );

        Http::assertSentCount(2);
    }

}
