<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\{StoreCompanyRequest, UpdateCompanyRequest};
use App\Http\Resources\CompanyResource;

class CompanyController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/companies",
     *     summary="Lista todas as empresas associadas ao usuário autenticado",
     *     tags={"Companies"},
     *     security={{"bearerAuth":{}}}, 
     *     @OA\Response(
     *         response=200,
     *         description="Lista de empresas retornada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/CompanyResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido ou não fornecido."
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="O usuário não está associado a nenhuma empresa."
     *     )
     * )
     */
    public function index()
    {
        // Apenas o usuário autenticado poderá ver as empresas associadas a ele
        $user = Auth::user();

        // Verificar se o usuário está associado a uma empresa
        if (!$user->company_id) {
            return response()->json(['message' => 'O usuário não está associado a nenhuma empresa.'], Response::HTTP_FORBIDDEN);
        }

        // Retornar os dados da empresa associada
        $company = Company::find($user->company_id);

        return response()->json(new CompanyResource($company));
    }
    /**
     * @OA\Post(
     *     path="/api/companies",
     *     summary="Criar uma nova empresa e associar um usuário admin",
     *     tags={"Companies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "address", "cnpj", "opening_date", "email"},
     *             @OA\Property(property="name", type="string", example="Empresa ABC"),
     *             @OA\Property(property="address", type="string", example="Rua Exemplo, 123, São Paulo, SP"),
     *             @OA\Property(property="cnpj", type="string", example="12345678000190"),
     *             @OA\Property(property="opening_date", type="string", format="date", example="2023-01-01"),
     *             @OA\Property(property="email", type="string", format="email", example="contato@empresaabc.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Empresa criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Empresa criada com sucesso."),
     *             @OA\Property(property="company", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Empresa ABC"),
     *                 @OA\Property(property="address", type="string", example="Rua Exemplo, 123, São Paulo, SP"),
     *                 @OA\Property(property="cnpj", type="string", example="12345678000190"),
     *                 @OA\Property(property="opening_date", type="string", format="date", example="2023-01-01"),
     *                 @OA\Property(property="email", type="string", format="email", example="contato@empresaabc.com"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2023-01-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2023-01-01T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Erro ao associar o usuário autenticado como administrador",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao associar o usuário ID X à empresa ID Y."),
     *             @OA\Property(property="error", type="string", example="Erro ao associar o usuário à empresa.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao criar a empresa",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao criar a empresa."),
     *             @OA\Property(property="error", type="string", example="Detalhes do erro.")
     *         )
     *     )
     * )
     */
    public function store(StoreCompanyRequest $request)
    {
        try {
            // Validar os dados fornecidos
            $validateData = $request->validated();
            // Verificar se o usuário autenticado já está associado a uma empresa
            $user = Auth::user();
            if ($user->company_id) {
                return response()->json([
                    'message' => 'O usuário já está associado a uma empresa e não pode criar outra.'
                ], Response::HTTP_FORBIDDEN);
            }

            // Criar a empresa
            $company = Company::create([
                'name'          => $validateData['name'],
                'address'       => $validateData['address'],
                'cnpj'          => $validateData['cnpj'],
                'opening_date'  => $validateData['opening_date'],
                'email'         => $validateData['email'],
            ]);

            // Associar o usuário autenticado como administrador da nova empresa
            $user->company_id = $company->id;
            if (!$user->save()) {
                Log::error("Erro ao associar o usuário ID {$user->id} à empresa ID {$company->id}.");
                return response()->json([
                    'message' => 'Erro ao associar o usuário à empresa.'
                ], Response::HTTP_CONFLICT);
            }

            Log::info("Empresa criada com sucesso: ID {$company->id}, nome: {$company->name}. Usuário ID {$user->id} associado como administrador.");
            return response()->json([
                'message' => 'Empresa criada com sucesso.',
                'company' => $company
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            Log::error("Erro ao criar empresa: {$e->getMessage()}");
            return response()->json([
                'message' => 'Erro ao criar a empresa.',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/companies/{id}",
     *     summary="Obter detalhes de uma empresa específica",
     *     tags={"Companies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da empresa a ser buscada",
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/EmployeeResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes da empresa retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="company", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Empresa ABC"),
     *                 @OA\Property(property="address", type="string", example="Rua Exemplo, 123, São Paulo, SP"),
     *                 @OA\Property(property="cnpj", type="string", example="12345678000190"),
     *                 @OA\Property(property="opening_date", type="string", format="date", example="2023-01-01"),
     *                 @OA\Property(property="email", type="string", format="email", example="contato@empresaabc.com"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2023-01-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2023-01-01T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Acesso não autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Acesso não autorizado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Empresa não encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Empresa não encontrada.")
     *         )
     *     )
     * )
     */
    public function show(Company $company)
    {
        $user = Auth::user();

        // Verificar se o usuário tem permissão para ver a empresa
        if ($user->company_id !== $company->id) {
            Log::warning("Acesso não autorizado: Usuário ID {$user->id} tentou acessar a empresa ID {$company->id}.");
            return response()->json(['message' => 'Acesso não autorizado.'], Response::HTTP_UNAUTHORIZED);
        }

        // Registrar o acesso bem-sucedido no log
        Log::info("Usuário ID {$user->id} acessou os detalhes da empresa ID {$company->id} com sucesso.");

        return response()->json(new CompanyResource($company));
    }

    /**
     * @OA\Put(
     *     path="/api/companies/{id}",
     *     summary="Atualizar os dados de uma empresa",
     *     description="Atualiza as informações de uma empresa. Requer autenticação com token e apenas usuários associados à empresa podem realizar a atualização.",
     *     tags={"Companies"},
     *     security={{"bearerAuth":{}}},
     * 
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da empresa a ser atualizada",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados da empresa a serem atualizados",
     *         @OA\JsonContent(
     *             required={"name", "address", "cnpj", "opening_date", "email"},
     *             @OA\Property(property="name", type="string", example="Empresa ABC"),
     *             @OA\Property(property="address", type="string", example="Rua Exemplo, 123, São Paulo, SP"),
     *             @OA\Property(property="cnpj", type="string", example="12.345.678/0001-90"),
     *             @OA\Property(property="opening_date", type="string", format="date", example="2023-01-01"),
     *             @OA\Property(property="email", type="string", format="email", example="contato@empresa.com")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Empresa atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Empresa atualizada com sucesso."),
     *             @OA\Property(property="company", ref="#/components/schemas/Company")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso não autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Acesso não autorizado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=304,
     *         description="Nenhuma alteração foi feita",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Nenhuma alteração foi feita.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao atualizar a empresa",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao atualizar a empresa."),
     *             @OA\Property(property="error", type="string", example="Detalhes do erro interno.")
     *         )
     *     )
     * )
     */
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $user = Auth::user();

        // Verificar se o usuário tem permissão para atualizar a empresa
        if ($user->company_id !== $company->id) {
            Log::warning("Tentativa não autorizada de atualização da empresa ID {$company->id} por usuário ID {$user->id}.");
            return response()->json(['message' => 'Acesso não autorizado.'], Response::HTTP_FORBIDDEN);
        }

        try {
            // Validar os dados enviados
            $validatedData = $request->validated();

            // Verificar se os dados não mudaram
            if ($company->isClean($validatedData)) {
                return response()->json(['message' => 'Nenhuma alteração foi feita.'], Response::HTTP_NOT_MODIFIED);
            }

            // Atualizar os dados da empresa
            $company->update($validatedData);

            Log::info("Empresa ID {$company->id} atualizada com sucesso pelo usuário ID {$user->id}.");
            return response()->json(['message' => 'Empresa atualizada com sucesso.', 'company' => $company], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error("Erro ao atualizar empresa ID {$company->id}: {$e->getMessage()}");
            return response()->json(['message' => 'Erro ao atualizar a empresa.', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * @OA\Delete(
     *     path="/api/companies/{id}",
     *     summary="Excluir uma empresa",
     *     description="Exclui uma empresa e desassocia o usuário autenticado. Apenas o administrador da empresa pode executar esta ação.",
     *     tags={"Companies"},
     *     security={{"bearerAuth":{}}},
     * 
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da empresa a ser excluída",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Empresa excluída com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Empresa excluída com sucesso.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Usuário não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuário não autenticado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso não autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Acesso não autorizado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao excluir a empresa",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao excluir a empresa."),
     *             @OA\Property(property="error", type="string", example="Detalhes do erro interno.")
     *         )
     *     )
     * )
     */
    public function destroy(Company $company)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Usuário não autenticado.'], Response::HTTP_UNAUTHORIZED);
        }

        if ($user->company_id !== $company->id) {
            Log::warning("Tentativa não autorizada de exclusão da empresa ID {$company->id} por usuário ID {$user->id}.");
            return response()->json(['message' => 'Acesso não autorizado.'], Response::HTTP_FORBIDDEN);
        }

        try {
            $company->delete();

            $user->company_id = null;
            $user->save();

            Log::info("Empresa ID {$company->id} excluída com sucesso pelo usuário ID {$user->id}.");
            return response()->json(['message' => 'Empresa excluída com sucesso.'], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error("Erro ao excluir empresa ID {$company->id}: {$e->getMessage()}");
            return response()->json(['message' => 'Erro ao excluir a empresa.', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
