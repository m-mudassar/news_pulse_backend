<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return ResponseHelper::error('Login credentials are not valid.', 401);
        }

        return $this->respondWithToken($token);
    }

    public function register(RegisterUserRequest $request)
    {
        try {
            $rules = [
                'name' => 'required',
                'email' => 'unique:users|required',
                'password' => 'required',
            ];

            $input = $request->only('name', 'email', 'password');
            $validator = Validator::make($input, $rules);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'error' => $validator->messages()]);
            }
            $name = $request->name;
            $email = $request->email;
            $password = $request->password;
            User::create(['name' => $name, 'email' => $email, 'password' => Hash::make($password)]);
            return ResponseHelper::success("User registered successfully");
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user = Auth::user();
        $minutes = auth()->factory()->getTTL() * 60;
        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $minutes
        ])->withCookie(cookie('token', $token, $minutes, '/', null, true, true));
    }
}
