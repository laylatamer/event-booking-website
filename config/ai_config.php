<?php
// AI Chatbot Configuration

return [
    'provider' => 'openrouter',
    
    'api_key' => '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',
    'api_url' => '',
    
    'model' => 'meta-llama/llama-3.2-3b-instruct',
    'temperature' => 0.7,
    'max_tokens' => 500,
    
    'use_fallback' => true,
    'fallback_threshold' => 0.3,
    
    'rate_limit' => 50,
    'cache_responses' => true,
    'cache_ttl' => 3600,
    
    'max_conversation_history' => 10,
    'include_user_context' => true,
    'include_event_data' => true,
];

