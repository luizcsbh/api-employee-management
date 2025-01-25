<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se o upload de arquivo retorna o status correto.
     */
    public function test_upload_returns_correct_status()
    {
        // Cria um usuário autenticado
        $user = User::factory()->create();

        // Finge o sistema de arquivos
        Storage::fake('local');

        // Arquivo CSV falso
        $file = UploadedFile::fake()->create('employees.csv', 1024, 'text/csv');

        // Faz a requisição de upload autenticada
        $response = $this->actingAs($user)->postJson('/api/upload', [
            'file' => $file,
        ]);

        // Verifica o status de resposta
        $response->assertStatus(201);

        // Verifica se o arquivo foi armazenado
        Storage::disk('local')->assertExists('imports/' . $file->hashName());

        // Verifica se o registro de ImportStatus foi criado
        $this->assertDatabaseHas('import_statuses', [
            'user_id' => $user->id,
            'file_path' => 'imports/' . $file->hashName(),
            'status' => 'pending',
        ]);
    }

    /**
     * Testa se a validação de upload retorna erro quando o arquivo está ausente.
     */
    public function test_upload_fails_without_file()
    {
        // Cria um usuário autenticado
        $user = User::factory()->create();

        // Faz a requisição de upload sem arquivo
        $response = $this->actingAs($user)->postJson('/api/upload');

        // Verifica o status de resposta
        $response->assertStatus(422);

        // Verifica se a mensagem de erro é retornada
        $response->assertJsonValidationErrors('file');
    }
}