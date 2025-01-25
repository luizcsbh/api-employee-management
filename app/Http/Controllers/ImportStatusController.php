<?php

namespace App\Http\Controllers;

use App\Models\ImportStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ImportStatusController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/import-status/{id}",
     *     summary="Obter o status de uma importação específica",
     *     tags={"ImportStatus"},
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