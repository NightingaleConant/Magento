<?php
class Moogento_Pickpack_Model_Pricesoptions
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '1', 'label'=>Mage::helper('pickpack')->__('Yes, with VAT info')),
            array('value' => 'yesnotax', 'label'=>Mage::helper('pickpack')->__('Yes, with no tax info')),
            array('value' => '0', 'label'=>Mage::helper('pickpack')->__('No'))
        );
    }

}
