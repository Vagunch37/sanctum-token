<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Token;
use App\Http\Resources\User as UserResource;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);
 
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
       
        $token = $user->createToken('auth-token')->plainTextToken;

        Token::create([
            'user_id' => $user->id,
            'token' => $token,
        ]);
 
        return response()->json(['token' => $token], 200);
    }

    public function login(Request $request)
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];
 
        if (auth()->attempt($data)) {
            $user = $request->user();
            $token = $user->createToken('auth-token')->plainTextToken;

            
            Token::create([
                'user_id' => $user->id,
                'token' => $token,
            ]);

            return response()->json(['token' => $token], 200);
        } else {
            
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function user(Request $request)
    {
        $user = $request->user();
        return new UserResource($user);
    }

    public function users(Request $request)
    {
        $users = User::all();
        return UserResource::collection($users);
    }

    public function generateToken(Request $request)
    {
        $user = $request->user();
        $token = $user->createToken('auth-token')->plainTextToken;

        Token::create([
            'user_id' => $user->id,
            'token' => $token,
        ]);

        return response()->json(['token' => $token], 200);
    }

    public function userTokens(Request $request)
    {
        $user = $request->user();
        $tokens = $user->user_tokens;

        return response()->json(['tokens' => $tokens], 200);
    }

}
