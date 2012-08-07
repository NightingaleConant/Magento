<?php
class Moogento_Pickpack_Model_Custommessage
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'yes', 'label'=>Mage::helper('pickpack')->__('Yes, one message')),
            array('value' => 'yes2', 'label'=>Mage::helper('pickpack')->__('Yes, 2 messages, depending on customer group')),
            array('value' => 'no', 'label'=>Mage::helper('pickpack')->__('No'))
        );
    }

}
