<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccessController extends Controller
{
    public function login(LoginRequest $request)
    {
        $post = $request->json()->all();

        $user = User::where('email', $post['email'])->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found']);
        }

        if (!Hash::check($post['password'], $user->password)) {
            return response()->json(['status' => 'error', 'message' => 'Password mismatch']);

        }
        Auth::loginUsingId(($user['id']));
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $update = $user->update([
            'api_token' => $token
        ]);
        $data = ['access_token' => $token, 'token_type' => 'Bearer'];
        return response()->json(['status' => 'success', 'result' => $data]);
    }

}
