<?php

namespace App\Cron;

use Bitrix\Main\Config\Option;
use Bitrix\Main\{Loader, Result, Error};
use Bitrix\Main\Mail\Event;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\{Order, OrderStatus};

/**
 * Class MissedOrders
 * @package App\Cron
 */
class MissedOrders extends CliJob
{
    /**
     * @return Result
     * @throws \Exception
     */
    public function run(): Result
    {
        $result = new Result();
        if (!Loader::includeModule('sale')) {
            $result->addError('Модуль "sale" не установлен');
            return $result;
        }
        $orders = [];
        $rsOrders = Order::getList([
            'filter' => [
                '=STATUS_ID' => OrderStatus::getInitialStatus(),
                'CANCELED' => false,
                '<DATE_STATUS' => (new DateTime())->add('-2D'),
            ],
            'order' => ['ID' => 'ASC'],
            'select' => ['ID', 'ACCOUNT_NUMBER']
        ]);

        while ($order = $rsOrders->fetch()) {
            $orders[$order['ID']] = $order;
        }
        $result->setData($orders);

        if (empty($orders)) {
            return $result;
        }

        $error = '';
        try {
            $sent = Event::sendImmediate([
                'EVENT_NAME' => 'MISSED_ORDERS',
                'LID' => \CSite::GetDefSite(),
                'C_FIELDS' => [
                    'ORDERS' => implode("\n", array_map(function ($order) {
                        return '[' . $order['ID'] . '] ' . $order['ACCOUNT_NUMBER'];
                    }, $orders)),
                ],
            ]);
        } catch (\Throwable $e) {
            $sent = Event::SEND_RESULT_NONE;
            $result->addError(new Error($e->getMessage(), $e->getCode(), $e->getTraceAsString()));
            $error = "\n" . $e->getMessage() . "\n" . $e->getTraceAsString();
        }

        if ($sent != Event::SEND_RESULT_SUCCESS) {
            mail(
                Option::get('main', 'email_from'),
                'Ошибка отправки почты',
                'Произошла ошибка при попытке отправить сообщение MISSED_ORDERS, статус отправки "' . $sent . '"' . $error
            );
        }

        return $result;
    }
}