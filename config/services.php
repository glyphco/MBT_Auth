<?php
return [
    'facebook' => [
        'client_id'     => env('FACEBOOK_ID'),
        'client_secret' => env('FACEBOOK_SCECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT'),
    ],
    'google'   => [
        'client_id'     => env('G+_CLIENT_ID'),
        'client_secret' => env('G+_CLIENT_SECRET'),
        'redirect'      => env('G+_REDIRECT'),
    ],
    // 'twitter' => [

    //         'client_id' => env ( 'TWITTER_CLIENT_ID' ),
    //         'client_secret' => env ( 'TWITTER_CLIENT_SECRET' ),
    //         'redirect' => env ( 'TWITTER_REDIRECT' )
    // ],
    // 'github' => [

    //         'client_id' => env ( 'GITHUB_CLIENT_ID' ),
    //         'client_secret' => env ( 'GITHUB_CLIENT_SECRET' ),
    //         'redirect' => env ( 'GITHUB_REDIRECT' )
    // ]
];
