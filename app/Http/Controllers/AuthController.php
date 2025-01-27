<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
        /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Registrar um novo usuário",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation", "role"},
     *             @OA\Property(property="name", type="string", example="ACME Corporation"),
     *             @OA\Property(property="email", type="string", example="admin@acme.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="role", type="string", example="admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário registrado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
    public function register(Request $request)
    {
        // Validação dos dados
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:admin,user', // Apenas 'admin' ou 'user' são válidos
            'company_id' => 'nullable|exists:companies,id', // ID de empresa existente
            'company_name' => 'nullable|string|max:255',   // Nome da nova empresa
        ]);

        // Variável para armazenar a empresa
        $company = null;

        if (!empty($validatedData['company_id'])) {
            // Associa a uma empresa existente
            $company = Company::find($validatedData['company_id']);
        } elseif (!empty($validatedData['company_name'])) {
            // Cria uma nova empresa
            $company = Company::create(['name' => $validatedData['company_name']]);
        }

        // Criação do usuário e associação à empresa, se necessário
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'], // Define o papel do usuário
            'company_id' => $company?->id,    // Associa à empresa criada ou existente
        ]);

        // Geração do token de autenticação
        //$token = $user->createToken('auth_token')->plainTextToken;

        // Retorno da resposta
        return response()->json([
            'message' => 'Usuário registrado com sucesso!',
            //'token' => $token,
            'user' => $user,
            'company' => $company,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Realizar login do usuário",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="admin@acme.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciais inválidas"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json(['token' => $token]);
    }
    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Realizar logout do usuário",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logout realizado com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Falha ao realizar o logout"
     *     )
     * )
     */
    public function logout()
    {
        try {
            // Invalida o token do usuário autenticado
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json(['message' => 'Logout realizado com sucesso'], Response::HTTP_OK);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Falha ao realizar o logout'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
