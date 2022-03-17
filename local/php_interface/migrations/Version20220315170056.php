<?php

namespace Sprint\Migration;

use Bitrix\Main\Config\Configuration;

class Version20220315170056 extends Version
{
    protected $description = "Добавление настройки роутинга";
    protected $moduleVersion = "4.0.6";

    /**
     * @return bool|void
     * @throws Exceptions\HelperException
     */
    public function up()
    {
        $configuration = Configuration::getInstance();
        $routing = $configuration->get('routing');
        if (!in_array('api_basket.php', $routing['config'])) {
            $routing['config'][] = 'api_basket.php';
        }
        $configuration->add('routing', $routing);
        $configuration->saveConfiguration();
    }

    public function down()
    {
        $configuration = Configuration::getInstance();
        $routing = $configuration->get('routing');
        if ($need = array_search('api_basket.php', $routing['config'])) {
            unset($routing['config'][$need]);
        }
        $configuration->add('routing', $routing);
        $configuration->saveConfiguration();
    }
}
