<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Teste Firebase - REST</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #0f172a;
            color: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: #020617;
            border-radius: 16px;
            padding: 24px 28px;
            max-width: 640px;
            width: 100%;
            box-shadow: 0 10px 25px rgba(0,0,0,0.4);
            border: 1px solid #1f2937;
        }
        .title {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }
        .subtitle {
            font-size: 0.9rem;
            color: #9ca3af;
            margin-bottom: 24px;
        }
        .item {
            border: 1px solid #1f2937;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        .item small {
            color: #9ca3af;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="title">Teste de conexão com Firebase (REST)</div>
    <div class="subtitle">
        Esta página envia um registro para o Realtime Database e mostra
        tudo o que está salvo em <code>teste_laravel</code>.
    </div>

    @if(is_array($dados))
        @foreach($dados as $id => $linha)
            <div class="item">
                <div><strong>ID:</strong> {{ $id }}</div>
                <div><strong>Mensagem:</strong> {{ $linha['mensagem'] ?? '-' }}</div>
                <div><small>{{ $linha['timestamp'] ?? '' }}</small></div>
            </div>
        @endforeach
    @else
        <div class="item">
            Nenhum dado retornado.
        </div>
    @endif
</div>
</body>
</html>
