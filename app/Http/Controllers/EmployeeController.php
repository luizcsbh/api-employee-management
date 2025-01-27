<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\{Auth, Cache, Log};

class EmployeeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/employees",
     *     summary="Listar funcionários da empresa do usuário autenticado",
     *     description="Retorna uma lista de funcionários filtrada por parâmetros opcionais. O usuário autenticado só pode visualizar funcionários associados à sua empresa.",
     *     operationId="getEmployees",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filtrar por nome do funcionário (busca parcial)",
     *         required=false,
     *         @OA\Schema(type="string", example="João")
     *     ),
     *     @OA\Parameter(
     *         name="position",
     *         in="query",
     *         description="Filtrar por cargo do funcionário",
     *         required=false,
     *         @OA\Schema(type="string", example="Gerente")
     *     ),
     *     @OA\Parameter(
     *         name="hired_at",
     *         in="query",
     *         description="Filtrar por data de contratação (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2023-01-01")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de funcionários retornada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Employee")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="total", type="integer", example=50, description="Total de registros"),
     *                 @OA\Property(property="per_page", type="integer", example=10, description="Quantidade de registros por página"),
     *                 @OA\Property(property="current_page", type="integer", example=1, description="Página atual"),
     *                 @OA\Property(property="last_page", type="integer", example=5, description="Última página disponível")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado (usuário não autenticado)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso proibido (usuário sem permissão)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Você não tem permissão para acessar estes dados.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno no servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao buscar os funcionários.")
     *         )
     *     )
     * )
     */
    public function index(Request $request, Employee $employee)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Construir a chave do cache baseada nos filtros e no usuário
        $cacheKey = 'employees_' . $user->company_id . '_'
            . md5(json_encode($request->all()));

        // Tempo de expiração do cache em minutos
        $cacheExpiration = 10;

        // Recuperar os dados do cache ou gerar se não existir
        $employees = Cache::remember($cacheKey, $cacheExpiration, function () use ($request, $employee, $user) {
            // Construir a consulta para buscar funcionários da empresa do usuário autenticado
            $query = $employee->where('company_id', $user->company_id);

            // Aplicar filtros, se fornecidos
            if ($request->has('name')) {
                $query->where('name', 'LIKE', '%' . $request->name . '%');
            }

            if ($request->has('position')) {
                $query->where('position', $request->position);
            }

            if ($request->has('hired_at')) {
                $query->whereDate('hired_at', $request->hired_at);
            }

            // Obter os dados paginados
            return $query->paginate(10);
        });

        // Retornar os dados paginados com metadados adicionais
        return EmployeeResource::collection($employees)
            ->additional([
                'meta' => [
                    'total' => $employees->total(),
                    'per_page' => $employees->perPage(),
                    'current_page' => $employees->currentPage(),
                    'last_page' => $employees->lastPage(),
                ]
            ], Response::HTTP_OK);
    }
    /**
     * @OA\Get(
     *     path="/api/employees/{id}",
     *     summary="Exibir detalhes de um funcionário",
     *     description="Retorna os detalhes de um funcionário específico pertencente à empresa do usuário autenticado.",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do funcionário",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do funcionário retornados com sucesso.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/EmployeeResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso não autorizado.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Acesso não autorizado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Funcionário não encontrado.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Funcionário não encontrado.")
     *         )
     *     )
     * )
     */
    public function show(Employee $employee)
    {
        $user = Auth::user();

        if ($employee->company_id !== $user->company_id) {
            Log::warning("Acesso não autorizado: Usuário ID {$user->id} tentou acessar informações do ID {$employee->id}.");
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }
        Log::info("Usuário ID {$user->id} acessou os detalhes da empregado de ID {$employee->id} com sucesso.");

        return response()->json(new EmployeeResource($employee));
    }
}