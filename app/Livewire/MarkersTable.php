<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class MarkersTable extends Component
{
    public array $markers = [];
    public string $errorMessage = '';

    public array $ratings = [];

    public ?string $projectId = null;
    public ?string $idToken   = null;


    public int $page = 1;
    public int $perPage = 10;

    public bool $showFinalizeModal = false;
    public ?string $finalizeMarkerId = null;
    public string $finalizeMarkerTitle = '';
    public string $finalizeReason = '';

    public function mount()
    {
      
        $this->projectId = session('projectId') ?? (env('FIREBASE_PROJECT_ID') ?: null);
        $this->idToken   = session('idToken');

        if (!$this->projectId) {
            $this->errorMessage = 'FIREBASE_PROJECT_ID não configurado no .env.';
            return;
        }

        if (!$this->idToken) {

            redirect()->route('firebase.login')->send();
            return;
        }

        $this->loadMarkers();
    }

    protected function loadMarkers(): void
    {
        if (!$this->projectId || !$this->idToken) {
            return;
        }

        $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/markers";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->idToken,
        ])->get($url);

        if ($response->status() === 401) {
            $this->handleUnauthorized();
            return;
        }

        if (!$response->successful()) {
            $this->errorMessage = 'Erro ao buscar markers: HTTP ' . $response->status();
            return;
        }

        $json      = $response->json();
        $documents = $json['documents'] ?? [];

        $this->markers = [];
        $this->ratings = [];

        foreach ($documents as $doc) {
            $name   = $doc['name'] ?? '';
            $id     = $name ? basename($name) : null;
            $fields = $doc['fields'] ?? [];

            $this->markers[] = [
                'id'           => $id,
                'title'        => $this->getString($fields['title'] ?? null),
                'address'      => $this->getString($fields['address'] ?? null),
                'problemType'  => $this->getString($fields['problemType'] ?? null),
                'ratingAvg'    => $this->getNumber($fields['ratingAvg'] ?? null),
                'ratingCount'  => $this->getNumber($fields['ratingCount'] ?? null),
                'latitude'     => $this->getNumber($fields['latitude'] ?? null),
                'longitude'    => $this->getNumber($fields['longitude'] ?? null),
                'timestamp'    => $this->getTimestamp($fields['timestamp'] ?? null),
                'username'     => $this->getString($fields['username'] ?? null),
                'photoUrl'     => $this->getString($fields['photoUrl'] ?? null),
                'adminRating'  => $this->getNumber($fields['adminRating'] ?? null),
                'userId'       => $this->getString($fields['userId'] ?? null),
            ];

            if ($id) {
                $this->ratings[$id] = null;
            }
        }

        $this->page = 1;
    }

    public function rate(string $markerId): void
    {
        if (!$this->projectId || !$this->idToken) {
            $this->errorMessage = 'Sessão expirada ou dados de conexão ausentes. Recarregue a página.';
            return;
        }

        $rating = $this->ratings[$markerId] ?? null;

        if ($rating === null || $rating === '') {
            $this->errorMessage = 'Selecione uma nota antes de avaliar.';
            return;
        }

        $rating = (float) $rating;

        if ($rating < 1 || $rating > 5) {
            $this->errorMessage = 'A nota deve ser entre 1 e 5.';
            return;
        }

        $index = collect($this->markers)->search(fn ($m) => $m['id'] === $markerId);

        if ($index === false) {
            $this->errorMessage = 'Marker não encontrado na lista.';
            return;
        }

        $marker        = $this->markers[$index];
        $oldAvg        = (float) ($marker['ratingAvg'] ?? 0);
        $oldCount      = (int)   ($marker['ratingCount'] ?? 0);
        $oldAdminScore = $marker['adminRating'] ?? null;

        if ($oldAdminScore === null) {
            $newCount = $oldCount + 1;
            $newAvg   = $newCount > 0
                ? (($oldAvg * $oldCount) + $rating) / $newCount
                : $rating;
        } else {
            $newCount = $oldCount;
            if ($newCount > 0) {
                $newAvg = (($oldAvg * $oldCount) - $oldAdminScore + $rating) / $newCount;
            } else {
                $newCount = 1;
                $newAvg   = $rating;
            }
        }

        $newAvg = round($newAvg, 2);

        $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/markers/{$markerId}"
            . "?updateMask.fieldPaths=ratingAvg"
            . "&updateMask.fieldPaths=ratingCount"
            . "&updateMask.fieldPaths=adminRating";

        $payload = [
            'fields' => [
                'ratingAvg' => [
                    'doubleValue' => $newAvg,
                ],
                'ratingCount' => [
                    'integerValue' => $newCount,
                ],
                'adminRating' => [
                    'doubleValue' => $rating,
                ],
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->idToken,
        ])->patch($url, $payload);

        if ($response->status() === 401) {
            $this->handleUnauthorized();
            return;
        }

        if (!$response->successful()) {
            $this->errorMessage = 'Erro ao atualizar nota no Firestore: HTTP ' . $response->status();
            return;
        }

        $this->markers[$index]['ratingAvg']    = $newAvg;
        $this->markers[$index]['ratingCount']  = $newCount;
        $this->markers[$index]['adminRating']  = $rating;

        $this->errorMessage       = '';
        $this->ratings[$markerId] = null;
    }


    public function openFinalizeModal(string $markerId): void
    {
        $this->errorMessage       = '';
        $this->finalizeReason     = '';
        $this->finalizeMarkerId   = $markerId;
        $this->finalizeMarkerTitle = '';

        $marker = collect($this->markers)->firstWhere('id', $markerId);
        if ($marker) {
            $this->finalizeMarkerTitle = $marker['title'] ?? '';
        }

        $this->showFinalizeModal = true;
    }

    public function closeFinalizeModal(): void
    {
        $this->showFinalizeModal  = false;
        $this->finalizeMarkerId   = null;
        $this->finalizeMarkerTitle = '';
        $this->finalizeReason     = '';
    }

    public function submitFinalize(): void
    {
        if (!$this->projectId || !$this->idToken) {
            $this->errorMessage = 'Sessão expirada ou dados de conexão ausentes. Recarregue a página.';
            return;
        }

        $markerId = $this->finalizeMarkerId;
        $reason   = trim($this->finalizeReason);

        if (!$markerId || $reason === '') {
            $this->errorMessage = 'Informe o motivo da finalização.';
            return;
        }

        $index = collect($this->markers)->search(fn ($m) => $m['id'] === $markerId);

        if ($index === false) {
            $this->errorMessage = 'Marcador não encontrado na lista.';
            return;
        }

        $marker = $this->markers[$index];
        $userId = $marker['userId'] ?? null;
        $title  = $marker['title'] ?? 'Sem título';

        $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/markers/{$markerId}";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->idToken,
        ])->delete($url);

        if ($response->status() === 401) {
            $this->handleUnauthorized();
            return;
        }

        if (!$response->successful()) {
            $this->errorMessage = 'Erro ao excluir marcador no Firestore: HTTP ' . $response->status();
            return;
        }
        if ($userId) {
            $notificationsUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/notifications";

            $notificationPayload = [
                'fields' => [
                    'userId' => [
                        'stringValue' => $userId,
                    ],
                    'markerId' => [
                        'stringValue' => $markerId,
                    ],
                    'markerTitle' => [
                        'stringValue' => $title,
                    ],
                    'reason' => [
                        'stringValue' => $reason,
                    ],
                    'type' => [
                        'stringValue' => 'marker_removed',
                    ],
                    'read' => [
                        'booleanValue' => false,
                    ],
                    'createdAt' => [
                        'timestampValue' => now()->toAtomString(),
                    ],
                ],
            ];

            Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->idToken,
            ])->post($notificationsUrl, $notificationPayload);
        }

        $this->closeFinalizeModal();
        $this->loadMarkers();
    }

    public function goToPage(int $page): void
    {
        $total    = count($this->markers);
        $lastPage = max(1, (int) ceil($total / $this->perPage));

        $page       = max(1, min($page, $lastPage));
        $this->page = $page;
    }

    public function nextPage(): void
    {
        $this->goToPage($this->page + 1);
    }

    public function prevPage(): void
    {
        $this->goToPage($this->page - 1);
    }

    protected function getString(?array $field): ?string
    {
        if (!is_array($field)) {
            return null;
        }

        return $field['stringValue'] ?? null;
    }

    protected function getNumber(?array $field): ?float
    {
        if (!is_array($field)) {
            return null;
        }

        if (isset($field['integerValue'])) {
            return (float) $field['integerValue'];
        }

        if (isset($field['doubleValue'])) {
            return (float) $field['doubleValue'];
        }

        return null;
    }

    protected function getTimestamp(?array $field): ?string
    {
        if (!is_array($field)) {
            return null;
        }

        if (isset($field['timestampValue'])) {
            return $field['timestampValue'];
        }

        if (isset($field['stringValue'])) {
            return $field['stringValue'];
        }

        return null;
    }

    protected function handleUnauthorized(): void
    {
        Session::forget(['projectId', 'idToken']);
        $this->errorMessage = 'Sessão expirada. Faça login novamente.';
        redirect()->route('firebase.login')->send();
    }

    public function render()
    {
        $collection = collect($this->markers);
        $total      = $collection->count();
        $lastPage   = max(1, (int) ceil($total / $this->perPage));

        $page       = max(1, min($this->page, $lastPage));
        $this->page = $page;

        $offset = ($page - 1) * $this->perPage;
        $items  = $collection->slice($offset, $this->perPage)->values()->all();

        return view('livewire.markers-table', [
            'paginatedMarkers' => $items,
            'total'            => $total,
            'page'             => $page,
            'perPage'          => $this->perPage,
            'lastPage'         => $lastPage,
        ]);
    }
}
