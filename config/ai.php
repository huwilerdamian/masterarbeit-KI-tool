<?php

return [
    'api_key' => $_ENV['OPENAI_API_KEY'] ?? '',
    'model' => $_ENV['OPENAI_MODEL'] ?? 'gpt-4.1-mini',
];
