<?php
/*
 * @author - Kalpesh Mehta
 * @desc - Rewriting file Mage/Adminhtml/Block/Sales/Order/Invoice/View.php
 */
class NightingaleConant_FreeTrial_Block_View extends Mage_Adminhtml_Block_Sales_Order_Invoice_View {
    
    /*
     * Overriding getHeaderText() method to mention invoices as "offline" and may be "unpaid" for orders
     * whose payment is made by Free Trial.
     */
    public function getHeaderText()
    {
        if ($this->getInvoice()->getEmailSent()) {
            $emailSent = Mage::helper('sales')->__('the invoice email was sent');
        }
        else {
            $emailSent = Mage::helper('sales')->__('the invoice email is not sent');
        }
        
        $payMethod = $this->getInvoice()->getOrder()->getPayment()->getMethod();
        if($this->getInvoice()->getState() == 1 && $payMethod == 'freetrial') {
            $stateName = $this->getInvoice()->getStateName() . ' (Off-line and Unpaid)';
        } else if($this->getInvoice()->getState() == 2 && $payMethod == 'freetrial') {
            $stateName = $this->getInvoice()->getStateName() . ' (Off-line)';
        } else {
            $stateName = $this->getInvoice()->getStateName();
        }
        return Mage::helper('sales')->__('Invoice #%1$s | %2$s | %4$s (%3$s)', $this->getInvoice()->getIncrementId(), $stateName, $emailSent, $this->formatDate($this->getInvoice()->getCreatedAtDate(), 'medium', true));
    }
    
}

?>
