<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    protected string $errorMessage;

    /**
     * Cria uma nova instância de notificação.
     *
     * @param string $errorMessage Mensagem de erro ocorrida na importação.
     */
    public function __construct(string $errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * Define os canais de entrega da notificação.
     *
     * @param mixed $notifiable O destinatário da notificação.
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Cria a representação de e-mail da notificação.
     *
     * @param mixed $notifiable O destinatário da notificação.
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Erro na Importação de Colaboradores')
            ->line('Ocorreu um erro durante a importação de colaboradores.')
            ->line('Erro: ' . $this->errorMessage)
            ->line('Por favor, verifique o arquivo de importação e tente novamente.')
            ->line('Se o problema persistir, entre em contato com o suporte.')
            ->line('Obrigado por usar nosso sistema!');
    }

    /**
     * Representação da notificação para array (caso necessário para logs ou outros canais).
     *
     * @param mixed $notifiable O destinatário da notificação.
     * @return array
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'error_message' => $this->errorMessage,
        ];
    }
}