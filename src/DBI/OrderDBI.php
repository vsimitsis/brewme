<?php

namespace BrewMe\DBI;

use BrewMe\AbstractDBI;

class OrderDBI extends AbstractDBI {

    public static function createOrder(array $order) {}

    public static function updateOrderStatus(int $orderId, int $status) {}

    public static function getOrdersByStatus(int $status) {}

}