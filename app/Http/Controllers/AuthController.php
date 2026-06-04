<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials['is_active'] = true;

        try {
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();

                return redirect()->intended(route('dashboard'))->with('success', 'Logged in successfully.');
            }
        } catch (Throwable $exception) {
            return $this->databaseUnavailable($request, $exception);
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => 'The email or password is incorrect.']);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['student', 'teacher'])],
            'profile_code' => ['required', 'max:50'],
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'max:50'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        try {
            if (User::where('email', $data['email'])->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'This email is already registered.',
                ]);
            }

            if ($data['role'] === 'student') {
                $profile = Student::where('student_code', $data['profile_code'])->first();

                if (! $profile) {
                    return back()
                        ->withInput($request->except(['password', 'password_confirmation']))
                        ->withErrors(['profile_code' => 'Student code was not found.']);
                }

                if ($profile->user_id) {
                    return back()
                        ->withInput($request->except(['password', 'password_confirmation']))
                        ->withErrors(['profile_code' => 'This student already has a linked account.']);
                }
            } else {
                $profile = Teacher::where('teacher_code', $data['profile_code'])->first();

                if (! $profile) {
                    return back()
                        ->withInput($request->except(['password', 'password_confirmation']))
                        ->withErrors(['profile_code' => 'Teacher code was not found.']);
                }

                if ($profile->user_id) {
                    return back()
                        ->withInput($request->except(['password', 'password_confirmation']))
                        ->withErrors(['profile_code' => 'This teacher already has a linked account.']);
                }
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'phone' => $data['phone'] ?? null,
                'is_active' => false,
                'password' => $data['password'],
            ]);

            $profile->update(['user_id' => $user->id]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            return $this->databaseUnavailable($request, $exception, 'profile_code');
        }

        return redirect()
            ->route('login')
            ->with('success', 'Account request created. Please wait for admin approval before logging in.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }

    private function databaseUnavailable(Request $request, Throwable $exception, string $field = 'email')
    {
        $missingMongoDsn = config('database.default') === 'mongodb'
            && ! config('database.connections.mongodb.dsn');

        if (! $missingMongoDsn) {
            report($exception);
        }

        $message = $missingMongoDsn
            ? 'Database is not configured. Add DB_URI or MONGODB_URI in Vercel.'
            : 'Database is unavailable. Please try again later.';

        return back()
            ->withInput($request->except(['password', 'password_confirmation']))
            ->withErrors([$field => $message]);
    }
}
