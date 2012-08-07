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
 */class AW_Sarp_Block_Sales_Order_Items_Renderer_Default extends Mage_Sales_Block_Order_Item_Renderer_Default
{
    public function getItemOptions()
    {


        $result = array();
        if ($options = $this->getOrderItem()->getProductOptions()) {
            if (isset($options['info_buyRequest'])) {
                $startDateLabel = $this->getOrderItem()->getIsVirtual() ? $this->__("Subscription start:")
                        : $this->__("First delivery:");
                $periodTypeId = @$options['info_buyRequest']['aw_sarp_subscription_type'];
                $periodStartDate = @$options['info_buyRequest']['aw_sarp_subscription_start'];
                if (($periodTypeId > 0) && $periodStartDate) {
                    $result[] = array(
                        'label' => $this->__('Subscription type:'),
                        'value' => Mage::getModel('sarp/period')->load($periodTypeId)->getName()
                    );

                    $result[] = array(
                        'label' => $this->__($startDateLabel),
                        'value' => $periodStartDate

                    );
                }
            }
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }

        }
        return $result;
    }

}