<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Python Script Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Python AI bridge script path and execution settings.
    | The script_path should point to your Python entry point that handles
    | AI operations like embeddings, analysis, and chat.
    |
    */

    'script_path' => env('OVERPASS_SCRIPT_PATH', base_path('overpass-ai/main.py')),

    /*
    |--------------------------------------------------------------------------
    | Execution Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for Python process execution including timeouts and output limits.
    | Adjust these based on your AI operations complexity and server resources.
    |
    */

    'timeout' => env('OVERPASS_TIMEOUT', 90),
    'max_output_length' => env('OVERPASS_MAX_OUTPUT', 10000),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | OpenAI API credentials that will be securely passed to Python processes
    | via environment variables. These can also be set in services.php config.
    |
    */

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Control logging behavior for the Python AI bridge operations.
    | Set to false to reduce log verbosity in production environments.
    |
    */

    'logging' => [
        'enabled' => env('OVERPASS_LOGGING', true),
        'log_channel' => env('OVERPASS_LOG_CHANNEL', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Handling Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how the bridge handles errors and fallback behavior.
    | Enable fallback_enabled to provide graceful degradation when AI fails.
    |
    */

    'error_handling' => [
        'fallback_enabled' => env('OVERPASS_FALLBACK_ENABLED', true),
        'retry_attempts' => env('OVERPASS_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('OVERPASS_RETRY_DELAY', 1000), // milliseconds
    ],

];
