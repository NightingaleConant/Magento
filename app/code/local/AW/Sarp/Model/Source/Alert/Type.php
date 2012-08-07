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
class AW_Sarp_Model_Source_Alert_Type extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    /**
     * Retrive all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }

    public function toOptionArray()
    {
        return array(
            array('value' => AW_Sarp_Model_Alert::TYPE_DATE_START,          'label' => Mage::helper('sarp')->__('First Delivery')),
            array('value' => AW_Sarp_Model_Alert::TYPE_DELIVERY,            'label' => Mage::helper('sarp')->__('Delivery')),
            array('value' => AW_Sarp_Model_Alert::TYPE_DATE_EXPIRE,         'label' => Mage::helper('sarp')->__('Expiration date')),
            array('value' => AW_Sarp_Model_Alert::TYPE_NEW_SUBSCRIPTION,    'label' => Mage::helper('sarp')->__('New subscription')),
            array('value' => AW_Sarp_Model_Alert::TYPE_SUSPENDED,           'label' => Mage::helper('sarp')->__('Suspended')),
            array('value' => AW_Sarp_Model_Alert::TYPE_CANCEL_SUBSCRIPTION, 'label' => Mage::helper('sarp')->__('Unsubscription')),
            array('value' => AW_Sarp_Model_Alert::TYPE_ACTIVATION,          'label' => Mage::helper('sarp')->__('Activation'))
        );
    }

    /**
     * Returns label for value
     * @param string $value
     * @return string
     */
    public function getLabel($value)
    {
        $options = $this->toOptionArray();
        foreach ($options as $v) {
            if ($v['value'] == $value) {
                return $v['label'];
            }
        }
        return '';
    }

    /**
     * Returns array ready for use by grid
     * @return array
     */
    public function getGridOptions()
    {
        $items = $this->getAllOptions();
        $out = array();
        foreach ($items as $item) {
            $out[$item['value']] = $item['label'];
        }
        return $out;
    }

}
