<?php

// http://localhost/chrome/decoronizer/src/localizeBuilder/index.php

require_once('Config\ConfigConstants.php');
require_once('Config\Config.php');

require_once('Shared\ReplacementDataTransfer.php');

require_once('Business\Application.php');
require_once('Business\DataWriterInterface.php');
require_once('Business\DataWriter.php');
require_once('Business\JsonHelperInterface.php');
require_once('Business\JsonHelper.php');
require_once('Business\LocalizationDataBuilderBusinessFactory.php');
require_once('Business\LocaleConstants.php');
require_once('Business\LocaleMasterProcessorInterface.php');
require_once('Business\LocaleMasterProcessor.php');
require_once('Business\MessageMasterProcessorInterface.php');
require_once('Business\MessageMasterProcessor.php');
require_once('Business\ReplacementDataProcessorInterface.php');
require_once('Business\ReplacementDataProcessor.php');

require_once('Communication\LocalizationDataBuilderCommunicationFactory.php');
require_once('Communication\PageRenderer.php');

require_once('Persistence\FileHandlerInterface.php');
require_once('Persistence\FileHandler.php');

use LocalizationDataBuilder\Business\Application;

$app = new Application();
$app->run();
