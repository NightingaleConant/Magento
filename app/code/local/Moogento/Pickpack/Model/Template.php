<?php
class Moogento_Pickpack_Model_Template
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('pickpack')->__('Default')),
            array('value' => 1, 'label'=>Mage::helper('pickpack')->__('Shifter Theme (to over-print pre-printed forms)')),
        );
    }

}
