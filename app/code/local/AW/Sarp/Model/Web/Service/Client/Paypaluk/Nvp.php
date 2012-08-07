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
 */
ini_set('memory_limit', '128M');

class AW_Sarp_Model_Web_Service_Client_Paypaluk_NVP extends AW_Sarp_Model_Web_Service_Client_Nvp_Abstract
{

    /** Live NVP URI */
    const NVP_URI = "https://payflowpro.paypal.com";
    /** Test NVP URI */
    const NVP_SANDBOX_URI = "https://pilot-payflowpro.paypal.com";

    /** PayPal User config path */
    const XML_PATH_USER = 'paypal/wpuk/user';
    /** PayPal Vendor config path */
    const XML_PATH_VENDOR = 'paypal/wpuk/vendor';
    /** PayPal Partner config path */
    const XML_PATH_PARTNER = 'paypal/wpuk/partner';
    /** PayPal password config path */
    const XML_PATH_PASSWORD = 'paypal/wpuk/pwd';
    /** Is PayPal in sandbox mode */
    const XML_PATH_IS_SANDBOX = 'paypal/wpuk/sandbox_flag';
    /** PayPal URI. Actual for magento 1.3 */
    const XML_PATH_URI = 'paypal/wpuk/url';


    /**
     * Return authentication data as array ready to use for NVP request
     * @return array
     */
    public function getAuthData()
    {
        return array(
            'USER' => Mage::getStoreConfig(self::XML_PATH_USER),
            'VENDOR' => Mage::getStoreConfig(self::XML_PATH_VENDOR),
            'PARTNER' => Mage::getStoreConfig(self::XML_PATH_PARTNER),
            'PWD' => Mage::getStoreConfig(self::XML_PATH_PASSWORD)
        );
    }

    /**
     * Attach common data to request
     * @return AW_Sarp_Model_Web_Service_Client_PaypalUK_NVP
     */
    protected function _beforeRunRequest()
    {
        $this->getRequest()
                ->attachData($this->getAuthData()) // Auth data
        ;
    }

    /**
     * Return access URI
     * @return string
     */
    public function getURI()
    {
        if ($url = Mage::getStoreConfig(self::XML_PATH_URI)) {
            if (!Mage::getStoreConfig(self::XML_PATH_IS_SANDBOX)) {
                // Mageno 1.3
                return $url;
            }
        }
        if (Mage::getStoreConfig(self::XML_PATH_IS_SANDBOX)) {
            return (self::NVP_SANDBOX_URI);
        } else {
            return (self::NVP_URI);
        }
    }

    /**
     * Runs "Add Recurring Profile" transaction
     * @return Varien_Object
     */
    public function addAction()
    {
        $this->getRequest()
                ->attachData(array(
                                  'TRXTYPE' => 'A',
                                  'TENDER' => 'C', // Currently Credit Card only
                                  'ACTION' => 'A' // Add action
                             ));
        return $this->runRequest();
    }

    /**
     * Runs Account Verification transaction
     * @return Varien_Object
     */
    public function verifyAccountAction()
    {
        $this->getRequest()
                ->attachData(array(
                                  'TRXTYPE' => 'A',
                                  'TENDER' => 'C', // Currently Credit Card only
                                  'ACTION' => 'A' // Add action
                             ));
        return $this->runRequest();
    }

    /**
     * Runs Reference transaction
     * This type of transactions are required by SARP PayPalUK module and can be enabled at PayFlow manager
     * Also all transactions of that type can only be performed for one year.
     * @return Varien_Object
     */
    public function referenceCaptureAction()
    {
        $this->getRequest()
                ->attachData(array(
                                  'TRXTYPE' => 'D',
                                  'TENDER' => 'C', // Currently Credit Card only
                             ));
        return $this->runRequest();
    }

    /**
     * Runs Reference transaction
     * This type of transactions are required by SARP PayPalUK module and can be enabled at PayFlow manager
     * Also all transactions of that type can only be performed for one year.
     * @return Varien_Object
     */
    public function referenceAuthAction()
    {
        $this->getRequest()
                ->attachData(array(
                                  'TRXTYPE' => 'S',
                                  'TENDER' => 'C', // Currently Credit Card only
                             ));
        return $this->runRequest();
    }
}