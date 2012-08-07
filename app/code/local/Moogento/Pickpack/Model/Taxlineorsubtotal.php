<?php
class Moogento_Pickpack_Model_Taxlineorsubtotal
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'line', 'label'=>Mage::helper('pickpack')->__('Add tax to product line subtotal [US Standard]')),
            array('value' => 'order', 'label'=>Mage::helper('pickpack')->__('Add tax in order subtotal [Europe Standard]')),
        );
    }

}
