<?php

return [
    'replacement_char' => env('PROFANITY_REPLACEMENT_CHAR', '*'),

    // Simple configurable list for BTS scope.
    'words' => array_filter(array_map('trim', explode(',', (string) env(
        'PROFANITY_WORDS',
        'shit,fuck,bitch,merde,con,pute'
    )))),
];
