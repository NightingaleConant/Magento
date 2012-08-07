<?php


class NGC_Installment_Model_Transaction_Api extends Mage_Api_Model_Resource_Abstract
{

    public function create($transactionData)
    {
        Mage::log(print_r($transactionData));
        try {
            $transaction = Mage::getModel('installment/transaction')
                ->setData($transactionData)
                ->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }

        return $transaction->getId();
    }

    public function info($transactionId)
    {
        $transaction = Mage::getModel('installment/transaction')->load($transactionId);
        if (!$transaction->getId()) {
            $this->_fault('not_exists');
        }

        return $transaction->toArray();
    }

    public function items($filters)
    {
        $collection = Mage::getModel('installment/transaction')->getCollection()->addFieldToSelect('*');

        if (is_array($filters)) {
            try {
                foreach ($filters as $field => $value) {
                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
            }
        }

        $result = array();
        foreach ($collection as $transaction) {
            $result[] = $transaction->toArray();
        }

        return $result;
    }

    public function update($transactionId, $transactionData)
    {
        $transaction = Mage::getModel('installment/transaction')->load($transactionId);
        if (!$transaction->getId()) {
            $this->_fault('not_exists');
        }

        $transaction->addData($transactionData)->save();

        return true;
    }

    public function delete($transactionId)
    {
        $transaction = Mage::getModel('installment/transaction')->load($transactionId);
        if (!$transaction->getId()) {
            $this->_fault('not_exists');
        }

        try {
            $transaction->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_deleted', $e->getMessage());
        }

        return true;
    }
}