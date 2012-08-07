<?php


class NGC_Installment_Model_Master_Api extends Mage_Api_Model_Resource_Abstract
{

    public function create($masterData)
    {
        try {
            $master = Mage::getModel('installment/master')
                ->setData($masterData)
                ->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
            // We cannot know all the possible exceptions,
            // so let's try to catch the ones that extend Mage_Core_Exception
        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        return $master->getId();
    }

    public function info($masterId)
    {
        $master = Mage::getModel('installment/master')->load($masterId);
        if (!$master->getId()) {
            $this->_fault('not_exists');
        }

        return $master->toArray();
    }

    public function items($filters)
    {
        $collection = Mage::getModel('installment/master')->getCollection()->addFieldToSelect('*');

        if (is_array($filters)) {
            try {
                foreach ($filters as $field => $value) {
                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
                // If we are adding filter on non-existent attribute
            }
        }

        $result = array();
        foreach ($collection as $master) {
            $result[] = $master->toArray();
        }

        return $result;
    }

    public function update($masterId, $masterData)
    {
        $master = Mage::getModel('installment/master')->load($masterId);
        if (!$master->getId()) {
            $this->_fault('not_exists');
        }

        $master->addData($masterData)->save();

        return true;
    }

    public function delete($masterId)
    {
        $master = Mage::getModel('installment/master')->load($masterId);
        if (!$master->getId()) {
            $this->_fault('not_exists');
        }

        try {
            $master->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_deleted', $e->getMessage());
        }

        return true;
    }
}