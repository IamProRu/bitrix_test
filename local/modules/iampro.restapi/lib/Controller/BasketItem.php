<?php

namespace Iampro\Restapi\Controller;

use Bitrix\Main\{Application, Engine\Controller, Engine\ActionFilter, Loader, Error};

class BasketItem extends Controller
{

    /**
     * Получение записи из корзины
     *
     * @param int $id ID элемента корзины
     * @return array|null
     */
    public function getAction(int $id): ?array
    {
        try {
            if (!Loader::includeModule('sale')) {
                return null;
            }

            $basket = Basket::loadBasket();
            if ($basketItem = $basket->getItemById($id)) {
                return $this->convertKeysToCamelCase($basketItem->toArray());
            }
            throw new \Exception('Not found', 404);
        } catch (\Exception $e) {
            $this->addError($this->buildErrorFromException($e));
            return null;
        }
    }

    /**
     * Обновление записи в корзине
     *
     * @param int $id ID элемента корзины
     * @return bool
     */
    public function updateAction(int $id): bool
    {
        try {
            if (!Loader::includeModule('sale')) {
                return false;
            }

            // Получение запроса в виде ассоциативного массива
            $app = Application::getInstance();
            if ($app->getContext()->getRequest()->getHeaders()->getContentType() == ActionFilter\ContentType::JSON) {
                $post = (new \Bitrix\Main\Engine\JsonPayload())->getData();
            } else {
                $post = $app->getContext()->getRequest()->getPostList()->toArray();
            }

            // обновление количества или удаление позиции корзины
            $quantity = floatval($post['quantity']);
            $basket = Basket::loadBasket();
            $this->decodePostData();
            if ($basketItem = $basket->getItemById($id)) {
                if ($quantity > 0) {
                    $updateResult = $basketItem->setField('QUANTITY', $quantity);
                } else {
                    $updateResult = $basketItem->delete();
                }
                if (!$updateResult->isSuccess()) {
                    throw new \Exception(implode("\n", $updateResult->getErrorMessages()));
                }
                //$refreshStrategy = Sale\Basket\RefreshFactory::create(Sale\Basket\RefreshFactory::TYPE_FULL);
                //$basket->refresh($refreshStrategy);
                $saveResult = $basket->save();
                if (!$saveResult->isSuccess()) {
                    throw new \Exception(implode("\n", $saveResult->getErrorMessages()));
                }
                return true; // static::getAction()
            }
            throw new \Exception('Not found', 404);
        } catch (\Exception $e) {
            $this->addError($this->buildErrorFromException($e));
            return false;
        }
    }

    /**
     * Удаление записи из корзины
     *
     * @param int $id ID элемента корзины
     * @return bool
     */
    public function deleteAction(int $id): bool
    {
        try {
            if (!Loader::includeModule('sale')) {
                return false;
            }

            $basket = Basket::loadBasket();
            if ($basketItem = $basket->getItemById($id)) {
                $deleteResult = $basketItem->delete();
                if (!$deleteResult->isSuccess()) {
                    throw new \Exception(implode("\n", $deleteResult->getErrorMessages()));
                }
                //$refreshStrategy = Sale\Basket\RefreshFactory::create(Sale\Basket\RefreshFactory::TYPE_FULL);
                //$basket->refresh($refreshStrategy);
                $saveResult = $basket->save();
                if (!$saveResult->isSuccess()) {
                    throw new \Exception(implode("\n", $saveResult->getErrorMessages()));
                }
                return true; // static::getAction()
            }
            throw new \Exception('Not found', 404);
        } catch (\Exception $e) {
            $this->addError($this->buildErrorFromException($e));
            return false;
        }
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
            'update' => [
                'prefilters' => [
                    new ActionFilter\ContentType([
                        ActionFilter\ContentType::JSON,
                        'application/x-www-form-urlencoded',
                        'multipart/form-data',
                    ]),
                ],
                'postfilters' => [],
            ],
            'delete' => [
                'prefilters' => [],
                'postfilters' => [],
            ],
        ];
    }
}