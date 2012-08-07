<?php
class Moogento_Pickpack_Model_Notesfilter
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'yesfrontend', 'label'=>Mage::helper('pickpack')->__('Yes, show only notes tagged \'visible on frontend\'')),
            array('value' => 'yestext', 'label'=>Mage::helper('pickpack')->__('Yes, filter by notes text')),
            array('value' => 'no', 'label'=>Mage::helper('pickpack')->__('No'))
            //array('value' => 'no', 'label'=>Mage::helper('pickpack')->__('No'))
        );
    }

}
