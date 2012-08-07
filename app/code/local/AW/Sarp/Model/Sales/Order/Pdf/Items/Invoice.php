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
class AW_Sarp_Model_Sales_Order_Pdf_Items_Invoice extends Mage_Sales_Model_Order_Pdf_Items_Invoice_Default
{
    public function getItemOptions()
    {
        $opts = ($this->getItem()->getOrderItem()->getProductOptions());
        $options = array();
        if (@$opts['info_buyRequest']) {

            if ($opts['info_buyRequest'] &&
                ($periodTypeId = @$opts['info_buyRequest']['aw_sarp_subscription_type']) &&
                ($periodStartDate = @$opts['info_buyRequest']['aw_sarp_subscription_start'])
            ) {
                $startDateLabel = $this->getItem()->getOrderItem()->getIsVirtual() ? "Subscription start:"
                        : "First delivery:";
                $options[] = array(
                    'label' => Mage::helper('sarp')->__('Subscription type:'),
                    'value' => Mage::getModel('sarp/period')->load($periodTypeId)->getName()
                );
                $options[] = array(
                    'label' => Mage::helper('sarp')->__($startDateLabel),
                    'value' => $periodStartDate

                );
            }
        }
        $options = array_merge($options, parent::getItemOptions());
        return $options;
    }

}
