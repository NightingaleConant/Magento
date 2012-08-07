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
class AW_Sarp_Model_Source_Periods extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Retrive all attribute options
     *
     * @return array
     */

    const PERIOD_DAYS = 'day';
    const PERIOD_WEEKS = 'week';
    const PERIOD_MONTHS = 'month';
    const PERIOD_YEARS = 'year';

    public static $options;
    public static $options_by_id;
    protected $_collection = false;

    /**
     * Returns period types as array
     * @return array
     */
    public function getAllOptions()
    {
        return array(
            array('label' => Mage::helper('sarp')->__('Days'), 'value' => self::PERIOD_DAYS),
            array('label' => Mage::helper('sarp')->__('Weeks'), 'value' => self::PERIOD_WEEKS),
            array('label' => Mage::helper('sarp')->__('Months'), 'value' => self::PERIOD_MONTHS),
            array('label' => Mage::helper('sarp')->__('Years'), 'value' => self::PERIOD_YEARS)
        );
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

    /**
     * TODO check if this is realy needed
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