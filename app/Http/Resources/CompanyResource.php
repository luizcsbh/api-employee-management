<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="CompanyResource",
     *     type="object",
     *     title="Company Resource",
     *     description="Estrutura de resposta para informações de uma empresa.",
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID único da empresa.",
     *         example=1
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Nome da empresa.",
     *         example="Empresa Exemplo LTDA"
     *     ),
     *     @OA\Property(
     *         property="address",
     *         type="string",
     *         description="Endereço da empresa.",
     *         example="Rua Exemplo, 123, São Paulo, SP"
     *     ),
     *     @OA\Property(
     *         property="cnpj",
     *         type="string",
     *         description="CNPJ da empresa.",
     *         example="12345678000195"
     *     ),
     *     @OA\Property(
     *         property="opening_date",
     *         type="string",
     *         format="date",
     *         description="Data de abertura da empresa.",
     *         example="2023-01-01"
     *     ),
     *     @OA\Property(
     *         property="email",
     *         type="string",
     *         format="email",
     *         description="E-mail de contato da empresa.",
     *         example="contato@empresaexemplo.com"
     *     )
     * )
     */

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'cnpj' => $this->cnpj,
            'opening_date' => $this->opening_date,
            'email' => $this->email,
        ];
    }
}
