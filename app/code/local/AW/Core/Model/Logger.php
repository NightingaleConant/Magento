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

class AW_Core_Model_Logger extends Mage_Core_Model_Abstract
{

    /** Notice */
    const LOG_SEVERITY_NOTICE = 1;
    /** Strict notice */
    const LOG_SEVERITY_STRICT_NOTICE = 2;
    /** Warning */
    const LOG_SEVERITY_WARNING = 4;
    /** Error */
    const LOG_SEVERITY_ERROR = 8;
    /** Fatal */
    const LOG_SEVERITY_FATAL = 8;

    protected function _construct()
    {
        $this->_init('awcore/logger');
    }

    /**
     * Prepares log entry for saving
     * @return AW_Core_Model_Log
     */
    public function _beforeSave()
    {
        if (!$this->getSeverity()) {
            $this->setSeverity(self::LOG_SEVERITY_NOTICE);
        }
        if (!$this->getDate()) {
            $this->setDate(now());
        }
        return parent::_beforeSave();
    }

    /**
     * Exorcise wrapper
     * @return
     */
    public function exorcise()
    {
        return Mage::helper('awcore/logger')->exorcise();
    }
}