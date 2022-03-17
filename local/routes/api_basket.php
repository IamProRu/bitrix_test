<?php

use Bitrix\Main\Routing\RoutingConfigurator;
use Iampro\Restapi\Controller\{Basket, BasketItem};

return function (RoutingConfigurator $routes) {
    $routes
        ->prefix('api/v1/basket')
        ->name('api_basket_')
        ->group(function (RoutingConfigurator $routes) {
            $routes
                ->name('get')
                ->get('', [Basket::class, 'get']);
            $routes
                ->name('add')
                ->post('', [Basket::class, 'add']);
            $routes
                ->name('delete')
                ->any('', [Basket::class, 'delete'])
                ->methods(['DELETE']);
            $routes
                ->name('item_get')
                ->get('{id}/', [BasketItem::class, 'get'])
                ->where('id', '[0-9]+');
            $routes
                ->name('item_update')
                ->any('{id}/', [BasketItem::class, 'update'])
                ->where('id', '[0-9]+')
                ->methods(['PATCH']);
            $routes
                ->name('item_delete')
                ->any('{id}/', [BasketItem::class, 'delete'])
                ->where('id', '[0-9]+')
                ->methods(['DELETE']);
        });

};