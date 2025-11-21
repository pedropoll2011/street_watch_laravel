<?php

use App\Livewire\Auth\LoginFirebase;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\MarkersTable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Tela de markers protegida pela sessão do Firebase
Route::get('/markers', MarkersTable::class)
    ->middleware('firebase.auth')
    ->name('markers.index');

// Logout limpa sessão do Firebase e volta pro login
Route::get('/logout', function () {
    session()->forget(['projectId', 'idToken']);
    return redirect('/login-firebase');
})->name('logout');

// Login via Firebase Auth (Livewire)
Route::get('/login-firebase', LoginFirebase::class)->name('firebase.login');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

Route::get('/login', function () {
    abort(404);
});

// ==============================================
// ROTA DE TESTE COM FIREBASE (Realtime Database)
// ==============================================
Route::get('/firebase-teste', function () {
    $baseUrl = rtrim(env('FIREBASE_DATABASE_URL'), '/');
    $url = "{$baseUrl}/teste_laravel.json";

    $payload = [
        'mensagem'  => 'Olá do Laravel via Firebase REST! 🎯',
        'timestamp' => now()->toDateTimeString(),
    ];

    $response = Http::post($url, $payload);

    if (!$response->successful()) {
        dd('Erro ao enviar dados para o Firebase', $response->status(), $response->body());
    }

    $all   = Http::get($url);
    $dados = $all->json();

    return view('firebase-teste', [
        'dados' => $dados,
    ]);
});
