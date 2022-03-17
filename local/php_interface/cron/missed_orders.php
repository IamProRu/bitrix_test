<?php
if (PHP_SAPI != 'cli') {
    exit('only for cli use' . PHP_EOL);
}

if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__, 4);
}
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_WITH_ON_AFTER_EPILOG', true);
define('BX_NO_ACCELERATOR_RESET', true);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

#@set_time_limit(0);
#@ini_set('max_execution_time', 0);

try {
    $result = (new App\Cron\MissedOrders(__FILE__))->run();
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');