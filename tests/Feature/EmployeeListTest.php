<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeListTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_list_requires_authentication()
    {
        // Faz uma requisição sem autenticação
        $response = $this->getJson('/api/employees');

        // Verifica se a resposta é 401 (não autorizado)
        $response->assertStatus(401);
    }

    public function test_employee_list_returns_correct_data()
    {
        // Cria uma empresa e um usuário
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        // Cria 5 funcionários associados à empresa
        Employee::factory()->count(5)->create(['company_id' => $company->id]);

        // Faz a requisição autenticada
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/employees');

        // Verifica se o status da resposta é 200
        $response->assertStatus(200);

        // Verifica a estrutura dos dados retornados
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'cpf',
                    'email',
                    'position',
                    'hired_at',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta' => [
                'total',
                'per_page',
                'current_page',
                'last_page',
            ],
        ]);

        // Verifica se o total de funcionários retornados está correto
        $this->assertCount(5, $response->json('data'));
    }

    public function test_employee_list_applies_filters_correctly()
    {
        // Cria uma empresa e um usuário
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        // Cria funcionários associados à empresa
        Employee::factory()->create([
            'company_id' => $company->id,
            'name' => 'João Silva',
            'position' => 'Developer',
            'hired_at' => '2023-01-01',
        ]);

        Employee::factory()->create([
            'company_id' => $company->id,
            'name' => 'Maria Oliveira',
            'position' => 'Designer',
            'hired_at' => '2023-06-01',
        ]);

        // Faz uma requisição com o filtro "name"
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/employees?name=João');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));

        // Faz uma requisição com o filtro "position"
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/employees?position=Designer');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));

        // Faz uma requisição com o filtro "hired_at"
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/employees?hired_at=2023-06-01');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_employee_list_handles_pagination_correctly()
    {
        // Cria uma empresa e um usuário
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        // Cria 15 funcionários associados à empresa
        Employee::factory()->count(15)->create(['company_id' => $company->id]);

        // Faz a requisição autenticada
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/employees?page=1');

        // Verifica se a resposta contém os primeiros 10 funcionários
        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));

        // Verifica a segunda página
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/employees?page=2');
        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }
}