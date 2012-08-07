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
class AW_Sarp_Model_Product_Type_Default
{

    /*
     *  checking sended period value, if product doesn't include this period, then  function throw exception with message
    */

    public function checkPeriod(AW_Sarp_Model_Catalog_Product $product, Varien_Object $buyRequest)
    {

        if (!$product->getAwSarpPeriod())
            $product = Mage::getModel('catalog/product')->load($product->getId());
        if ($buyRequest->getAwSarpSubscriptionType() != '' && !in_array($buyRequest->getAwSarpSubscriptionType(), explode(',', $product->getAwSarpPeriod()))) {
            throw new Exception('Wrong Period passed!');
        }
    }

    public function validateSubscription(AW_Sarp_Model_Catalog_Product $product, Varien_Object $buyRequest)
    {
        if ($options = $buyRequest->getOptions()) {
            if (!isset($options['aw_sarp_subscription_start']) && !in_array(AW_Sarp_Model_Period::PERIOD_TYPE_NONE, explode(',', $product->getAwSarpPeriod()))) {
                if(!$buyRequest->getAwSarpSubscriptionStart())
                    return false;
                else
                    return true;
            } else {
                return true;
            }
        }
        if (!$buyRequest->getAwSarpSubscriptionStart() && !in_array(AW_Sarp_Model_Period::PERIOD_TYPE_NONE, explode(',', $product->getAwSarpPeriod())))
            return false;
        else
            return true;
    }
}