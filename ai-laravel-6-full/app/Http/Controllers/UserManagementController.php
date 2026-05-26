<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserManagementFormRequest;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('admin');

        $search = trim((string) $request->query('search', ''));
        $userType = $request->query('user_type');
        $blocked = $request->query('blocked');

        $users = User::query()
            ->with(['customer' => fn ($query) => $query->withCount(['orders', 'tshirtImages'])])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhereHas('customer', fn ($query) => $query->where('nif', 'like', "%$search%"));

                    if (ctype_digit($search)) {
                        $query->orWhere('id', (int) $search);
                    }
                });
            })
            ->when(in_array($userType, ['C', 'F', 'A'], true), fn ($query) => $query->where('user_type', $userType))
            ->when(in_array($blocked, ['0', '1'], true), fn ($query) => $query->where('blocked', (bool) $blocked))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'user_type' => $userType,
                'blocked' => $blocked,
            ],
            'typeLabels' => $this->typeLabels(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('admin');

        $user = new User([
            'user_type' => 'C',
            'gender' => 'M',
            'blocked' => false,
        ]);

        return view('users.create', [
            'user' => $user,
            'customer' => new Customer(),
            'typeLabels' => $this->typeLabels(),
        ]);
    }

    public function store(UserManagementFormRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = new User();
        $user->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'email_verified_at' => now(),
            'password' => Hash::make(($validated['password'] ?? null) ?: '123'),
            'user_type' => $validated['user_type'],
            'gender' => $validated['gender'],
            'blocked' => $request->boolean('blocked'),
        ])->save();

        if ($user->isCustomer()) {
            $this->saveCustomer($user, $validated);
        }

        $this->storePhoto($request, $user);

        return redirect()
            ->route('users.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "User '{$user->name}' has been created successfully.");
    }

    public function show(User $user): View
    {
        Gate::authorize('admin');

        return view('users.show', [
            'user' => $user->load(['customer' => fn ($query) => $query->withCount(['orders', 'tshirtImages'])]),
            'customer' => $user->customer ?? new Customer(),
            'typeLabels' => $this->typeLabels(),
        ]);
    }

    public function edit(User $user): View
    {
        Gate::authorize('admin');

        return view('users.edit', [
            'user' => $user->load('customer'),
            'customer' => $user->customer ?? new Customer(),
            'typeLabels' => $this->typeLabels(),
        ]);
    }

    public function update(UserManagementFormRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->user()->is($user) && $validated['user_type'] !== 'A') {
            return back()
                ->withInput()
                ->with('alert-type', 'warning')
                ->with('alert-msg', 'You cannot change your own administrator type.');
        }

        if ($request->boolean('blocked') && $request->user()->is($user)) {
            return back()
                ->withInput()
                ->with('alert-type', 'warning')
                ->with('alert-msg', 'You cannot block your own account.');
        }

        $userData = Arr::only($validated, ['name', 'email', 'user_type', 'gender']);
        $userData['blocked'] = $request->boolean('blocked');

        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->forceFill($userData)->save();

        if ($user->isCustomer()) {
            $this->saveCustomer($user, $validated);
        }

        $this->storePhoto($request, $user);

        return redirect()
            ->route('users.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "User '{$user->name}' has been updated successfully.");
    }

    public function blockUnblock(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('admin');

        if ($request->user()->is($user)) {
            return back()
                ->with('alert-type', 'warning')
                ->with('alert-msg', 'You cannot block your own account.');
        }

        $user->forceFill(['blocked' => ! $user->blocked])->save();

        return back()
            ->with('alert-type', 'success')
            ->with('alert-msg', "User '{$user->name}' is now ".($user->blocked ? 'blocked' : 'active').'.');
    }

    public function changeType(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'user_type' => ['required', Rule::in(['C', 'F', 'A'])],
        ]);

        if ($request->user()->is($user) && $validated['user_type'] !== 'A') {
            return back()
                ->with('alert-type', 'warning')
                ->with('alert-msg', 'You cannot change your own administrator type.');
        }

        $user->forceFill(['user_type' => $validated['user_type']])->save();

        if ($user->isCustomer()) {
            $this->saveCustomer($user, []);
        }

        return back()
            ->with('alert-type', 'success')
            ->with('alert-msg', "User '{$user->name}' type has been updated successfully.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('admin');

        if ($request->user()->is($user)) {
            return back()
                ->with('alert-type', 'warning')
                ->with('alert-msg', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "User '{$user->name}' has been deleted successfully.");
    }

    private function saveCustomer(User $user, array $validated): void
    {
        $customer = Customer::withTrashed()->firstOrNew(['id' => $user->id]);
        $customer->fill(Arr::only($validated, [
            'nif',
            'address',
            'default_payment_type',
            'default_payment_ref',
        ]));
        $customer->save();

        if ($customer->trashed()) {
            $customer->restore();
        }
    }

    private function storePhoto(UserManagementFormRequest $request, User $user): void
    {
        if (! $request->hasFile('photo_file')) {
            return;
        }

        $file = $request->file('photo_file');
        $filename = str_pad((string) $user->id, 5, '0', STR_PAD_LEFT)
            .'_'.Str::random(10).'.'.$file->extension();

        Storage::disk('public')->putFileAs('photos', $file, $filename);
        $user->forceFill(['photo_url' => $filename])->save();
    }

    private function typeLabels(): array
    {
        return [
            'C' => 'Customer',
            'F' => 'Employee',
            'A' => 'Administrator',
        ];
    }
}
