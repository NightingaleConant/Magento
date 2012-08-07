<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Customer_Form_Login extends Mage_Customer_Block_Form_Login
{
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        if (Mage::getStoreConfig('amcustomerattr/login/login_field') && Mage::getStoreConfig('amcustomerattr/login/modify_title'))
        {
            $attributesHash = Mage::helper('amcustomerattr')->getAttributesHash();
            if (isset($attributesHash[Mage::getStoreConfig('amcustomerattr/login/login_field')]))
            {
                if (!Mage::getStoreConfig('amcustomerattr/login/disable_email'))
                {
                    $replaceWith = $this->__('Email Address') . '/' . $attributesHash[Mage::getStoreConfig('amcustomerattr/login/login_field')];
                } else 
                {
                    $replaceWith = $attributesHash[Mage::getStoreConfig('amcustomerattr/login/login_field')];
                }
                $html = str_replace($this->__('Email Address'), $replaceWith, $html);
                $html = str_replace('validate-email', '', $html);
            }
        }
        return $html;
    }
}