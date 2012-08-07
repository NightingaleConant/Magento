<?php

/**
 *
 */
class CJ_Reports_Model_Customer extends Mage_Customer_Model_Customer
{

    public function getData($key = '', $index = null)
    {
        switch ($key) {
            case 'value-ar-total':
                return $this->getDueTotals();
                break;
            case 'value-ar-current':
                return $this->getDueTotals(0, 29);
                break;
            case 'value-ar-30days':
                return $this->getDueTotals(30, 59);
                break;
            case 'value-ar-60days':
                return $this->getDueTotals(60, 89);
                break;
            case 'value-ar-90days':
                return $this->getDueTotals(90, 119);
                break;
            case 'value-ar-120days':
                return $this->getDueTotals(120, 179);
                break;
            case 'value-ar-180days':
                return $this->getDueTotals(180, 239);
                break;
            case 'value-ar-240days':
                return $this->getDueTotals(240);
                break;

            default:
            return parent::getData($key, $index);
        }

        return parent::getData($key, $index);
    }

    public function getDueTotals($periodFrom = null, $periodTo = null)
    {
        $salesModelCollection = Mage::getModel('sales/order')->getCollection();
        $select = $salesModelCollection->getSelect()->where('customer_id = ' . $this->getId());
        if (null !== $periodFrom && null !== $periodTo) {
            $select->where(sprintf('created_at BETWEEN DATE_SUB(curdate(), INTERVAL %d day) AND DATE_SUB(curdate(), INTERVAL %d day)', $periodTo, $periodFrom));
        }
        if (null === $periodTo && null !== $periodFrom ) {
            $select->where(sprintf('created_at < DATE_SUB(curdate(), INTERVAL %d day) ', $periodFrom));
        }
        try {
        $amounts = array();
        foreach ($salesModelCollection as $order) {
            if (!isset($amounts[$order->getOrderCurrencyCode()])) {

                $amounts[$order->getOrderCurrencyCode()] = 0;
            }
            $amounts[$order->getOrderCurrencyCode()] += $order->getTotalDue();
        }
        $return = '';
        foreach ($amounts as $currency => $amount) {
            $return .= sprintf('%s %s ', $currency, $amount);
        }
        } Catch (Exception $e) {
            echo $select;exit;
        }
        return $return;
    }
}
