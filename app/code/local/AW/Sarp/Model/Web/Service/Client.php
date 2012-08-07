<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Sarp
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */abstract class AW_Sarp_Model_Web_Service_Client extends AW_Core_Object
{

    /**
     * Initializes request model for web client.
     * @return AW_Sarp_Model_Web_Service_Client_Request_Simple
     */
    public function getRequest()
    {
        if (!$this->getData('request')) {
            $this->setRequest(Mage::getModel('sarp/web_service_client_request_simple'));
        }
        return $this->getData('request');
    }

    /**
     * Initializes response model for web client.
     * @return AW_Sarp_Model_Web_Service_Client_Response_Simple
     */
    public function getResponse()
    {
        if (!$this->getData('response')) {
            $this->setResponse(Mage::getModel('sarp/web_service_client_response_simple'));
        }
        return $this->getData('response');
    }


    /**
     * Initializes SOAP service or returns existing
     * @return Zend_Soap_Client
     */
    public function getService($function = null)
    {
        if (!$this->getData('service')) {
            $this->setService(
                new Zend_Soap_Client($this->getWsdl(), $this->getServiceOptions())
            );
        }
        if ($uri = $this->getUri($function)) {
            $this->getData('service')->setLocation($uri);
        }
        return $this->getData('service');
    }

    /**
     * Re-initializes service
     * @return Zend_Soap_Client
     */
    public function resetService()
    {
        $this->setService(null);
        return $this->getService();
    }

    /**
     * Returns current SOAP service options or default options if not set
     * @return array
     */
    public function getServiceOptions()
    {
        if (!$this->getData('service_options')) {
            return $this->getDefaultServiceOptions();
        } else {
            return array_merge($this->getDefaultServiceOptions(), $this->getData('service_options'));
        }
    }

    /**
     * Sets one or more service options
     * @param string $arg
     * @param mixed $value [optional]
     * @return
     */
    public function setServiceOptions($arg, $value = null)
    {
        if (is_array($arg)) {
            $this->_data['service_options'] = array_merge($this->_data['service_options'], $arg);
        } else {
            $this->_data['service_options'][$arg] = $value;
        }
        return $this;
    }

    /**
     * Returns default SOAP service options
     * @return array
     */
    public function getDefaultServiceOptions()
    {
        return array(
            'compression' => SOAP_COMPRESSION_ACCEPT,
            'soap_version' => SOAP_1_2
        );
    }

    /**
     * Retrieve and return current customer
     * @return Varien_Object
     */
    public function getCustomer()
    {
        if (!$this->getData('customer')) {

            $Customer = new Varien_Object(array(
                                               'id' => $this->getQuote()->getCustomerId(),
                                               'name' => $this->getQuote()->getCustomerName(),
                                               'email' => $this->getQuote()->getCustomerEmail()
                                          ));
            $this->setData('customer', $Customer);
        }
        return $this->getData('customer');
    }

    /**
     * Retrieve current quote
     * @return <type>
     */
    public function getQuote()
    {
        if (!$this->getData('quote')) {
            $Quote = $this->getPayment()->getQuote();
            $this->setData('quote', $Quote);
        }
        return $this->getData('quote');
    }


}