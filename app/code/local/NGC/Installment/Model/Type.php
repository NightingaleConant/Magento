<?php

class NGC_Installment_Model_Type extends Mage_Core_Model_Abstract
{
    const INSTALLMENT_TYPE_MAX_LENGTH = 255;
    const INSTALLMENT_DESC_MAX_LENGTH = 65534;

    public function __construct()
    {
        $this->_init('installment/type');
    }
}

