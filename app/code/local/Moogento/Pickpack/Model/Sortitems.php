<?php
class Moogento_Pickpack_Model_Sortitems
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'sku', 'label'=>Mage::helper('pickpack')->__('SKU')),
            array('value' => 'attribute', 'label'=>Mage::helper('pickpack')->__('Custom Attribute')),
            array('value' => 'Mcategory', 'label'=>Mage::helper('pickpack')->__('Product Category Label')),
        );
    }

}
