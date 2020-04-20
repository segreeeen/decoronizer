<?php
    $config[ConfigConstants::PATH_OUTPUT] = '_locales';
    $config[ConfigConstants::PATH_SOURCE_CSV] = 'locale_master.csv';
    $config[ConfigConstants::PATH_MSG_MASTER] = 'message_master.json';
    $config[ConfigConstants::PATH_MSG_DESTINATION] = 'messages.json';

    $config[ConfigConstants::VERBOSE_MODE] = false;

    $config[ConfigConstants::LOCALES_ACTIVE] = [
        'en',
        'de',
        'tr',
        'hu',
    ];

    $config[ConfigConstants::LOCALE_CORRELATIONS] = [
        'de' => 'de',
        'en' => 'en',
        'en-GB' => 'en',
        'en-US' => 'en',
        'hu' => 'hu',
        'tr' => 'tr',
    ];

    $config[ConfigConstants::DERIVATIVE_TABLE] = [
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
    ];
