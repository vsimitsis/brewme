<?php

namespace BrewMe\DBI;

use BrewMe\DBI\AbstractDBI;

class OrderDBI extends AbstractDBI {

    public static function createOrder(array $order) {
        return self::default_insert(['user_id', 'type', 'comments', 'status'], $order, self::TABLE_ORDERS);
    }

    public static function changeOrdersStatus($fromStatus, $toStatus) {
        $q = "UPDATE " . self::TABLE_ORDERS . " SET status = ? WHERE status = ?";
        return self::query($q, [$toStatus, $fromStatus]);
    }

    public static function updateOrderStatus(int $orderId, int $status) {}

    public static function getOrdersByStatus(int $status) {
        $q = "SELECT u.username, o.type, o.comments, o.created_at  
                FROM " . self::TABLE_ORDERS . " o 
                INNER JOIN " . self::TABLE_USERS . " u ON u.id = o.user_id
                WHERE o.status = ?"; 
        return self::query_and_fetch($q, [$status]);
    }

}