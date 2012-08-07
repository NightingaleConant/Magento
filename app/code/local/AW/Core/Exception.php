<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Core
 * @version    1.0
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */

class AW_Core_Exception extends Mage_Core_Exception
{
    /**
     * @deprecated
     * @static
     * @var mixed
     */
    protected static $_log;

    public function __construct($message, $details = '', $level = 1, $module = '')
    {
        Mage::helper('awcore/logger')->log($this, $message, AW_Core_Model_Logger::LOG_SEVERITY_WARNING, $details, $this->getLine());
        return parent::__construct($message);
    }
}