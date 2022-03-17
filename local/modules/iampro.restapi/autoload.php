<?php

use Bitrix\Main\Loader;

Bitrix\Main\Loader::registerNamespace(
    '\Iampro\Restapi',
    Loader::getDocumentRoot() . '/local/modules/iampro.restapi/lib'
);
