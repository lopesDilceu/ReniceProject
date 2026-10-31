<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI review summaries
    |--------------------------------------------------------------------------
    |
    | A short summary with pros and cons of each product's reviews, generated
    | through laravel/ai (Anthropic's Claude recommended). Leave the provider
    | empty to turn summaries off: product pages then show the reviews only.
    |
    */

    'summaries' => [
        'provider' => env('REVIEW_SUMMARY_PROVIDER'),
        // Null uses the provider's cheapest capable default.
        'model' => env('REVIEW_SUMMARY_MODEL'),
        // Below this, a summary would just restate one or two reviews.
        'min_reviews' => 3,
        // Reviews arriving within this window produce a single summary.
        'debounce_seconds' => 300,
        // Only the most recent reviews are sent, which bounds cost per call.
        'max_reviews' => 100,
        'timeout' => 30,
    ],

];
