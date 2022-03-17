<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class iampro_restapi extends CModule
{
    private CMain $app;

    public function __construct()
    {
        $this->app = $GLOBALS['APPLICATION'];

        $arModuleVersion = [];
        include(__DIR__ . '/version.php');

        $this->MODULE_ID = str_replace('_', '.', get_class($this));
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('IAMPRO_RESTAPIMODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('IAMPRO_RESTAPIMODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('IAMPRO_RESTAPIPARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('IAMPRO_RESTAPIPARTNER_URI');
        $this->MODULE_SORT = 1;
        $this->MODULE_GROUP_RIGHTS = 'Y';
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
    }

    public function DoInstall(): bool
    {
        $version2 = '21.400.0';
        if (!CheckVersion(ModuleManager::getVersion('main'), $version2)) {
            $this->app->ThrowException(
                Loc::getMessage('IAMPRO_RESTAPIINSTALL_ERROR', ['#VERSION#' => $version2])
            );
            return false;
        }

        if ('D' != $this->app->GetGroupRight($this->MODULE_ID)) {
            $this->InstallFiles();
            $this->InstallDB();
            $this->InstallEvents();
            $this->app->IncludeAdminFile(
                Loc::getMessage('IAMPRO_RESTAPIINSTALL_TITLE'),
                __DIR__ . '/step.php'
            );
            return true; // die() inside
        }

        return false;
    }

    public function DoUninstall(): bool
    {
        if ('D' != $this->app->GetGroupRight($this->MODULE_ID)) {
            $this->UnInstallFiles();
            if (!$this->UnInstallDB()) {
                return false;
            }
            $this->UnInstallEvents();

            $this->app->IncludeAdminFile(
                Loc::getMessage('IAMPRO_RESTAPIUNINSTALL_TITLE'),
                __DIR__ . '/unstep.php'
            );
            return true; // die() inside
        }

        return false;
    }

    function InstallDB(): bool
    {
        ModuleManager::registerModule($this->MODULE_ID);
        return true;
    }

    function UnInstallDB(): bool
    {
        try {
            Option::delete($this->MODULE_ID);
        } catch (Exception $e) {
            $this->app->ThrowException(Loc::getMessage('IAMPRO_RESTAPIUNINSTALL_ERROR'));
            return false;
        }
        ModuleManager::unRegisterModule($this->MODULE_ID);
        return true;
    }

    function InstallEvents()
    {
    }

    function UnInstallEvents()
    {
    }

    function InstallFiles()
    {
    }

    function UnInstallFiles()
    {
    }
}