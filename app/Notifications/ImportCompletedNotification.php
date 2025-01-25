<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var int
     */
    protected int $importStatusId;

    /**
     * Cria uma nova instância de notificação.
     *
     * @param int $importStatusId ID do status da importação.
     */
    public function __construct(int $importStatusId)
    {
        $this->importStatusId = $importStatusId;
    }

    /**
     * Define os canais de entrega da notificação.
     *
     * @param mixed $notifiable O usuário que receberá a notificação.
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Cria a representação de e-mail da notificação.
     *
     * @param mixed $notifiable O usuário que receberá a notificação.
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        // Configura o link para detalhes da importação
        $detailsUrl = url('/api/import-status/' . $this->importStatusId);

        return (new MailMessage)
            ->subject('Importação Concluída com Sucesso')
            ->line('A importação de colaboradores foi concluída com sucesso.')
            ->action('Ver Detalhes', $detailsUrl)
            ->line('Obrigado por usar nosso sistema!');
    }

    /**
     * Representação da notificação para array (caso necessário para logs ou outros canais).
     *
     * @param mixed $notifiable O usuário que receberá a notificação.
     * @return array
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'import_status_id' => $this->importStatusId,
            'message' => 'A importação foi concluída com sucesso.',
        ];
    }
}