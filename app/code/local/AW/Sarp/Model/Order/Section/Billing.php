<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Sarp
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Sarp_Model_Order_Section_Billing extends AW_Sarp_Model_Order_Section
{

    /**
     * Saves billing section to order
     * @param Mage_Sales_Model_Order $Order
     * @return
     */
    public function preset(Mage_Sales_Model_Order $order, $data)
    {
        $ba = $order->getBillingAddress();
        foreach ($data as $k => $v) {
            $ba->setData($k, $v);
        }
        $ba->implodeStreetAddress();
        $ba->save();

        return $order;
    }
}