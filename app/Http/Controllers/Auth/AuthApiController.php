<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\SignupActivate;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthApiController extends Controller
{
    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed',
        ]);

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'activation_token' => str_random(60),
        ]);

        $user->save();

        $user->notify(new SignUpActivate($user));

        return response()->json([
            'message' => 'Sucesfully created user!',
        ], 201);
    }

    public function signUpActivate($token)
    {
        $user = User::find($token, 'activation_token')->first();

        if (!$user) {
            return response()->json([
                'message' => 'This activation token is invalid',
            ], 404);
        }

        $user->active = true;

        $user->activation_token = '';

        $user->save();

        return $user;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean',
        ]);

        $credentials = request(['email', 'password']);
        $credentials['active'] = 1;
        $credentials['deleted_at'] = null;

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = $request->user();

        $TokenResult = $user->createToken('Personal Access Token');
        $token = $TokenResult->token;

        if ($request->remember_me) {
            $token->expires_at = Carbon::now()->addWeeks(1);
        }

        $token->save();

        return response()->json([
            'access_token' => $TokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $TokenResult->token->expires_at
            )->toDateTimeString(),
        ]);

    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Logged out',
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
