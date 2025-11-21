<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class LoginFirebase extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public string $errorMessage = '';
    public bool $loading = false;

    public function login()
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ]);

        $this->errorMessage = '';
        $this->loading = true;

        try {
            $apiKey = env('FIREBASE_API_KEY');

            if (!$apiKey) {
                $this->errorMessage = 'Chave da API do Firebase não configurada.';
                return;
            }

            $url = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key={$apiKey}";

            $response = Http::post($url, [
                'email' => $this->email,
                'password' => $this->password,
                'returnSecureToken' => true,
            ]);

            if (!$response->successful()) {
                $data = $response->json();
                $code = $data['error']['message'] ?? 'UNKNOWN';
                $this->errorMessage = $this->mapFirebaseError($code);
                return;
            }

            $data = $response->json();
            Session::put('projectId', env('FIREBASE_PROJECT_ID'));
            Session::put('idToken', $data['idToken']);

            return redirect()->route('markers.index');

        } finally {
            $this->loading = false;
        }
    }

    protected function mapFirebaseError(string $code): string
    {
        return match ($code) {
            'EMAIL_NOT_FOUND' => 'Email não encontrado.',
            'INVALID_PASSWORD' => 'Senha inválida.',
            'USER_DISABLED' => 'Usuário desativado.',
            'TOO_MANY_ATTEMPTS_TRY_LATER' => 'Muitas tentativas. Tente novamente mais tarde.',
            default => 'Falha ao entrar. Verifique as credenciais.',
        };
    }

    public function render()
    {
        return view('livewire.auth.login-firebase');
    }
}
