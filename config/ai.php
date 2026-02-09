<?php

return [
    'api_key' => $_ENV['OPENAI_API_KEY'] ?? '',
    'model' => $_ENV['OPENAI_MODEL'] ?? 'gpt-4.1-mini',
    'max_history' => (int)($_ENV['OPENAI_MAX_HISTORY'] ?? 20),
    'vector_store_id' => $_ENV['OPENAI_VECTOR_STORE_ID'] ?? '',
    'temperature' => isset($_ENV['OPENAI_TEMPERATURE']) ? (float)$_ENV['OPENAI_TEMPERATURE'] : null,
];
