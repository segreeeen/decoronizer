<?php

namespace LocalizationDataBuilder\Config;

interface ConfigConstants
{
    public const PATH_OUTPUT = 'PATH_OUTPUT';
    public const PATH_SOURCE_CSV = 'PATH_SOURCE_CSV';
    public const PATH_MSG_MASTER = 'PATH_MSG_MASTER';
    public const PATH_MSG_DESTINATION = 'PATH_MSG_DESTINATION';

    public const LOCALES_ACTIVE = 'LOCALES_ACTIVE';
    public const LOCALE_CORRELATIONS = 'LOCALE_CORRELATIONS';
    public const DERIVATIVE_TABLE = 'DERIVATIVE_TABLE';

    public const DRY_RUN = 'DRY_RUN';
    public const VERBOSE_MODE = 'VERBOSE_MODE';
}