<?php
class Moogento_Pickpack_Model_Sortorder
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'ascending', 'label'=>Mage::helper('pickpack')->__('Ascending')),
            array('value' => 'descending', 'label'=>Mage::helper('pickpack')->__('Descending')),
        );
    }

}
