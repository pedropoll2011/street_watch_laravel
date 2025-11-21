<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Projeto padrão
    |--------------------------------------------------------------------------
    |
    | "app" é só um apelido interno. Não precisa ser igual ao ID do Firebase.
    |
    */
    'default' => 'app',

    /*
    |--------------------------------------------------------------------------
    | Projetos configurados
    |--------------------------------------------------------------------------
    */
    'projects' => [

        'app' => [
            'credentials' => [
                // Caminho relativo a partir da raiz do projeto Laravel
                'file' => base_path(env('FIREBASE_CREDENTIALS')),
            ],

            // Realtime Database (pra nosso teste)
            'database' => [
                'url' => env('FIREBASE_DATABASE_URL'),
            ],

            // Se quiser usar Firestore depois, a gente configura aqui
            // 'firestore' => [
            //     'database' => '(default)',
            // ],
        ],

    ],
];
