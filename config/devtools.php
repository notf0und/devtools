<?php

return [
    'lagoon' => [
        'ssh' => [
            'hostnames' => [
                env('LAGOON_HOSTNAME', 'ssh.lagoon.amazeeio.cloud')
            ]
        ],
        'graphql' => [
            'endpoint' => env('LAGOON_GRAPHQL_ENDPOINT', "https://api.lagoon.amazeeio.cloud/graphql"),
        ]
    ]
];
