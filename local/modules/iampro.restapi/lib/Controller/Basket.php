<?php

namespace Iampro\Restapi\Controller;

use Bitrix\Main\{Engine\Controller, Engine\ActionFilter, Context, Loader, Error};
use Bitrix\Sale;
use Bitrix\Catalog\Product\Basket as CatalogBasket;

class Basket extends Controller
{
    /**
     * Получение содержимого корзины
     *
     * @return array|null
     */
    public function getAction(): ?array
    {
        try {
            if (!Loader::includeModule('sale')) {
                return null;
            }

            // перебор коллекции корзины
            $basket = static::loadBasket();
            $items = [];
            /** @var Sale\BasketItem $basketItem */
            foreach ($basket as $basketItem) { // $basket->getOrderableItems() , если нужны только товары, доступные для покупки
                $items[$basketItem->getId()] = $this->convertKeysToCamelCase($basketItem->toArray());
            }

            return $items;
        } catch (\Exception $e) {
            $this->addError($this->buildErrorFromException($e));
            return null;
        }
    }

    /**
     * Добавление товара в корзину
     *
     * @param int $id ID продукта
     * @param float|int $quantity Количество
     * @return array|null
     */
    public function addAction(int $id, float $quantity = 1): ?array
    {
        try {
            if (!Loader::includeModule('catalog') || !Loader::includeModule('sale')) {
                return null;
            }

            // добавляем товар в корзину через метод из модуля catalog
            $addResult = CatalogBasket::addProduct([
                'PRODUCT_ID' => $id,
                'QUANTITY' => $quantity,
            ]);
            if (!$addResult->isSuccess()) {
                throw new \Exception(implode("\n", $addResult->getErrorMessages()));
            }

            // сохраняем изменения корзины
            $basket = static::loadBasket();
            //$refreshStrategy = Sale\Basket\RefreshFactory::create(Sale\Basket\RefreshFactory::TYPE_FULL);
            //$basket->refresh($refreshStrategy);
            $saveResult = $basket->save();
            if (!$saveResult->isSuccess()) {
                throw new \Exception(implode("\n", $saveResult->getErrorMessages()));
            }

            return $this->convertKeysToCamelCase($addResult->getData()); // static::getAction()
        } catch (\Exception $e) {
            $this->addError($this->buildErrorFromException($e));
            return null;
        }
    }

    public function updateAction(): ?array
    {
        return null;
    }

    /**
     * Очистка корзины
     *
     * @return bool
     * @throws \Bitrix\Main\LoaderException
     */
    public function deleteAction(): bool
    {
        try {
            if (!Loader::includeModule('sale')) {
                return false;
            }

            // очистка коллекции корзины с ее сохранением
            $basket = static::loadBasket();
            $basket->clearCollection();
            $saveResult = $basket->save();
            if (!$saveResult->isSuccess()) {
                throw new \Exception(implode("\n", $saveResult->getErrorMessages()));
            }
            return true; // static::getAction()

        } catch (\Exception $e) {
            $this->addError($this->buildErrorFromException($e));
            return false;
        }
    }

    /**
     * Получение корзины для текущего посетителя
     *
     * @return Sale\BasketBase
     * @throws \Exception
     */
    public static function loadBasket(): Sale\BasketBase
    {
        /** @var Sale\Basket\Storage $basketStorage */
        $basketStorage = Sale\Basket\Storage::getInstance(Sale\Fuser::getId(), Context::getCurrent()->getSite());
        return $basketStorage->getBasket();
    }

    /**
     * Конфигурация разрешенных действий контроллера с пре- и пост-фильтрами
     *
     * @return array
     */
    public function configureActions(): array
    {
        return [
            'get' => [
                'prefilters' => [],
                'postfilters' => [],
            ],
            'add' => [
                'prefilters' => [
                    new ActionFilter\ContentType([
                        ActionFilter\ContentType::JSON,
//                        'application/x-www-form-urlencoded',
//                        'multipart/form-data',
                    ]),
                ],
                'postfilters' => [],
            ],
//            'update' => [
//                'prefilters' => [],
//                'postfilters' => [],
//            ],
            'delete' => [
                'prefilters' => [],
                'postfilters' => [],
            ],
        ];
    }
}