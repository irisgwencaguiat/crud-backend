<?php

namespace App\Http\Controllers;

use App\Http\Requests\Authentication\LoginRequest;
use App\Http\Requests\Authentication\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{
    public function register(RegisterRequest $request) {
        $data = $request->validated();

        $user = User::create($data + ['user_type' => 'guest'])->fresh();

        $credentials = [
            "email" => $request->get("email"),
            "password" => $request->get("password"),
        ];

        if (!Auth::attempt($credentials)) {
            return customResponse()
                ->data([])
                ->message("Failed to sign up.")
                ->unathorized()
                ->generate();
        }

        $accessToken = Auth::user()->createToken("authToken")->accessToken;

        return customResponse()
            ->data(['user' => $user,
                'access_token' => $accessToken])
            ->message('Successfully created record')
            ->success()
            ->generate();
    }

    public function login(LoginRequest $request) {
        $credentials = [
            "password" => $request->get("password"),
        ];

        $username = $request->get('username');

        if(filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = $username;
        } else {
            $credentials['username'] = $username;
        }

        if (!Auth::attempt($credentials)) {
            $userExist = User::orWhere('email', $username)
                ->orWhere('username', $username)->first();
            if (is_null($userExist)) {
                return customResponse()
                    ->data([])
                    ->message("Account doesn’t exist. Please create an account before logging in.")
                    ->unathorized()
                    ->generate();
            }
            return customResponse()
                ->data([])
                ->message("Invalid credentials.")
                ->unathorized()
                ->generate();
        }

        $accessToken = Auth::user()->createToken("authToken")->accessToken;

        $user = User::find(Auth::id());

        return customResponse()
            ->data(['user' => $user,
                'access_token' => $accessToken])
            ->message('Successfully logged in.')
            ->success()
            ->generate();
    }
}
