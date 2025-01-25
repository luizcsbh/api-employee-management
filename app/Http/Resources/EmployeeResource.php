<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
/**
 * @OA\Schema(
 *     schema="EmployeeResource",
 *     type="object",
 *     title="Employee Resource",
 *     description="Representação de um funcionário.",
 *     @OA\Property(property="id", type="integer", example=1, description="ID do funcionário"),
 *     @OA\Property(property="name", type="string", example="João da Silva", description="Nome do funcionário"),
 *     @OA\Property(property="position", type="string", example="Desenvolvedor", description="Cargo do funcionário"),
 *     @OA\Property(property="email", type="string", example="joao.silva@example.com", description="E-mail do funcionário"),
 *     @OA\Property(property="hired_at", type="string", format="date", example="2023-01-15", description="Data de contratação do funcionário"),
 *     @OA\Property(property="company_id", type="integer", example=1, description="ID da empresa do funcionário"),
 * )
 */

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name' => $this->name,
            'position' => $this->position,
            'hired_at' => $this->hired_at,
        ];
    }
}
