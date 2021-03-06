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
class AW_Core_Helper_Extension extends Varien_Object
{

    /**
     * Detect if extension installed
     * @param string $code
     * @return bool
     */
    public function isExtensionInstalled($code)
    {
        $exts = $this->getInstalledExtensions();
        return (isset($exts[$code]));
    }

    /**
     * Detect if extension is installed and active
     * @param string $code
     * @return bool
     */
    public function isExtensionActive($code)
    {
        if ($this->isExtensionInstalled($code)) {
            $exts = $this->getInstalledExtensions();
            return (bool)$exts[$code]['active'];
        }
    }

    /**
     * Return all installed extensions
     * This way is based on
     * @return array
     */
    public function getInstalledExtensions()
    {
        if (!$this->getData('installed_extensions')) {
            $exts = array();
            $modules = ((array)Mage::getConfig()->getNode('modules')->children());
            foreach ($modules as $k => $Module) {
                $exts[$k] = (array)$Module;
            }
            $this->setData('installed_extensions', $exts);
        }
        return $this->getData('installed_extensions');
    }
}