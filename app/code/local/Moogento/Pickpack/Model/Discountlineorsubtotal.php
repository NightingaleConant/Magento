<?php
class Moogento_Pickpack_Model_Discountlineorsubtotal
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'line', 'label'=>Mage::helper('pickpack')->__('Include discounts in product line price')),
            array('value' => 'subtotal', 'label'=>Mage::helper('pickpack')->__('Show discounts in order subtotal')),
        );
    }

}
