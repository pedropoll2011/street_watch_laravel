<div class="p-6 lg:p-10">
    <div class="flex justify-end mb-4">
    <a href="{{ route('logout') }}"
       class="px-3 py-1.5 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-medium">
        Sair
    </a>
    </div>

    <style>
        .marker-rating-select,
        .marker-rating-select option {
            color: #111827;
            background-color: #ffffff;
        }

        html.dark .marker-rating-select,
        html.dark .marker-rating-select option {
            color: #e5e7eb;
            background-color: #020617;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 50;
            background: rgba(0, 0, 0, 0.75);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-panel {
            width: 100%;
            max-width: 32rem;
            background-color: #18181b;
            border-radius: 0.75rem;
            border: 1px solid #3f3f46;
            color: #e4e4e7;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.8);
        }

        .modal-panel-header,
        .modal-panel-footer {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #3f3f46;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-panel-footer {
            border-top: 1px solid #3f3f46;
            border-bottom: none;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        .modal-panel-body {
            padding: 1rem;
        }

        .photo-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 40;
            background: rgba(0,0,0,0.85);
            display: none;
            align-items: center;
            justify-content: center;
        }

        .photo-modal-overlay.active {
            display: flex;
        }

        .photo-modal-img {
            max-width: 90vw;
            max-height: 90vh;
            border-radius: 0.75rem;
            border: 1px solid #3f3f46;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.9);
        }

        .photo-modal-close {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            border: none;
            background: rgba(24,24,27,0.85);
            color: #e4e4e7;
            border-radius: 9999px;
            width: 2rem;
            height: 2rem;
            font-size: 1.1rem;
            cursor: pointer;
        }

        .photo-modal-close:hover {
            background: rgba(39,39,42,0.95);
        }
    </style>

    <h1 class="text-2xl font-semibold text-zinc-100 mb-4">
        Avaliação de Reportes
    </h1>

    @if ($errorMessage && !$showFinalizeModal)
        <div class="mb-4 rounded-md bg-red-900/40 border border-red-500 text-red-100 px-4 py-3 text-sm">
            {{ $errorMessage }}
        </div>
    @endif

    @if ($total === 0)
        <p class="text-sm text-zinc-400">
            Nenhum reporte encontrado.
        </p>
    @else
        <div class="overflow-x-auto rounded-lg border border-zinc-700">

            <table class="w-full table-fixed text-sm text-left text-zinc-200">
                <thead class="bg-zinc-900/70 text-xs uppercase text-zinc-400">
                    <tr>
                        <th class="px-4 py-3">Título</th>
                        <th class="px-4 py-3">Endereço</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3 text-center">Média</th>
                        <th class="px-4 py-3 text-center">Votos</th>
                        <th class="px-4 py-3 text-center">Foto</th>
                        <th class="px-4 py-3 text-center">Avaliar</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-800 bg-zinc-900/40">
                    @foreach ($paginatedMarkers as $marker)
                        <tr>
                      
                            <td class="px-4 py-3 align-top">
                                <div class="font-medium text-zinc-100 truncate">
                                    {{ $marker['title'] ?? '-' }}
                                </div>

                                <div class="text-xs text-zinc-400">
                                    por {{ $marker['username'] ?? 'Usuário desconhecido' }}
                                </div>

                                @if (!empty($marker['adminRating']))
                                    <div class="mt-1 text-[11px] text-emerald-400">
                                        Avaliado pelo painel ({{ $marker['adminRating'] }}★)
                                    </div>
                                @endif
                            </td>

                       
                            <td class="px-4 py-3 align-top text-xs text-zinc-300">
                                <div class="truncate">
                                    {{ $marker['address'] ?? '-' }}
                                </div>

                                @if (!empty($marker['timestamp']))
                                    <div class="mt-1 text-[11px] text-zinc-500">
                                        {{ $marker['timestamp'] }}
                                    </div>
                                @endif
                            </td>

      
                            <td class="px-4 py-3 align-top text-xs">
                                <span class="inline-flex rounded-full bg-zinc-800 px-2.5 py-0.5 text-[11px] text-zinc-200">
                                    {{ $marker['problemType'] ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center align-top">
                                <div class="text-sm font-semibold">
                                    {{ $marker['ratingAvg'] ?? 0 }} ★
                                </div>
                            </td>

                        
                            <td class="px-4 py-3 text-center align-top text-xs text-zinc-300">
                                {{ $marker['ratingCount'] ?? 0 }}
                            </td>

                          
                            <td class="px-4 py-3 text-center align-top">
                                <div class="mx-auto w-12 h-12 flex items-center justify-center">
                                    @if (!empty($marker['photoUrl']))
                                        <button
                                            type="button"
                                            class="block w-10 h-10 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                            onclick="openPhotoModal('{{ $marker['photoUrl'] }}')"
                                        >
                                            <img
                                                src="{{ $marker['photoUrl'] }}"
                                                alt="Foto do reporte"
                                                class="w-10 h-10 object-cover rounded-md border border-zinc-700"
                                            />
                                        </button>
                                    @else
                                        <span class="text-[10px] text-zinc-500 leading-tight">
                                            Sem foto
                                        </span>
                                    @endif
                                </div>
                            </td>

                          
                            <td class="px-4 py-3 text-center align-top">
                                <div class="flex flex-col items-center gap-1">
                                    <select
                                        class="marker-rating-select border border-zinc-700 text-xs rounded-md px-2 py-1"
                                        wire:model="ratings.{{ $marker['id'] }}"
                                    >
                                        <option value="">Selecione</option>
                                        <option value="1">1 ★</option>
                                        <option value="2">2 ★★</option>
                                        <option value="3">3 ★★★</option>
                                        <option value="4">4 ★★★★</option>
                                        <option value="5">5 ★★★★★</option>
                                    </select>

                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-500 disabled:opacity-50"
                                        wire:click="rate('{{ $marker['id'] }}')"
                                    >
                                        Avaliar
                                    </button>

                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-500"
                                        wire:click="openFinalizeModal('{{ $marker['id'] }}')"
                                    >
                                        Finalizar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-zinc-300">
            @php
                $from = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
                $to   = min($page * $perPage, $total);
            @endphp

            <div>
                Mostrando <span class="font-semibold">{{ $from }}</span>
                – <span class="font-semibold">{{ $to }}</span>
                de <span class="font-semibold">{{ $total }}</span> reportes
            </div>

            <div class="flex items-center gap-1">
                <button
                    type="button"
                    class="px-2 py-1 rounded-md border border-zinc-700 hover:bg-zinc-800 disabled:opacity-40"
                    wire:click="prevPage"
                    @disabled($page <= 1)
                >
                    ‹ Anterior
                </button>

                @for ($p = 1; $p <= $lastPage; $p++)
                    <button
                        type="button"
                        class="px-2 py-1 rounded-md border
                        {{ $p === $page ? 'border-emerald-500 bg-emerald-600 text-white' : 'border-zinc-700 hover:bg-zinc-800' }}"
                        wire:click="goToPage({{ $p }})"
                    >
                        {{ $p }}
                    </button>
                @endfor

                <button
                    type="button"
                    class="px-2 py-1 rounded-md border border-zinc-700 hover:bg-zinc-800 disabled:opacity-40"
                    wire:click="nextPage"
                    @disabled($page >= $lastPage)
                >
                    Próxima ›
                </button>
            </div>
        </div>
    @endif

    @if($showFinalizeModal)
        <div class="modal-overlay" wire:click="closeFinalizeModal">
            <div class="modal-panel" wire:click.stop>
                <div class="modal-panel-header">
                    <h2 class="text-sm font-medium">
                        Finalizar marcador
                    </h2>
                    <button
                        type="button"
                        class="text-zinc-400 hover:text-zinc-200 text-xl leading-none"
                        wire:click="closeFinalizeModal"
                    >
                        ×
                    </button>
                </div>

                <div class="modal-panel-body space-y-3">
                    @if ($errorMessage)
                        <div class="rounded-md bg-red-900/40 border border-red-600 text-red-200 px-3 py-2 text-xs">
                            {{ $errorMessage }}
                        </div>
                    @endif

                    <p class="text-sm text-zinc-200">
                        Você está prestes a finalizar o marcador:
                        <strong class="font-semibold">{{ $finalizeMarkerTitle }}</strong>
                    </p>

                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-zinc-300">
                            Motivo da finalização
                        </label>
                        <textarea
                            class="w-full rounded-md border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder:text-zinc-500 focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500"
                            rows="4"
                            placeholder="Descreva o motivo..."
                            wire:model.defer="finalizeReason"
                        ></textarea>
                    </div>
                </div>

                <div class="modal-panel-footer">
                    <button
                        type="button"
                        class="rounded-md border border-zinc-600 px-3 py-1.5 text-xs font-medium text-zinc-200 bg-transparent hover:bg-zinc-800"
                        wire:click="closeFinalizeModal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-500"
                        wire:click="submitFinalize"
                    >
                        Confirmar finalização
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div id="photoModal" class="photo-modal-overlay" wire:ignore>
        <div class="relative">
            <img id="photoModalImg" src="" alt="Foto ampliada do reporte" class="photo-modal-img">
            <button class="photo-modal-close" type="button" onclick="closePhotoModal()">×</button>
        </div>
    </div>

    <script>
        function openPhotoModal(url) {
            if (!url) return;
            const overlay = document.getElementById('photoModal');
            const img = document.getElementById('photoModalImg');
            img.src = url;
            overlay.classList.add('active');
        }

        function closePhotoModal() {
            const overlay = document.getElementById('photoModal');
            const img = document.getElementById('photoModalImg');
            overlay.classList.remove('active');
            img.src = '';
        }
    </script>
</div>
