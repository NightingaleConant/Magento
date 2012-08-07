<?php
class Moogento_Pickpack_Model_Optionschoiceseparated
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '0', 'label'=>Mage::helper('pickpack')->__('No')),
            array('value' => '1', 'label'=>Mage::helper('pickpack')->__('Yes, group parent SKUs and option SKUs')),
            array('value' => '2', 'label'=>Mage::helper('pickpack')->__('Yes, split parent SKUs if option SKUs vary')),
        );
    }

}
