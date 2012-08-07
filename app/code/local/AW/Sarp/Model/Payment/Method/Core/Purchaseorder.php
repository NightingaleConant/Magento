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
 */class AW_Sarp_Model_Payment_Method_Core_Purchaseorder extends Mage_Payment_Model_Method_Purchaseorder
{
    protected static $poIsset = false;

    /**
     * Validate payment method information object
     *
     * @param   Mage_Payment_Model_Info $info
     * @return  Mage_Payment_Model_Abstract
     */
    public function validate()
    {
        if (AW_Sarp_Model_Subscription::isIterating()) {
            if (!self::$poIsset) {
                $info = $this->getInfoInstance();
                if ($info) {
                    $poNumber = $info->getData('po_number');
                    if ($poNumber) {
                        $info->setPoNumber($poNumber . '-' . Mage::getModel('core/date')->date('dmy'));
                        self::$poIsset = true;
                    }
                }
            }

            return $this;
        } else {
            return parent::validate();
        }
    }
}