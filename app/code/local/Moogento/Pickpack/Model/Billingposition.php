<?php
class Moogento_Pickpack_Model_Billingposition
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '0', 'label'=>Mage::helper('pickpack')->__('Standard : shipping on left, billing on right')),
            array('value' => '1', 'label'=>Mage::helper('pickpack')->__('Switched, billing on left, shipping on right')),
        );
    }

}
