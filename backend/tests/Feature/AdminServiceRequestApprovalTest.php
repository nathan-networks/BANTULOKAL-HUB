<?php

namespace Tests\Feature;

use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminServiceRequestApprovalTest extends TestCase
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
            $table->string('invoice_number')->nullable();
            $table->timestamp('acknowledgement_sent_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_admin_can_approve_request_and_calculate_the_correct_price_split(): void
    {
        $admin = User::factory()->create();

        $category = ServiceCategory::create([
            'name' => 'Desain poster',
            'slug' => 'desain-poster',
            'description' => 'Test category',
        ]);

        $serviceRequest = ServiceRequest::create([
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'customer_whatsapp' => '081234567890',
            'location' => 'Surabaya',
            'category_id' => $category->id,
            'description' => 'Test poster design',
            'worker_count' => 1,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'terms_version' => 'service-request-v1',
            'terms_accepted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.service-requests.review', $serviceRequest), [
                'decision' => 'approved',
                'service_fee' => 125000,
                'approved_expenses' => 50000,
                'expense_notes' => 'Approved transport expense',
                'admin_notes' => 'Approval test',
            ])
            ->assertRedirect(
                route('admin.service-requests.show', $serviceRequest)
            )
            ->assertSessionHas('success');

        $serviceRequest->refresh();

        $this->assertSame('approved', $serviceRequest->status);
        $this->assertSame('125000.00', $serviceRequest->service_fee);
        $this->assertSame('50000.00', $serviceRequest->approved_expenses);
        $this->assertSame('175000.00', $serviceRequest->final_total);
        $this->assertSame('100000.00', $serviceRequest->worker_pool_amount);
        $this->assertSame('25000.00', $serviceRequest->platform_amount);

        $this->actingAs($admin)
            ->from(route('admin.service-requests.show', $serviceRequest))
            ->patch(route('admin.service-requests.review', $serviceRequest), [
                'decision' => 'approved',
                'service_fee' => 999999,
                'approved_expenses' => 0,
            ])
            ->assertRedirect(
                route('admin.service-requests.show', $serviceRequest)
            )
            ->assertSessionHasErrors('decision');

        $serviceRequest->refresh();

        $this->assertSame('175000.00', $serviceRequest->final_total);
        $this->assertSame('100000.00', $serviceRequest->worker_pool_amount);
        $this->assertSame('25000.00', $serviceRequest->platform_amount);
    }
}
