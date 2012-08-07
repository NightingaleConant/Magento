<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function fields($step)
    {
        return Mage::app()->getLayout()->createBlock('amorderattr/fields')->setStep($step)->toHtml();
    }
    
    public function clearCache()
    {
        $cacheDir = Mage::getBaseDir('var') . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
        $this->_clearDir($cacheDir);
        Mage::app()->cleanCache();
        Mage::getConfig()->reinit();
    }
    
    protected function _clearDir($dir = '')
    {
        if($dir) 
        {
            if (is_dir($dir)) 
            {
                if ($handle = @opendir($dir)) 
                {
                    while (($file = readdir($handle)) !== false) 
                    {
                        if ($file != "." && $file != "..") 
                        {
                            $fullpath = $dir . '/' . $file;
                            if (is_dir($fullpath)) 
                            {
                                $this->_clearDir($fullpath);
                                @rmdir($fullpath);
                            }
                            else 
                            {
                                @unlink($fullpath);
                            }
                        }
                    }
                    closedir($handle);
                }
            }
        }
    }
}