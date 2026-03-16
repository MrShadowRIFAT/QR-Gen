<?php
header('Content-Type: application/json');
echo json_encode(
    array(
        'ok' => true,
        'service' => 'QR-Gen API',
        'runtime' => getenv('VERCEL') ? 'vercel' : 'php'
    )
);
