<?php
function woocommerce_blocks_do_action($block_content, $block)
{
    {
        $blocks = array( // Список блоков, для которых нужно выполнить действие
            'woocommerce/cart',
            'woocommerce/cart-totals-block',
            'woocommerce/cart-order-summary-block',
            'woocommerce/cart-order-summary-heading-block',
            'woocommerce/cart-order-summary-coupon-form-block',
            'woocommerce/cart-order-summary-subtotal-block',
            'woocommerce/cart-order-summary-fee-block',
            'woocommerce/cart-order-summary-discount-block',
            'woocommerce/cart-order-summary-shipping-block',
            'woocommerce/cart-order-summary-taxes-block',
            'woocommerce/cart-items-block',
            'woocommerce/cart-line-items-block',
            'woocommerce/cart-cross-sells-block',
            'woocommerce/cart-cross-sells-products-block',
            'woocommerce/cart-express-payment-block',
            'woocommerce/proceed-to-checkout-block',
            'woocommerce/cart-accepted-payment-methods-block',
        );
        if (in_array($block['blockName'], $blocks)) { // Проверяем, является ли текущий блок одним из указанных
            ob_start(); // Начинаем буферизацию вывода
            do_action('gamestore_before_' . $block['blockName']); // Выполняем действие перед выводом блока
            echo $block_content; // Выводим содержимое блока
            do_action('gamestore_after_' . $block['blockName']); // Выполняем действие после вывода блока
            $block_content = ob_get_contents(); // Получаем содержимое буфера вывода
            ob_end_clean(); // Очищаем буфер вывода и отключаем его
        }
    }
    return $block_content;
}

add_filter('render_block', 'woocommerce_blocks_do_action', 9999, 2); // Фильтр для обработки рендеринга блоков WooCommerce

add_action('gamestore_before_woocommerce/cart', function(){
   // echo 'Hello before';
});

add_action('gamestore_after_woocommerce/cart', function(){
   // echo 'Hello after';
});