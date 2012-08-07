<?php
class Moogento_Pickpack_Model_Order extends Mage_Sales_Model_Order
{
    public function canDelete()
    {

        if ($this->getState() === self::STATE_CANCELED ) {
            if (!$this->hasShipments() && !$this->hasCreditmemos() && !$this->hasInvoices() ) {
                return true;
            }
        }
        return false;
    }
}
