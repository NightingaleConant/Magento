<?php
/**
 * Used in creating options for Yes|No config value selection
 *
 */
class Moogento_Pickpack_Model_Noyes
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('pickpack')->__('Yes')),
            array('value' => 0, 'label'=>Mage::helper('pickpack')->__('No')),
        );
    }

}
