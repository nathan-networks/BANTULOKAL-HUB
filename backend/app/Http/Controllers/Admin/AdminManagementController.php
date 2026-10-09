<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class AdminManagementController extends Controller
{
    public function index()
    {
        $admins = User::query()
            ->where('is_admin', true)
            ->orderBy('name')
            ->paginate(15);

        $auditLogs = AdminAuditLog::query()
            ->with([
                'actor:id,name,email',
                'target:id,name,email',
            ])
            ->latest()
            ->paginate(15, ['*'], 'audit_page');

        return view('admin.admins.index', compact('admins', 'auditLogs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(12)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $admin = DB::transaction(function () use ($validated, $request) {
            $admin = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            // Grant privileges explicitly in trusted server-side code.
            $admin->forceFill([
                'is_admin' => true,
                'is_active' => true,
            ])->save();

            AdminAuditLog::create([
                'actor_user_id' => $request->user()->id,
                'target_user_id' => $admin->id,
                'action' => 'admin_created',
                'details' => [
                    'email' => $admin->email,
                    'is_admin' => true,
                    'is_active' => true,
                ],
                'ip_address' => $request->ip(),
            ]);

            return $admin;
        });

        return redirect()
            ->route('admin.admins.index')
            ->with('success', "Administrator {$admin->name} berhasil dibuat.");
    }

    public function updateStatus(Request $request, User $user)
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $newStatus = (bool) $validated['is_active'];

        if (! $user->is_admin) {
            return back()->withErrors([
                'admin' => 'Akun tersebut bukan administrator.',
            ]);
        }

        if (! $newStatus && $request->user()->id === $user->id) {
            return back()->withErrors([
                'admin' => 'Anda tidak dapat menonaktifkan akun sendiri.',
            ]);
        }

        try {
            DB::transaction(function () use ($request, $user, $newStatus) {
                // Lock the admin rows while enforcing the last-admin rule.
                $admins = User::query()
                    ->where('is_admin', true)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $target = $admins->firstWhere('id', $user->id);

                if (! $target) {
                    throw ValidationException::withMessages([
                        'admin' => 'Akun administrator tidak ditemukan.',
                    ]);
                }

                if (! $newStatus && $target->is_active) {
                    $activeAdminCount = $admins
                        ->where('is_active', true)
                        ->count();

                    if ($activeAdminCount <= 1) {
                        throw ValidationException::withMessages([
                            'admin' => 'Administrator aktif terakhir tidak dapat dinonaktifkan.',
                        ]);
                    }
                }

                $oldStatus = (bool) $target->is_active;

                if ($oldStatus === $newStatus) {
                    return;
                }

                $target->is_active = $newStatus;
                $target->save();

                AdminAuditLog::create([
                    'actor_user_id' => $request->user()->id,
                    'target_user_id' => $target->id,
                    'action' => $newStatus
                        ? 'admin_activated'
                        : 'admin_deactivated',
                    'details' => [
                        'old_is_active' => $oldStatus,
                        'new_is_active' => $newStatus,
                    ],
                    'ip_address' => $request->ip(),
                ]);
            }, 3);
        } catch (QueryException $exception) {
            report($exception);

            return back()->withErrors([
                'admin' => 'Perubahan status gagal disimpan. Silakan coba lagi.',
            ]);
        }

        return redirect()
            ->route('admin.admins.index')
            ->with(
                'success',
                $newStatus
                    ? 'Administrator berhasil diaktifkan.'
                    : 'Administrator berhasil dinonaktifkan.'
            );
    }
}
