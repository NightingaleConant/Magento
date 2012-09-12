<?php
/**
 * @author - Kalpesh Mehta
 * @desc - Installer script to add new order status "freetrial" in database
 */

$installer = $this;
$installer->startSetup();
$installer->run("
    INSERT INTO sales_order_status VALUE ('freetrial', 'In Free Trial');
    INSERT INTO sales_order_status_state VALUE ('freetrial', 'processing', 0);
");
$installer->endSetup();
?>
