<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessEmployeeImport;
use App\Models\ImportStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Bus, Log };
use Symfony\Component\HttpFoundation\Response;

class ImportController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/upload",
     *     summary="Importar colaboradores via arquivo CSV",
     *     description="Permite o upload de um arquivo CSV para importar colaboradores para a empresa do usuário autenticado.",
     *     tags={"Upload"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Arquivo CSV contendo os colaboradores a serem importados.",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="O arquivo CSV para importação."
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Arquivo enviado e processamento iniciado.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Arquivo enviado e processamento iniciado."),
     *             @OA\Property(property="status_id", type="integer", example=1, description="ID do status da importação.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro de validação no envio do arquivo.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Arquivo inválido ou não enviado.")
     *         )
     *     )
     * )
     */
    public function importCSV(Request $request)
    {
        $user = Auth::user();
    
        // Verifica se o usuário está autenticado
        if (!$user) {
            return response()->json(['message' => 'Usuário não autenticado.'], 401);
        }
    
        // Verifica se o usuário está associado a uma empresa
        if (is_null($user->company_id)) {
            return response()->json(['message' => 'Usuário não está associado a nenhuma empresa.'], 403);
        }
    
        // Valida o arquivo enviado
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);
    
        try {
            // Armazena o arquivo e gera o caminho
            $file = $request->file('file');
            $path = $file->store('imports');
    
            // Cria o registro de status da importação
            $importStatus = ImportStatus::create([
                'user_id' => $user->id,
                'status' => 'in_progress',
                'file_path' => $path,
            ]);
    
            // Dispara o job de processamento da importação
            Bus::dispatch(new ProcessEmployeeImport($importStatus, $user->company_id));
    
            // Retorna a resposta de sucesso
            return response()->json([
                'message' => 'Arquivo enviado e processamento iniciado.',
                'status_id' => $importStatus->id,
            ], Response::HTTP_CREATED);
    
        } catch (\Exception $e) {
            // Log do erro detalhado
            Log::error('Erro durante a importação do CSV', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
    
            // Retorna a resposta de erro
            return response()->json([
                'message' => 'Erro ao processar a importação.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * @OA\Get(
     *     path="/api/import-status/{id}",
     *     summary="Obter o status de uma importação específica",
     *     tags={"Upload"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do status de importação",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do status de importação",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="completed"),
     *             @OA\Property(property="file_path", type="string", example="storage/imports/file.csv")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Status de importação não encontrado ou acesso não autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Status de importação não encontrado ou você não tem permissão para acessá-lo.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno ao buscar o status de importação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao buscar status de importação."),
     *             @OA\Property(property="error", type="string", example="Detalhes do erro.")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            // Buscar o status de importação pelo ID e garantir que pertence ao usuário autenticado
            $importStatus = ImportStatus::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$importStatus) {
                return response()->json([
                    'message' => 'Status de importação não encontrado ou você não tem permissão para acessá-lo.',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => $importStatus->status,
                'file_path' => $importStatus->file_path,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar status de importação.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}