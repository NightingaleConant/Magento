<?php

/**
 *
 */
class CJ_Reports_Model_Sales_Invoice extends Mage_Sales_Model_Order_Invoice
{

    public function getData($key='', $index=null)
    {
        switch ($key) {
            case '0days':
                return $this->getCurrentTotal();
                break;
            case '30days':
                return $this->get30DayTotal();
                break;
            case '60days':
                return $this->get60DayTotal();
                break;
            case '90days':
                return $this->get90DayTotal();
                break;
            case '120days':
                return $this->get120DayTotal();
                break;
            case '180days':
                return $this->get180DayTotal();
                break;
            case '240days':
                return $this->get240DayTotal();
                break;
            default:
                return parent::getData($key, $index);
        }
    }

    public function getPeriodValue($startDays, $endDays = false)
    {
        $daysOld = $this->getAgeInDays();
        if ($daysOld >= $startDays) {
            if ($endDays !== false) {
                if ($daysOld <= $endDays) {
                    return (float) $this->getData('total_paid');
                }
                return null;
            }
            return (float) $this->getData('total_paid');
        }
        return null;
    }

    /**
     * Get age of an invoice
     */
    public function getAgeInDays()
    {
        $now = new DateTime('now');
        $now->setTime(23,59,59);

        $created = new DateTime($this->getCreatedAt());
        $diff = $now->diff($created);
        return $diff->days;
    }

    public function getCurrentTotal()
    {
        return $this->getPeriodValue(0, 30);
    }

    public function get30DayTotal()
    {
        return $this->getPeriodValue(31, 60);
    }

    public function get60DayTotal()
    {
        return $this->getPeriodValue(61, 90);
    }

    public function get90DayTotal()
    {
        return $this->getPeriodValue(91, 120);
    }

    public function get120DayTotal()
    {
        return $this->getPeriodValue(121, 180);
    }

    public function get180DayTotal()
    {
        return $this->getPeriodValue(181, 240);
    }

    public function get240DayTotal()
    {
        return $this->getPeriodValue(241);
    }
}
