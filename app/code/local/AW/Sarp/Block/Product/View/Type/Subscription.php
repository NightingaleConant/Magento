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
class AW_Sarp_Block_Product_View_Type_Subscription extends Varien_Object
{
    const TEMPLATE_RADIOS = 'aw_sarp/product/view/subscription/radio_selector.phtml'; // Radios selector
    const TEMPLATE_OPTIONS = 'aw_sarp/product/view/subscription/selector.phtml'; // Normal HTML selector

    const DATE_FIELD_NAME = "aw_sarp_subscription_start";

    protected $_productBlock;

    public function __construct($productBlock)
    {
        $this->_productBlock = $productBlock;
        parent::__construct();
    }

    protected function getProductBlock()
    {
        return $this->_productBlock;
    }

    /**
     * Returns all available subscription types
     * @return array
     */
    public function getSubscriptionTypes()
    {
        if (!$this->getData('subscription_types')) {
            $data = Mage::getModel('sarp/source_subscription_periods');
            $data->getCollection()->addProductFilter($this->getProduct());
            $this->hasSubscriptionOptions();
            $this->setData('subscription_types', $data->getAllOptions());
        }
        return $this->getData('subscription_types');
    }

    /**
     * Returns default period
     * @return AW_Sarp_Model_Period
     */
    public function getDefaultPeriod()
    {
        if (!$this->getData('default_period')) {
            $this->setData('default_period', Mage::getModel('sarp/period')->load($this->getDefaultPeriodId()));
        }
        return $this->getData('default_period');
    }

    /**
     * Returns defult period id
     * @return int
     */
    public function getDefaultPeriodId()
    {
        foreach ($this->getSubscriptionTypes() as $k => $v) {
            return $v['value'];
        }
    }

    /**
     * Returns current product
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('product');
    }

    /**
     * Returns ready-to-use calendar field HTML
     * @return string
     */
    public function getCalendarHtml()
    {
        $zDate = new Zend_Date($this->getProductBlock()->formatDate($this->getDefaultPeriod()->getNearestAvailableDay(), Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), null, Mage::app()->getLocale()->getLocaleCode());
        $date = $zDate->toString(preg_replace(array('/M/', '/d/'), array('MM', 'dd'), Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)));
        $calendar = $this->getProductBlock()
                ->getLayout()
                ->createBlock('sarp/html_date')
                ->setId(self::DATE_FIELD_NAME)
                ->setName(self::DATE_FIELD_NAME)
                ->setPeriod($this->getDefaultPeriod())
                ->setClass('product-custom-option datetime-picker input-text')
                ->setImage(Mage::getDesign()->getSkinUrl('aw_sarp/images/grid-cal.gif'))
                ->setValue($date)
                ->setFormat(Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
        return $calendar->getHtml();
    }

    /**
     * Returns true if product has subscriptions options
     * @return bool
     */
    public function hasSubscriptionOptions()
    {
        return $this->getProduct()->getTypeInstance()->hasSubscriptionOptions();
    }

    public function requiresSubscription()
    {
        return true;
    }

    /**
     * Return current template
     * @return string
     */
    public function getTemplate()
    {
        if (Mage::getStoreConfig(AW_Sarp_Helper_Config::XML_PATH_APPEARANCE_USE_RADIOS)) {
            return self::TEMPLATE_RADIOS;
        } else {
            return self::TEMPLATE_OPTIONS;
        }
    }
}

?>