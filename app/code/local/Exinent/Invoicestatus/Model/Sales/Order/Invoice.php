<?php

class Exinent_Invoicestatus_Model_Sales_Order_Invoice extends Mage_Sales_Model_Order_Invoice
{
    /**
     * Invoice states
     */
    const STATE_READY_TO_PRINT   = 4;
   
	/**
     * Retrieve invoice states array
     *
     * @return array
     */
    public static function getStates()
    {
        if (is_null(self::$_states)) {
            self::$_states = array(
                self::STATE_OPEN            => Mage::helper('sales')->__('Pending'),
                self::STATE_PAID            => Mage::helper('sales')->__('Paid'),
                self::STATE_CANCELED        => Mage::helper('sales')->__('Canceled'),
                self::STATE_READY_TO_PRINT  => Mage::helper('sales')->__('Ready To Print'),
            );
        }
        return self::$_states;
    }
}