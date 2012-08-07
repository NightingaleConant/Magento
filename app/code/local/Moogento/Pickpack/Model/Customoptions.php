<?php
class Moogento_Pickpack_Model_Customoptions
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'yes', 'label'=>Mage::helper('pickpack')->__('Yes, below product line')),
            array('value' => 'yescol', 'label'=>Mage::helper('pickpack')->__('Yes, in separate column')),
            array('value' => 'no', 'label'=>Mage::helper('pickpack')->__('No'))
        );
    }

}
