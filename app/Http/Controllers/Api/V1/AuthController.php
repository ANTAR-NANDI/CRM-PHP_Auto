<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::query()
            ->where(fn ($query) => $query->where('email', $data['login'])->orWhere('employee_code', $data['login']))
            ->first();

        if (! $user || ! $user->is_active || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['login' => 'The employee ID/email or password is incorrect.']);
        }

        if (! $user->hasAnyPharmacyRole(['admin', 'manager', 'cashier', 'salesperson'])) {
            throw ValidationException::withMessages(['login' => 'Your account does not have mobile POS access.']);
        }

        $token = $user->createToken($data['device_name'] ?? 'Pharmacy POS mobile', ['mobile-pos']);

        return response()->json([
            'message' => 'Login successful.',
            'data' => [
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'user' => $this->userData($user),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->userData($request->user())]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'employee_code' => $user->employee_code,
            'email' => $user->email,
            'designation' => $user->designation,
            'roles' => $user->getRoleNames()->values(),
            'primary_role' => $user->getRoleNames()->first(),
        ];
    }
}
