<?php
class NGC_Installment_CronController extends Mage_Core_Controller_Front_Action
{
    public function dailyPaymentCaptureAction()
    {
        $observer = Mage::getModel('installment/observer');

        $observer->dailyCapturePayment();
    }

    public function dailyCaptureInstallmentPaymentAction()
    {
        $observer = Mage::getModel('installment/observer');

        $observer->dailyCaptureInstallmentPayment();
    }
}