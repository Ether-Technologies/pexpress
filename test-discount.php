<?php
require_once('/Users/atiqisrak/myspace/Web_Projects/pexpress/wp-load.php');

$product_id = 15; // I need a valid product ID. Let's find one.
$args = array('post_type' => 'product', 'posts_per_page' => 1);
$products = get_posts($args);
if (empty($products)) {
    echo "No products found.\n";
    exit;
}
$product = wc_get_product($products[0]->ID);
$regular_price = $product->get_regular_price();
if (!$regular_price) {
    $regular_price = $product->get_price();
}

echo "Product: " . $product->get_name() . " (ID: " . $product->get_id() . ")\n";
echo "Regular Price: " . $regular_price . "\n";

if (class_exists('\Wdr\App\Controllers\ManageDiscount')) {
    $discounted = \Wdr\App\Controllers\ManageDiscount::calculateProductDiscountPrice($regular_price, $product, 1);
    echo "Discounted Price (qty 1): " . print_r($discounted, true) . "\n";
    
    $discounted2 = \Wdr\App\Controllers\ManageDiscount::calculateProductDiscountPrice($regular_price, $product, 5);
    echo "Discounted Price (qty 5): " . print_r($discounted2, true) . "\n";
} else {
    echo "WDR class not found.\n";
}
