<?php

namespace Tests\Feature;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminManagementTest extends TestCase
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

        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->unsignedBigInteger('target_user_id')->nullable();
            $table->string('action', 100);
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['target_user_id', 'created_at']);
        });
    }

    private function createAdmin(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        $user->forceFill([
            'is_admin' => true,
            'is_active' => true,
        ])->save();

        return $user->fresh();
    }

    public function test_admin_can_open_management_page(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.admins.index'))
            ->assertOk()
            ->assertSee('Manajemen Administrator')
            ->assertSee($admin->email);
    }

    public function test_ordinary_user_cannot_open_management_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.admins.index'))
            ->assertForbidden();
    }

    public function test_inactive_admin_cannot_open_management_page(): void
    {
        $admin = $this->createAdmin();
        $admin->is_active = false;
        $admin->save();

        $this->actingAs($admin)
            ->get(route('admin.admins.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_another_admin_and_action_is_audited(): void
    {
        $actor = $this->createAdmin();

        $response = $this->actingAs($actor)
            ->post(route('admin.admins.store'), [
                'name' => 'New Administrator',
                'email' => 'new-admin@example.com',
                'password' => 'StrongPassword!482',
                'password_confirmation' => 'StrongPassword!482',
            ]);

        $response->assertRedirect(route('admin.admins.index'))
            ->assertSessionHas('success');

        $created = User::where('email', 'new-admin@example.com')->firstOrFail();

        $this->assertTrue($created->is_admin);
        $this->assertTrue($created->is_active);
        $this->assertTrue(Hash::check('StrongPassword!482', $created->password));

        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_user_id' => $actor->id,
            'target_user_id' => $created->id,
            'action' => 'admin_created',
        ]);
    }

    public function test_admin_creation_rejects_weak_password(): void
    {
        $actor = $this->createAdmin();

        $this->actingAs($actor)
            ->from(route('admin.admins.index'))
            ->post(route('admin.admins.store'), [
                'name' => 'Weak Password User',
                'email' => 'weak-admin@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('admin.admins.index'))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', [
            'email' => 'weak-admin@example.com',
        ]);
    }

    public function test_admin_creation_rejects_duplicate_email(): void
    {
        $actor = $this->createAdmin();
        User::factory()->create(['email' => 'existing@example.com']);

        $this->actingAs($actor)
            ->from(route('admin.admins.index'))
            ->post(route('admin.admins.store'), [
                'name' => 'Duplicate Email',
                'email' => 'existing@example.com',
                'password' => 'StrongPassword!482',
                'password_confirmation' => 'StrongPassword!482',
            ])
            ->assertRedirect(route('admin.admins.index'))
            ->assertSessionHasErrors('email');
    }

    public function test_admin_cannot_deactivate_themselves(): void
    {
        $actor = $this->createAdmin();
        $anotherAdmin = $this->createAdmin();

        $this->actingAs($actor)
            ->patch(route('admin.admins.update-status', $actor), [
                'is_active' => 0,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('admin');

        $this->assertTrue($actor->fresh()->is_active);
        $this->assertTrue($anotherAdmin->fresh()->is_active);
    }

    public function test_last_active_admin_cannot_be_deactivated(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->patch(route('admin.admins.update-status', $admin), [
                'is_active' => 0,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('admin');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_can_deactivate_another_admin_and_action_is_audited(): void
    {
        $actor = $this->createAdmin();
        $target = $this->createAdmin();

        $this->actingAs($actor)
            ->patch(route('admin.admins.update-status', $target), [
                'is_active' => 0,
            ])
            ->assertRedirect(route('admin.admins.index'))
            ->assertSessionHas('success');

        $this->assertFalse($target->fresh()->is_active);

        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_user_id' => $actor->id,
            'target_user_id' => $target->id,
            'action' => 'admin_deactivated',
        ]);
    }


    public function test_admin_creation_audit_records_actor_target_and_ip(): void
    {
        $actor = $this->createAdmin();

        $this->actingAs($actor)
            ->withServerVariables(['REMOTE_ADDR' => '192.0.2.25'])
            ->post(route('admin.admins.store'), [
                'name' => 'Audited Administrator',
                'email' => 'audited-admin@example.com',
                'password' => 'StrongPassword!482',
                'password_confirmation' => 'StrongPassword!482',
            ])
            ->assertRedirect(route('admin.admins.index'));

        $created = User::where('email', 'audited-admin@example.com')->firstOrFail();

        $log = AdminAuditLog::where('action', 'admin_created')
            ->where('target_user_id', $created->id)
            ->firstOrFail();

        $this->assertSame($actor->id, $log->actor_user_id);
        $this->assertSame($created->id, $log->target_user_id);
        $this->assertSame('192.0.2.25', $log->ip_address);
        $this->assertSame('audited-admin@example.com', $log->details['email']);
        $this->assertTrue($log->details['is_admin']);
        $this->assertTrue($log->details['is_active']);
    }

    public function test_status_change_audit_records_old_and_new_values(): void
    {
        $actor = $this->createAdmin();
        $target = $this->createAdmin();

        $this->actingAs($actor)
            ->withServerVariables(['REMOTE_ADDR' => '192.0.2.26'])
            ->patch(route('admin.admins.update-status', $target), [
                'is_active' => 0,
            ])
            ->assertRedirect(route('admin.admins.index'));

        $log = AdminAuditLog::where('target_user_id', $target->id)
            ->where('action', 'admin_deactivated')
            ->firstOrFail();

        $this->assertSame($actor->id, $log->actor_user_id);
        $this->assertSame($target->id, $log->target_user_id);
        $this->assertSame('192.0.2.26', $log->ip_address);
        $this->assertTrue($log->details['old_is_active']);
        $this->assertFalse($log->details['new_is_active']);
    }

    public function test_unchanged_admin_status_does_not_create_audit_entry(): void
    {
        $actor = $this->createAdmin();
        $target = $this->createAdmin();

        $before = AdminAuditLog::where('target_user_id', $target->id)->count();

        $this->actingAs($actor)
            ->patch(route('admin.admins.update-status', $target), [
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.admins.index'));

        $after = AdminAuditLog::where('target_user_id', $target->id)->count();

        $this->assertSame($before, $after);
        $this->assertTrue($target->fresh()->is_active);
    }

    public function test_admin_creation_rolls_back_if_audit_log_cannot_be_written(): void
    {
        $actor = $this->createAdmin();

        // Remove the audit table to force the audit insert to fail.
        Schema::drop('admin_audit_logs');

        $this->actingAs($actor)
            ->post(route('admin.admins.store'), [
                'name' => 'Should Not Exist',
                'email' => 'rollback-admin@example.com',
                'password' => 'StrongPassword!482',
                'password_confirmation' => 'StrongPassword!482',
            ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'rollback-admin@example.com',
        ]);
    }


    public function test_admin_status_change_rolls_back_if_audit_log_cannot_be_written(): void
    {
        $actor = $this->createAdmin();
        $target = $this->createAdmin();

        // Remove the audit table to force the audit insert to fail.
        Schema::drop('admin_audit_logs');

        try {
            $this->actingAs($actor)
                ->patch(route('admin.admins.update-status', $target), [
                    'is_active' => 0,
                ]);
        } catch (\Throwable $exception) {
            // The request may propagate a database exception.
        }

        $this->assertTrue(
            User::findOrFail($target->id)->is_active,
            'The target admin must remain active if the audit write fails.'
        );
    }


}
