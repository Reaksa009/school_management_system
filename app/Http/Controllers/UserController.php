<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->role))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.form', ['user' => new User()]);
    }

    public function store(Request $request)
    {
        User::create($this->validated($request));

        return redirect()->route('users.index')->with('success', 'បង្កើតអ្នកប្រើប្រាស់បានជោគជ័យ។');
    }

    public function edit(User $user)
    {
        return view('users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'កែប្រែអ្នកប្រើប្រាស់បានជោគជ័យ។');
    }

    public function destroy(User $user)
    {
        abort_if($user->is(auth()->user()), 422, 'មិនអាចលុបគណនីខ្លួនឯងបានទេ។');

        $user->delete();

        return redirect()->route('users.index')->with('success', 'លុបអ្នកប្រើប្រាស់បានជោគជ័យ។');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->getKey(), (new User())->getKeyName())],
            'role' => ['required', Rule::in(array_keys($this->roles()))],
            'phone' => ['nullable', 'max:50'],
            'password' => [$user ? 'nullable' : 'required', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    public static function roles(): array
    {
        return [
            'admin' => 'អ្នកគ្រប់គ្រង',
            'teacher' => 'គ្រូ',
            'accountant' => 'គណនេយ្យករ',
            'student' => 'សិស្ស',
            'student_parent' => 'សិស្ស/អាណាព្យាបាល',
        ];
    }
}
