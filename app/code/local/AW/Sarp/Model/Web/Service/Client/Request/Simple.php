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
class AW_Sarp_Model_Web_Service_Client_Request_Simple extends Varien_Object
{

    const POST_SPACER = "&";

    protected $_fields;
    protected $_defaultData = null;


    public function setOnceFields(array $fields)
    {
        $this->_fields = $fields;
    }

    public function reset()
    {
        $this->setData(array());
        return $this;
    }

    /**
     * Encode request as POST data
     * @return array
     */
    public function encodeRawPost()
    {
        $out = array();
        foreach ($this->getData() as $key => $value) {
            $out[] = urlencode($key) . "=" . urlencode($value);
        }
        return implode(self::POST_SPACER, $out);
    }

    /**
     * Attach data w/o rewriting whole data array
     * @param <type> $v1
     * @param <type> $v2
     * @return AW_Sarp_Model_Web_Service_Client_Request_Simple
     */
    public function attachData($v1, $v2 = null)
    {
        if (is_array($v1)) {
            foreach ($v1 as $k => $v) {
                $this->setData($k, $v);
            }
        } else {
            $this->setData($v1, $v2);
        }
        return $this;
    }

    /**
     * Adds bulk empty fields to the request(required by some web services)
     * @param array $data
     */
    public function setDefaultData(array $data)
    {
        $this->_defaultData = $data;
        return $this;
    }

    public function getDefaultData()
    {
        return $this->_defaultData;
    }

    /**
     * Return request data with merged default data
     * @return array
     */
    public function getRequestData()
    {
        if (is_array($this->getDefaultData())) {
            $data = array_merge($this->getDefaultData(), $this->getData());
        } else {
            $data = $this->getData();
        }
        return $data;
    }
}
