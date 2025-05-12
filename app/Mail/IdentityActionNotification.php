<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Identity;
use App\Models\IdentityActionReason;
use function App\Helpers\mapRole;
use Illuminate\Support\Facades\Log;

class IdentityActionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $identity, $action, $admin, $role, $subject, $userName;
    private $reason;

    public function __construct(Identity $identity, string $action, $reason, $admin, ?string $role = null, ?string $userName = null)
    {
        $this->identity = $identity;
        $this->action = $action;
        $this->reason = $reason;
        $this->admin = $admin;
        $this->role = $role;
        $this->userName = $userName ?? $identity->user->name;
        $this->subject = $this->getSubject();
    }

    protected function getSubject()
    {
        $key = "subjects.{$this->action}";
        if (trans()->has($key)) {
            return trans($key);
        }
        return trans('subjects.default', ['action' => $this->action]);
    }

    public function build()
    {
        $role = $this->role ?? mapRole($this->identity->type);
        $isInviter = !$this->role;
        $logoUrl = asset('assets/img/LogoLargo.png');
        $latestChange = $this->identity->change_requests()->latest('sent_at')->first();

        // Normalizamos $reason a una cadena legible
        $reasonText = '';
        if ($this->reason instanceof IdentityActionReason) {
            $reasonText = $this->reason->title ?? trans('reason_default', [], 'Motivo no especificado');
            Log::debug("Reason procesado como objeto IdentityActionReason: {$reasonText}");
        } elseif (is_string($this->reason)) {
            $reasonText = $this->reason;
            Log::debug("Reason procesado como string: {$reasonText}");
        } elseif (is_null($this->reason)) {
            $reasonText = '';
            Log::debug("Reason es null");
        } else {
            $reasonText = trans('reason_unknown', [], 'Motivo desconocido');
            Log::debug("Reason tiene tipo inesperado: " . gettype($this->reason));
        }

        // Aplicamos el locale de la sesión
        $locale = session('locale', config('app.locale', 'es'));
        $this->locale($locale);

        return $this->subject($this->subject)
            ->view('emails.identity_action')
            ->with([
                'userName' => $this->identity->user->name,
                'identityName' => $this->identity->name,
                'role' => $role,
                'action' => $this->action,
                'reason' => $reasonText,
                'adminName' => $this->admin->name,
                'adminEmail' => $this->admin->email,
                'logoUrl' => $logoUrl,
                'requestedChanges' => $latestChange ? $latestChange->message : '',
                'isInviter' => $isInviter,
                'appName' => config('app.name'),
                'year' => date('Y'),
            ]);
    }
}
