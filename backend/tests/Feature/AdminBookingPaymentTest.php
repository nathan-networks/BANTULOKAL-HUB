<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminBookingPaymentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.apps_script_email.url' => 'https://example.test/email',
            'services.apps_script_email.token' => 'test-token',
        ]);

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

        // Minimal equipment schema needed by the Booking relationship
        // and the rental email templates.
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->decimal('price_per_day', 12, 2)->default(0);
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->string('status')->default('published');
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipment_id');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_whatsapp');
            $table->date('rental_start_date');
            $table->date('rental_end_date');
            $table->decimal('price_per_day', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->string('status')->default('pending');
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->string('terms_version')->nullable();
            $table->timestamp('terms_accepted_at')->nullable();
            $table->decimal('final_rental_amount', 12, 2)->nullable();
            $table->decimal('final_deposit_amount', 12, 2)->nullable();
            $table->string('payment_status')->default('unpaid');
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('payment_verified_by')->nullable();
            $table->string('invoice_number')->nullable()->unique();
            $table->timestamp('acknowledgement_sent_at')->nullable();
            $table->timestamp('payment_instructions_sent_at')->nullable();
            $table->timestamp('payment_confirmation_sent_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('handover_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        DB::table('equipment')->insert([
            'id' => 1,
            'name' => 'Bor Listrik',
            'location' => 'Surabaya',
            'price_per_day' => 50000,
            'deposit_amount' => 100000,
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createBooking(array $attributes = []): Booking
    {
        return Booking::create(array_merge([
            'equipment_id' => 1,
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_whatsapp' => '081234567890',
            'rental_start_date' => '2026-11-01',
            'rental_end_date' => '2026-11-02',
            'price_per_day' => 50000,
            'total_amount' => 100000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'terms_version' => 'rental-terms-v1',
            'terms_accepted_at' => now(),
        ], $attributes));
    }

    public function test_admin_can_approve_rental_and_send_payment_instructions(): void
    {
        Http::fake([
            '*' => Http::response(['success' => true], 200),
        ]);

        $admin = User::factory()->create();
        $admin->forceFill([
            'is_admin' => true,
            'is_active' => true,
        ])->save();

        $admin->forceFill([
            'is_admin' => true,
            'is_active' => true,
        ])->save();
        $booking = $this->createBooking();

        $this->actingAs($admin)
            ->patch(route('admin.bookings.review', $booking), [
                'decision' => 'approved',
                'final_rental_amount' => 100000,
                'final_deposit_amount' => 100000,
                'admin_notes' => 'Rental approval test',
            ])
            ->assertRedirect(route('admin.bookings.show', $booking));

        $booking->refresh();

        $this->assertSame('approved', $booking->status);
        $this->assertSame('100000.00', $booking->final_rental_amount);
        $this->assertSame('100000.00', $booking->final_deposit_amount);
        $this->assertNotNull($booking->approved_at);
        $this->assertNotNull($booking->payment_instructions_sent_at);

        Http::assertSent(function (HttpRequest $request) use ($booking) {
            return $request->url() === config('services.apps_script_email.url')
                && $request['to'] === $booking->customer_email
                && $request['token'] === config('services.apps_script_email.token')
                && str_contains($request['subject'], (string) $booking->id)
                && str_contains($request['html'], '103374578188')
                && str_contains($request['html'], '5063020647');
        });
    }

    public function test_admin_can_verify_rental_payment_and_send_confirmation(): void
    {
        Http::fake([
            '*' => Http::response(['success' => true], 200),
        ]);

        $admin = User::factory()->create();
        $admin->forceFill([
            'is_admin' => true,
            'is_active' => true,
        ])->save();

        $booking = $this->createBooking([
            'status' => 'approved',
            'final_rental_amount' => 100000,
            'final_deposit_amount' => 100000,
            'approved_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.bookings.verify-payment', $booking), [
                'payment_reference' => 'TEST-RENTAL-TRANSFER-001',
            ])
            ->assertRedirect(route('admin.bookings.show', $booking))
            ->assertSessionHas('success');

        $booking->refresh();

        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame(
            'TEST-RENTAL-TRANSFER-001',
            $booking->payment_reference
        );
        $this->assertNotNull($booking->paid_at);
        $this->assertSame($admin->id, $booking->payment_verified_by);
        $this->assertNotEmpty($booking->invoice_number);
        $this->assertNotNull($booking->payment_confirmation_sent_at);

        Http::assertSent(function (HttpRequest $request) use ($booking) {
            return $request->url() === config('services.apps_script_email.url')
                && $request['to'] === $booking->customer_email
                && $request['token'] === config('services.apps_script_email.token')
                && str_contains($request['subject'], $booking->invoice_number);
        });
    }

    public function test_failed_rental_confirmation_email_can_be_retried(): void
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

        $booking = $this->createBooking([
            'status' => 'approved',
            'final_rental_amount' => 100000,
            'final_deposit_amount' => 100000,
            'approved_at' => now(),
        ]);

        // Payment is recorded even if the confirmation email fails.
        $this->actingAs($admin)
            ->patch(route('admin.bookings.verify-payment', $booking), [
                'payment_reference' => 'TEST-RENTAL-RETRY',
            ])
            ->assertRedirect(route('admin.bookings.show', $booking))
            ->assertSessionHas('warning');

        $booking->refresh();

        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame('TEST-RENTAL-RETRY', $booking->payment_reference);
        $this->assertNotNull($booking->paid_at);
        $this->assertNotEmpty($booking->invoice_number);
        $this->assertNull($booking->payment_confirmation_sent_at);

        $originalInvoice = $booking->invoice_number;
        $originalPaidAt = $booking->paid_at->toDateTimeString();

        // Retry the email without verifying or recording payment again.
        $this->post(
            route('admin.bookings.resend-payment-confirmation', $booking)
        )
            ->assertRedirect()
            ->assertSessionHas('success');

        $booking->refresh();

        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame($originalInvoice, $booking->invoice_number);
        $this->assertSame(
            $originalPaidAt,
            $booking->paid_at->toDateTimeString()
        );
        $this->assertSame('TEST-RENTAL-RETRY', $booking->payment_reference);
        $this->assertNotNull($booking->payment_confirmation_sent_at);

        Http::assertSentCount(2);
    }

    public function test_unpaid_rental_cannot_resend_payment_confirmation(): void
    {
        Http::fake();

        $admin = User::factory()->create();
        $admin->forceFill([
            'is_admin' => true,
            'is_active' => true,
        ])->save();
        $booking = $this->createBooking([
            'status' => 'approved',
            'final_rental_amount' => 100000,
            'final_deposit_amount' => 100000,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.bookings.show', $booking))
            ->post(
                route('admin.bookings.resend-payment-confirmation', $booking)
            )
            ->assertRedirect(route('admin.bookings.show', $booking))
            ->assertSessionHasErrors('payment');

        $booking->refresh();

        $this->assertSame('unpaid', $booking->payment_status);

        Http::assertNothingSent();
    }

public function test_unauthenticated_users_cannot_access_admin_booking_actions(): void
{
    $booking = $this->createBooking([
        'status' => 'approved',
        'final_rental_amount' => 100000,
        'final_deposit_amount' => 100000,
    ]);

    $this->get(route('admin.bookings.index'))
        ->assertRedirect(route('admin.login'));

    $this->get(route('admin.bookings.show', $booking))
        ->assertRedirect(route('admin.login'));

    $this->patch(route('admin.bookings.review', $booking), [
        'decision' => 'approved',
        'final_rental_amount' => 100000,
        'final_deposit_amount' => 100000,
    ])->assertRedirect(route('admin.login'));

    $this->patch(route('admin.bookings.verify-payment', $booking), [
        'payment_reference' => 'UNAUTHORIZED',
    ])->assertRedirect(route('admin.login'));

    $this->post(
        route('admin.bookings.resend-payment-confirmation', $booking)
    )->assertRedirect(route('admin.login'));

    $booking->refresh();

    $this->assertSame('approved', $booking->status);
    $this->assertSame('unpaid', $booking->payment_status);
    $this->assertNull($booking->payment_reference);
}

public function test_admin_cannot_approve_rental_with_negative_fees(): void
{
    $admin = User::factory()->create();
    $admin->forceFill([
        'is_admin' => true,
        'is_active' => true,
    ])->save();
    $booking = $this->createBooking();

    $this->actingAs($admin)
        ->from(route('admin.bookings.show', $booking))
        ->patch(route('admin.bookings.review', $booking), [
            'decision' => 'approved',
            'final_rental_amount' => -100,
            'final_deposit_amount' => 100000,
        ])
        ->assertRedirect(route('admin.bookings.show', $booking))
        ->assertSessionHasErrors('final_rental_amount');

    $booking->refresh();

    $this->assertSame('pending', $booking->status);
    $this->assertNull($booking->approved_at);
    $this->assertSame('unpaid', $booking->payment_status);
}

public function test_rejected_rental_cannot_be_approved_later(): void
{
    $admin = User::factory()->create();
        $admin->forceFill([
            'is_admin' => true,
            'is_active' => true,
        ])->save();
    $booking = $this->createBooking([
        'status' => 'rejected',
        'rejected_at' => now(),
    ]);

    $this->actingAs($admin)
        ->from(route('admin.bookings.show', $booking))
        ->patch(route('admin.bookings.review', $booking), [
            'decision' => 'approved',
            'final_rental_amount' => 100000,
            'final_deposit_amount' => 100000,
        ])
        ->assertRedirect(route('admin.bookings.show', $booking))
        ->assertSessionHasErrors('decision');

    $booking->refresh();

    $this->assertSame('rejected', $booking->status);
    $this->assertNull($booking->approved_at);
    $this->assertSame('unpaid', $booking->payment_status);
}

public function test_failed_payment_instructions_email_keeps_approval_and_warns_admin(): void
{
    Http::fake([
        '*' => Http::response(['success' => false], 500),
    ]);

    $admin = User::factory()->create();
            $admin->forceFill([
            'is_admin' => true,
            'is_active' => true,
        ])->save();
    $booking = $this->createBooking();

    $this->actingAs($admin)
        ->patch(route('admin.bookings.review', $booking), [
            'decision' => 'approved',
            'final_rental_amount' => 100000,
            'final_deposit_amount' => 100000,
        ])
        ->assertRedirect(route('admin.bookings.show', $booking))
        ->assertSessionHas('warning');

    $booking->refresh();

    $this->assertSame('approved', $booking->status);
    $this->assertSame('unpaid', $booking->payment_status);
    $this->assertNull($booking->payment_instructions_sent_at);
}
}