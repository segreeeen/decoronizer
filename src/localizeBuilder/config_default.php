<?php
    $config = [
        ConfigConstants::PATH_OUTPUT => '..\..\_locales',
        ConfigConstants::PATH_SOURCE_CSV => 'locale_master.csv',

        ConfigConstants::PATH_MSG_MASTER => 'message_master.json',
        ConfigConstants::PATH_MSG_DESTINATION => 'messages.json',

        ConfigConstants::LOCALES_ACTIVE => [
            'en','de','tr','hu'
        ],
        ConfigConstants::LOCALE_CORRELATIONS => [
            'de' => 'de',
            'en' => 'en',
            'en-GB' => 'en',
            'en-US' => 'en',
            'hu' => 'hu',
            'tr' => 'tr',
        ],

        ConfigConstants::DERIVATIVE_TABLE => [
            '100' => [
                '{string}',
            ],
            '200' => [
                '{string}-',
                '{string} ',
                '{string}19',
            ],
            '300' => [
                '{string}',
            ],
            '400' => [
                '{string} ',
                '{string}-',
                '-{string}-',
            ],
        ],
    ];
