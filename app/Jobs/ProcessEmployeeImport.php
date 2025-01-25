<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Models\ImportStatus;
use App\Notifications\ImportCompletedNotification;
use App\Notifications\ImportFailedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use Throwable;

class ProcessEmployeeImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $importStatus;
    protected $companyId;

    /**
     * Cria uma nova instância do Job.
     *
     * @param  \App\Models\ImportStatus  $importStatus
     * @param  int  $companyId
     */
    public function __construct(ImportStatus $importStatus, int $companyId)
    {
        $this->importStatus = $importStatus;
        $this->companyId = $companyId;
    }

    /**
     * Executa o Job.
     */
    public function handle()
    {
        $filePath = $this->importStatus->file_path;

        try {
            $csv = Reader::createFromPath(Storage::path($filePath), 'r');
            $csv->setHeaderOffset(0); // Define o cabeçalho do CSV

            foreach ($csv as $row) {
                Log::info('Processando linha do CSV', ['row' => $row]);

                try {
                    $validatedData = $this->validateRow($row);

                    if (Employee::where('cpf', $validatedData['cpf'])->exists()) {
                        throw new \Exception("CPF duplicado encontrado: {$validatedData['cpf']}");
                    }

                    if (Employee::where('email', $validatedData['email'])->exists()) {
                        throw new \Exception("E-mail duplicado encontrado: {$validatedData['email']}");
                    }

                    Employee::updateOrCreate(
                        ['email' => $validatedData['email']],
                        [
                            'company_id' => $this->companyId,
                            'name' => $validatedData['name'],
                            'cpf' => $validatedData['cpf'],
                            'position' => $validatedData['position'],
                            'hired_at' => $validatedData['hired_at'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );

                } catch (\Exception $e) {
                    Log::error('Erro ao processar linha do CSV', [
                        'error' => $e->getMessage(),
                        'row' => $row,
                    ]);
                    continue;
                }
            }

            $this->importStatus->update(['status' => 'completed']);

            // Enviar notificação de conclusão com captura de erro
            try {
                $this->importStatus->user->notify(new ImportCompletedNotification($this->importStatus->id));
            } catch (\Exception $e) {
                Log::error('Erro ao enviar notificação de conclusão.', [
                    'status_id' => $this->importStatus->id,
                    'error' => $e->getMessage(),
                ]);
            }

        } catch (Throwable $e) {
            $this->importStatus->update(['status' => 'failed']);

            // Enviar notificação de falha com captura de erro
            try {
                $this->importStatus->user->notify(new ImportFailedNotification($e->getMessage()));
            } catch (\Exception $ex) {
                Log::error('Erro ao enviar notificação de falha.', [
                    'status_id' => $this->importStatus->id,
                    'error' => $ex->getMessage(),
                ]);
            }

            Log::error('Erro ao processar a importação.', [
                'status_id' => $this->importStatus->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Valida os dados de uma linha do CSV.
     *
     * @param  array  $row
     * @return array
     * @throws \Exception
     */
    private function validateRow(array $row)
    {
        if (empty($row['name']) || empty($row['cpf']) || empty($row['email']) || empty($row['position']) || empty($row['hired_at'])) {
            throw new \Exception('Dados inválidos no CSV: ' . json_encode($row));
        }

        return [
            'name' => $row['name'],
            'cpf' => preg_replace('/\D/', '', $row['cpf']),
            'email' => $row['email'],
            'position' => $row['position'],
            'hired_at' => $row['hired_at'],
        ];
    }
}