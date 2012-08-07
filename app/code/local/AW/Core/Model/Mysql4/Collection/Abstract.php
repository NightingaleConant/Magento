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
class AW_Core_Model_Mysql4_Collection_Abstract extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    /**
     * Logs entry wrapper
     * @param object $message
     * @param object $severity [optional]
     * @return
     */
    public function log($message, $severity = null)
    {
        Mage::helper('awcore/logger')->log($this, $message, $severity);
    }
}