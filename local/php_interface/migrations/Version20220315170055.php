<?php

namespace Sprint\Migration;

class Version20220315170055 extends Version
{
    protected $description = "Событие и шаблон для списка забытых заказов";
    protected $moduleVersion = "4.0.6";

    /**
     * @return bool|void
     * @throws Exceptions\HelperException
     */
    public function up()
    {
        $helper = $this->getHelperManager();
        $helper->Event()->saveEventType('MISSED_ORDERS', [
            'LID' => 'ru',
            'EVENT_TYPE' => 'email',
            'NAME' => 'Забытые заказы',
            'DESCRIPTION' => '#ORDERS# - Список забытых заказов',
            'SORT' => '150',
        ]);
        $helper->Event()->saveEventType('MISSED_ORDERS', [
            'LID' => 'en',
            'EVENT_TYPE' => 'email',
            'NAME' => 'Missed orders',
            'DESCRIPTION' => '#ORDERS# - Missed orders list',
            'SORT' => '150',
        ]);
        $helper->Event()->saveEventMessage('MISSED_ORDERS', [
            'LID' => [\CSite::GetDefSite()],
            'ACTIVE' => 'Y',
            'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
            'EMAIL_TO' => '#DEFAULT_EMAIL_FROM#',
            'SUBJECT' => 'Список забытых заказов',
            'MESSAGE' => 'Заказы, которые висят в начальном статусе 2 дня:' . "\n" . '#ORDERS#',
            'BODY_TYPE' => 'text',
            'BCC' => '',
            'REPLY_TO' => '',
            'CC' => '',
            'IN_REPLY_TO' => '',
            'PRIORITY' => '',
            'FIELD1_NAME' => '',
            'FIELD1_VALUE' => '',
            'FIELD2_NAME' => '',
            'FIELD2_VALUE' => '',
            'SITE_TEMPLATE_ID' => '',
            'ADDITIONAL_FIELD' => [],
            'LANGUAGE_ID' => '',
        ]);
    }

    public function down()
    {
    }
}
