<?php

class NGC_Customer_Model_Customer_Api extends Mage_Customer_Model_Customer_Api
{
    /**
     * Create new customer
     *
     * @param array $customerData
     * @return int
     */
    public function create($customerData)
    {
        $customerData = $this->_prepareData($customerData);

        try {
            $customer = Mage::getModel('customer/customer');
            if (!array_key_exists('email', $customerData) || (array_key_exists('email', $customerData) && empty($customerData['email']))) {
                $session = Mage::getSingleton('adminhtml/session_quote');
                $host = $session->getStore()
                    ->getConfig(Mage_Customer_Model_Customer::XML_PATH_DEFAULT_EMAIL_DOMAIN);
                $account = $customer->getIncrementId() ? $customer->getIncrementId() : time();

                $customerData['email'] = $account.'@'. $host;
            }

            $customer->setData($customerData)
                ->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        return $customer->getId();
    }
}