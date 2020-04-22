<?php
    use LocalizationDataBuilder\Config\ConfigConstants;

    $config[ConfigConstants::PATH_OUTPUT] = '_locales';

    $config[ConfigConstants::FILENAME_SOURCE_CSV] = 'locale_master.csv';
    $config[ConfigConstants::FILENAME_MSG_MASTER] = 'message_master.json';
    $config[ConfigConstants::FILENAME_MSG_DESTINATION] = 'messages.json';

    $config[ConfigConstants::DRY_RUN] = false;
    $config[ConfigConstants::VERBOSE_MODE] = true;

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
