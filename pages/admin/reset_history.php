<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

if (is_post()) {
    $_db->query('
        DELETE FROM topping_item;
        DELETE FROM product_order;
        DELETE FROM orders;
        ALTER TABLE topping_item AUTO_INCREMENT = 1;
        ALTER TABLE product_order AUTO_INCREMENT = 1;
        ALTER TABLE orders AUTO_INCREMENT = 1;
    ');
    temp('info', 'Order and item tables reset');
}

redirect('order_main.php');

// ----------------------------------------------------------------------------
