<?php
class Moogento_Pickpack_Model_Pagesize
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'a4', 'label'=>Mage::helper('pickpack')->__('A4 (Europe standard)')),
            array('value' => 'letter', 'label'=>Mage::helper('pickpack')->__('Letter (US standard)')),
        );
    }

}
