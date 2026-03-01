<?php

return [
'credentials' => [
        'file' => env('FIREBASE_CREDENTIALS')
            ? base_path(env('FIREBASE_CREDENTIALS'))
            : null,
    ],
];