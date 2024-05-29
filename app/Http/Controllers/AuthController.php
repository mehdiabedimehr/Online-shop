<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AuthController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth', except: ['login','register']),

        ];
    }

    /**
    * @OA\Post(
    *     path="/auth/login",
    *     tags={"Authentication"},
    *     summary="login",
    *     description="login",
    *     operationId="login",
  *     @OA\Response(
    *         response=200,
    *         description="Success Message",
    *         @OA\JsonContent(ref="#/components/schemas/SuccessModel"),
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="an 'unexpected' error",
    *         @OA\JsonContent(ref="#/components/schemas/ErrorModel"),
    *     ),
    *     @OA\RequestBody(
    *         description="tasks input",
    *         required=true,
    *         @OA\JsonContent(
    *             @OA\Property(
    *                 property="email",
    *                 type="string",
    *                 description="email",
    *                 example="test@example.com"
    *             ),
    *             @OA\Property(
    *                 property="password",
    *                 type="string",
    *                 description="password",
    *                 default="null",
    *                 example="password",
    *             )
    *         )
    *     )
    * )
    *
    * Get a JWT via given credentials.
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function login()
    {
        $credentials = request(['email', 'password']);
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }
    /**
        * @OA\Post(
        *     path="/auth/register",
        *     tags={"Authentication"},
        *     summary="register",
        *     description="register",
        *     operationId="register",
        *     @OA\Response(
        *         response=200,
        *         description="Success Message",
        *         @OA\JsonContent(ref="#/components/schemas/UserModel"),
        *     ),
        *     @OA\Response(
        *         response=400,
        *         description="an 'unexpected' error",
        *         @OA\JsonContent(ref="#/components/schemas/ErrorModel"),
        *     ),
        *     @OA\RequestBody(
        *         description="tasks input",
        *         required=true,
        *         @OA\JsonContent(
        *             @OA\Property(
        *                 property="first_name",
        *                 type="string",
        *                 description="first_name",
        *                 example="string"
        *             ),
        *             @OA\Property(
        *                 property="last_name",
        *                 type="string",
        *                 description="last_name",
        *                 default="null",
        *                 example="string"
        *             ),
        *             @OA\Property(
        *                 property="phone",
        *                 type="integer",
        *                 description="phone",
        *                 default="null",
        *                 example="123456789101"
        *             ),
        *             @OA\Property(
        *                 property="email",
        *                 type="string",
        *                 description="email",
        *                 example="test@example.com"
        *             ),
        *             @OA\Property(
        *                 property="password",
        *                 type="string",
        *                 description="password",
        *                 example="password"
        *             ),
        *             @OA\Property(
        *                 property="password_confirmation",
        *                 type="string",
        *                 description="password_confirmation",
        *                 example="password"
        *             )
        *
        *         )
        *     )
        * )
        *
        * Get a JWT via given credentials.
        *
        * @return \Illuminate\Http\JsonResponse
        */
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|max:255',
            'last_name'  => 'required|max:255',
            'phone'  => 'required|digits:12',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|confirmed|min:6',
        ]);

        $user = User::create($request->all());
        return response()->json(['message' => 'Register successful']);
    }
    /**
     * @OA\Get(
     *     path="/auth/me",
     *     tags={"Authentication"},
     *     summary="my info",
     *     description="my info",
     *     @OA\Response(
     *         response=200,
     *         description="Success Message",
     *         @OA\JsonContent(ref="#/components/schemas/UserModel"),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="an 'unexpected' error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel"),
     *     ),security={{"api_key": {}}}
     * )
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
    * @OA\Post(
    *     path="/auth/logout",
    *     tags={"Authentication"},
    *     summary="logout",
    *     description="logout",
    *     operationId="logout",
    *     @OA\Response(
    *         response=200,
    *         description="Success Message",
    *         @OA\JsonContent(ref="#/components/schemas/SuccessModel"),
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="an 'unexpected' error",
    *         @OA\JsonContent(ref="#/components/schemas/ErrorModel"),
    *     ),security={{"api_key": {}}}
    * )
    *
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
    * @OA\Get(
    *     path="/auth/refresh",
    *     tags={"Authentication"},
    *     summary="refresh",
    *     description="refresh a token",
    *     operationId="refresh",
    *     @OA\Response(
    *         response=200,
    *         description="Success Message",
    *         @OA\JsonContent(ref="#/components/schemas/SuccessModel"),
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="an 'unexpected' error",
    *         @OA\JsonContent(ref="#/components/schemas/ErrorModel"),
    *     ),security={{"api_key": {}}}
    * )
    *
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
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
