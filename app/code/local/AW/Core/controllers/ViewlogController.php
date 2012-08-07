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

class AW_Core_ViewlogController extends Mage_Adminhtml_Controller_action
{
    public function indexAction()
    {
        $this
                ->loadLayout()
                ->_addContent($this->getLayout()->createBlock('awcore/adminhtml_log'))
                ->renderLayout();
    }

    /**
     * Clears all records in log table
     * @return AW_Core_ViewlogController
     */
    public function clearAction()
    {
        try {
            Mage::getResourceSingleton('awcore/logger')->truncateAll();
            Mage::getSingleton('adminhtml/session')->addSuccess("Log successfully cleared");
            $this->_redirect('*/*');

        } catch (Mage_Core_Exception $E) {
            Mage::getSingleton('adminhtml/session')->addError($E->getMessage());
            $this->_redirectReferer();
        }
        return $this;
    }

}