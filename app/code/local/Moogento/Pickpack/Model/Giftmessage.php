<?php
class Moogento_Pickpack_Model_Giftmessage
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'yesunder', 'label'=>Mage::helper('pickpack')->__('Yes, under product list')),
            array('value' => 'yesbox', 'label'=>Mage::helper('pickpack')->__('Yes, in positionable box (handy for tear-off labels)')),
            array('value' => 'yesnewpage', 'label'=>Mage::helper('pickpack')->__('Yes, on a new page')),
            array('value' => 'no', 'label'=>Mage::helper('pickpack')->__('No'))
        );
    }

}
