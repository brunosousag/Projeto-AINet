<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserFormRequest;
use App\Models\User;
use App\Traits\UserPhotoFileStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    use UserPhotoFileStorage;

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
            'collaboratorTypeLabels' => $this->collaboratorTypeLabels(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('admin');

        $user = new User([
            'user_type' => 'F',
            'gender' => 'M',
            'blocked' => false,
        ]);

        return view('users.create', [
            'user' => $user,
            'typeLabels' => $this->collaboratorTypeLabels(),
        ]);
    }

    public function store(UserFormRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = new User;
        $user->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'email_verified_at' => now(),
            'password' => Hash::make(($validated['password'] ?? null) ?: '123'),
            'user_type' => $validated['user_type'],
            'gender' => $validated['gender'],
            'blocked' => $request->boolean('blocked'),
        ])->save();

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
            'typeLabels' => $this->typeLabels(),
        ]);
    }

    public function edit(User $user): View
    {
        Gate::authorize('admin');
        abort_if($user->isCustomer(), 403);

        return view('users.edit', [
            'user' => $user->load('customer'),
            'typeLabels' => $this->collaboratorTypeLabels(),
        ]);
    }

    public function update(UserFormRequest $request, User $user): RedirectResponse
    {
        abort_if($user->isCustomer(), 403);

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
        abort_if($user->isCustomer(), 403);

        $validated = $request->validate([
            'user_type' => ['required', Rule::in(['F', 'A'])],
        ]);

        if ($request->user()->is($user) && $validated['user_type'] !== 'A') {
            return back()
                ->with('alert-type', 'warning')
                ->with('alert-msg', 'You cannot change your own administrator type.');
        }

        $user->forceFill(['user_type' => $validated['user_type']])->save();

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

    public function destroyPhoto(User $user): RedirectResponse
    {
        Gate::authorize('admin');
        abort_if($user->isCustomer(), 403);

        $this->deleteUserPhoto($user);

        return back()
            ->with('alert-type', 'success')
            ->with('alert-msg', "Photo of '{$user->name}' deleted.");
    }

    private function storePhoto(UserFormRequest $request, User $user): void
    {
        $this->storeUserPhoto($request->file('photo_file'), $user);
    }

    private function typeLabels(): array
    {
        return [
            'C' => 'Customer',
            'F' => 'Employee',
            'A' => 'Administrator',
        ];
    }

    private function collaboratorTypeLabels(): array
    {
        return [
            'F' => 'Employee',
            'A' => 'Administrator',
        ];
    }
}
