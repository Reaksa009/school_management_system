<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))->with('success', 'ចូលប្រើប្រព័ន្ធបានជោគជ័យ។');
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => 'អ៊ីមែល ឬពាក្យសម្ងាត់មិនត្រឹមត្រូវ។']);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['student', 'teacher'])],
            'profile_code' => ['required', 'max:50'],
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'max:50'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        if ($data['role'] === 'student') {
            $profile = Student::where('student_code', $data['profile_code'])->first();

            if (! $profile) {
                return back()->withInput()->withErrors(['profile_code' => 'មិនឃើញលេខសម្គាល់សិស្សនេះទេ។']);
            }

            if ($profile->user_id) {
                return back()->withInput()->withErrors(['profile_code' => 'សិស្សនេះមានគណនីភ្ជាប់រួចហើយ។']);
            }
        } else {
            $profile = Teacher::where('teacher_code', $data['profile_code'])->first();

            if (! $profile) {
                return back()->withInput()->withErrors(['profile_code' => 'មិនឃើញលេខសម្គាល់គ្រូនេះទេ។']);
            }

            if ($profile->user_id) {
                return back()->withInput()->withErrors(['profile_code' => 'គ្រូនេះមានគណនីភ្ជាប់រួចហើយ។']);
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

        return redirect()
            ->route('login')
            ->with('success', 'បានបង្កើតសំណើគណនី។ សូមរង់ចាំ Admin អនុម័តមុនចូលប្រើ។');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'ចាកចេញពីប្រព័ន្ធបានជោគជ័យ។');
    }
}
