<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Block_Adminhtml_Order_Grid_Renderer_Datetime extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Datetime
{
public function render(Varien_Object $row)
    {
        if ($data = $this->_getValue($row)) {
            $format = $this->_getFormat();
            try {
                $data = Mage::app()->getLocale()->date($data, Varien_Date::DATETIME_INTERNAL_FORMAT, null, false)->toString($format);
            }
            catch (Exception $e)
            {
                $data = Mage::app()->getLocale()->date($data, Varien_Date::DATETIME_INTERNAL_FORMAT, null, false)->toString($format);
            }
            return $data;
        }
        return $this->getColumn()->getDefault();
    }
}