<?php
/**
 * SMTP Configuration
 */

$smtp = [
    'host'     => 'localhost',
    'auth'     => false,
    'username' => 'shoutout@localhost.net',
    'password' => '123456',
    'secure'   => '',
    'port'     => 25,
    'debug'    => 0,
    'from'     => [
        'email' => 'shoutout@localhost.net',
        'name'  => 'Shout-out'
    ],
    'options'  => [
        'ssl'  => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true
        ]
    ]
];