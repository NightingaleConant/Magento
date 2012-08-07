<?php
class Moogento_Pickpack_Model_Notesmessage
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'yesunder', 'label'=>Mage::helper('pickpack')->__('Under product list')),
            array('value' => 'yesbox', 'label'=>Mage::helper('pickpack')->__('In positionable box (handy for tear-off labels)')),
            array('value' => 'yesemail', 'label'=>Mage::helper('pickpack')->__('Under shipping details'))
            //array('value' => 'no', 'label'=>Mage::helper('pickpack')->__('No'))
        );
    }

}
