<?php
class Moogento_Pickpack_Model_Weightunit
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'lb', 'label'=>Mage::helper('pickpack')->__('Lb')),
            array('value' => 'kg', 'label'=>Mage::helper('pickpack')->__('Kg'))
        );
    }

}
