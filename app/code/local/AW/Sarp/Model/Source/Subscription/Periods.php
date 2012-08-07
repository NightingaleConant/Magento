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
class AW_Sarp_Model_Source_Subscription_Periods extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Retrive all attribute options
     *
     * @return array
     */

    public static $options;
    public static $options_by_id;
    protected $_collection = false;

    /**
     * Returns period types as array
     * @return array
     */
    public function getAllOptions()
    {

        if (!self::$options) {
            self::$options_by_id = array();
            $_options = array();
            $periods = $this->getCollection();

            if (!$this->getCollection()->hasProductFilterApplied() || ($this->getCollection()->hasNoSubscriptionOption())) {
                $periods->addItem(Mage::getModel('sarp/period')->setId('-1')->setName(Mage::helper('sarp')->__('No subscription')));
            }

            foreach ($periods as $Period) {
                $_options[] = array('value' => $Period->getId(), 'label' => $Period->getName());
                self::$options_by_id[$Period->getId()] = $Period->getName();
            }
            self::$options = $_options;
        }


        return self::$options;
    }

    /**
     * Returns array ready for use by grid
     * @return array
     */
    public function getGridOptions()
    {
        $items = $this->getAllOptions();
        $out = array();
        array_shift($items);
        foreach ($items as $item) {
            $out[$item['value']] = $item['label'];
        }
        return $out;
    }

    /**
     * Returns oeriods collection
     * @return AW_Sarp_Model_Mysql4_Period_Collection
     */
    public function getCollection()
    {
        if (!$this->_collection) {
            $this->_collection = Mage::getModel('sarp/period')->getCollection();
        }
        return $this->_collection;
    }

    /**
     * @param object $value
     * @return
     */
    public function getOptionText($value)
    {

        $out = array();
        if (is_string($value)) {
            $value = explode(',', $value);
        }
        $this->getAllOptions();
        if (is_array($value)) {
            foreach ($value as $key) {
                $out[] = @self::$options_by_id[$key];
            }
        }


        return implode(',', $out);
    }

}