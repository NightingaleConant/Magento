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
class AW_Sarp_Model_Product_Type_Configurable_Subscription extends Mage_Catalog_Model_Product_Type_Configurable
{
    protected $_canConfigure = true;

    const PRODUCT_TYPE_CONFIGURABLE = 'subscription_configurable';

    /**
     * Prepares product for cart according to buyRequest.
     *
     * @param Varien_Object $buyRequest
     * @param object        $product [optional]
     * @return
     */
    public function prepareForCart(Varien_Object $buyRequest, $product = null)
    {
        Mage::getModel('sarp/product_type_default')->checkPeriod($product, $buyRequest);

        $Period = Mage::getModel('sarp/period');

        /* We should add custom options that doesnt exist */
        if ($buyRequest->getAwSarpSubscriptionType()) {
            if ($Period->load($buyRequest->getAwSarpSubscriptionType())->getId()) {
                $product->addCustomOption('aw_sarp_subscription_type', $Period->getId());
            }
        }

        if ($buyRequest->getAwSarpSubscriptionStart() && $Period->getId()) {

            $date = new Zend_Date($buyRequest->getAwSarpSubscriptionStart(), Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
            // Check date

            // Never check if start date

            //$performDateCompare = !!Mage::getSingleton('customer/session')->getCustomer()->getId();
            $performDateCompare = !AW_Sarp_Model_Cron::$isCronSession;

            $today = new Zend_Date;
            if (!$this->isVirtual($product)) {
                $today->addDayOfYear($Period->getPaymentOffset());
            }


            if ($performDateCompare
                && ($date->compare($today, Zend_Date::DATE_SHORT) < 0
                    || !$Period->isAllowedDate($date, $product))
            ) {
                throw new Mage_Core_Exception(Mage::helper('sarp')->__("Selected date is not valid for specified period"));
            }
        }
        else
        {
            $date = Mage::app()->getLocale()->date();
        }
        $product->addCustomOption('aw_sarp_subscription_start', $date->toString('Y-MM-dd'));

        if ($attributes = $buyRequest->getSuperAttribute()) {
            $result = Mage_Catalog_Model_Product_Type_Abstract::prepareForCart($buyRequest, $product);
            if (is_array($result)) {
                $product = $this->getProduct($product);
                /**
                 * $attributes = array($attributeId=>$attributeValue)
                 */
                if ($subProduct = $this->getProductByAttributes($attributes, $product)) {
                    $product->addCustomOption('attributes', serialize($attributes));
                    $product->addCustomOption('product_qty_' . $subProduct->getId(), 1, $subProduct);
                    $product->addCustomOption('simple_product', $subProduct->getId(), $subProduct);

                    $_result = $subProduct->getTypeInstance(true)->prepareForCart($buyRequest, $subProduct);
                    if (is_string($_result) && !is_array($_result)) {
                        return $_result;
                    }

                    if (!isset($_result[0])) {
                        return Mage::helper('checkout')->__('Can not add item to shopping cart');
                    }

                    /**
                     * Adding parent product custom options to child product
                     * to be sure that it will be unique as its parent
                     */
                    if ($optionIds = $product->getCustomOption('option_ids')) {
                        $optionIds = explode(',', $optionIds->getValue());
                        foreach ($optionIds as $optionId) {
                            if ($option = $product->getCustomOption('option_' . $optionId)) {
                                $_result[0]->addCustomOption('option_' . $optionId, $option->getValue());
                            }
                        }
                    }

                    if ($buyRequest->getAwSarpSubscriptionType()) {
                        if ($Period->getId()) {
                            $_result[0]->addCustomOption('aw_sarp_subscription_start', $date->toString('Y-MM-dd'));
                            $_result[0]->addCustomOption('aw_sarp_subscription_type', $Period->getId());
                        }
                    }

                    $_result[0]->setParentProductId($product->getId())
                    // add custom option to simple product for protection of process when we add simple product separately
                            ->addCustomOption('parent_product_id', $product->getId())
                            ->setCartQty(1);

                    $result[] = $_result[0];

                    return $result;
                }
            }
        }
        return $this->getSpecifyOptionMessage();
    }

    public function prepareForCartAdvanced(Varien_Object $buyRequest, $product = null, $processMode = null)
    {
        Mage::getModel('sarp/product_type_default')->checkPeriod($product, $buyRequest);

        $Period = Mage::getModel('sarp/period');

        /* We should add custom options that doesnt exist */
        if ($buyRequest->getAwSarpSubscriptionType()) {
            if ($Period->load($buyRequest->getAwSarpSubscriptionType())->getId()) {
                $product->addCustomOption('aw_sarp_subscription_type', $Period->getId());
            }
        }

        $options = $buyRequest->getOptions();
        if (isset($options['aw_sarp_subscription_start']) && is_array($options['aw_sarp_subscription_start'])) {
            $subscriptionStart = $options['aw_sarp_subscription_start'];
            $date = new Zend_Date();
            $date
                    ->setMinute(0)
                    ->setHour(0)
                    ->setSecond(0)
                    ->setDay($subscriptionStart['day'])
                    ->setMonth($subscriptionStart['month'])
                    ->setYear($subscriptionStart['year']);
            $buyRequest->setAwSarpSubscriptionStart($date->toString(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)));
        }

        if ($buyRequest->getAwSarpSubscriptionStart() && $Period->getId()) {

            $date = new Zend_Date($buyRequest->getAwSarpSubscriptionStart(), Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
            // Check date

            // Never check if start date

            //$performDateCompare = !!Mage::getSingleton('customer/session')->getCustomer()->getId();
            $performDateCompare = !AW_Sarp_Model_Cron::$isCronSession;

            $today = new Zend_Date;
            if (!$this->isVirtual($product)) {
                $today->addDayOfYear($Period->getPaymentOffset());
            }


            if ($performDateCompare
                && ($date->compare($today, Zend_Date::DATE_SHORT) < 0
                    || !$Period->isAllowedDate($date, $product))
            ) {
                throw new Mage_Core_Exception(Mage::helper('sarp')->__("Selected date is not valid for specified period"));
            }
        }
        else
        {
            $date = Mage::app()->getLocale()->date();
        }
        $product->addCustomOption('aw_sarp_subscription_start', $date->toString('Y-MM-dd'));

        if ($attributes = $buyRequest->getSuperAttribute()) {
            $result = Mage_Catalog_Model_Product_Type_Abstract::prepareForCartAdvanced($buyRequest, $product);
            if (is_array($result)) {
                $product = $this->getProduct($product);
                /**
                 * $attributes = array($attributeId=>$attributeValue)
                 */
                if ($subProduct = $this->getProductByAttributes($attributes, $product)) {
                    $product->addCustomOption('attributes', serialize($attributes));
                    $product->addCustomOption('product_qty_' . $subProduct->getId(), 1, $subProduct);
                    $product->addCustomOption('simple_product', $subProduct->getId(), $subProduct);

                    $_result = $subProduct->getTypeInstance(true)->prepareForCartAdvanced($buyRequest, $subProduct);
                    if (is_string($_result) && !is_array($_result)) {
                        return $_result;
                    }

                    if (!isset($_result[0])) {
                        return Mage::helper('checkout')->__('Can not add item to shopping cart');
                    }

                    /**
                     * Adding parent product custom options to child product
                     * to be sure that it will be unique as its parent
                     */
                    if ($optionIds = $product->getCustomOption('option_ids')) {
                        $optionIds = explode(',', $optionIds->getValue());
                        foreach ($optionIds as $optionId) {
                            if ($option = $product->getCustomOption('option_' . $optionId)) {
                                $_result[0]->addCustomOption('option_' . $optionId, $option->getValue());
                            }
                        }
                    }

                    if ($buyRequest->getAwSarpSubscriptionType()) {
                        if ($Period->getId()) {
                            $_result[0]->addCustomOption('aw_sarp_subscription_start', $date->toString('Y-MM-dd'));
                            $_result[0]->addCustomOption('aw_sarp_subscription_type', $Period->getId());
                        }
                    }

                    $_result[0]->setParentProductId($product->getId())
                    // add custom option to simple product for protection of process when we add simple product separately
                            ->addCustomOption('parent_product_id', $product->getId())
                            ->setCartQty(1);

                    $result[] = $_result[0];

                    return $result;
                }
            }
        }
        return $this->getSpecifyOptionMessage();
    }

    /**
     * @param $product
     * @return bool
     */
    public function hasRequiredOptions($product = null)
	{
		return true;
	}

    /**
     * Returns true if product has subscriptions options
     * @return bool
     */
    public function hasSubscriptionOptions()
    {
        $opts = @preg_split('/[,]/', $this->getProduct()->getAwSarpPeriod(), -1, PREG_SPLIT_NO_EMPTY);
        if (!sizeof($opts) || ($opts[0] == -1 && sizeof($opts) == 1)) {
            return false;
        }
        return true;
    }

    /**
     * Returns true if product requires subscription options
     * @return bool
     */
    public function requiresSubscriptionOptions($product = null)
    {
        if (is_null($product)) $product = $this->getProduct();
        if (!$product->getAwSarpEnabled()) return false;
        $opts = @preg_split('/[,]/', $product->getAwSarpPeriod(), -1, PREG_SPLIT_NO_EMPTY);
        if (!sizeof($opts) || (array_search(-1, $opts) !== false)) {
            return false;
        }
        return true;
    }

    /**
     * Returns default period id. If none, returns -1
     * @return int
     */
    public function getDefaultSubscriptionPeriodId()
    {
        $opts = @preg_split('/[,]/', $this->getProduct()->getAwSarpPeriod(), -1, PREG_SPLIT_NO_EMPTY);
        return isset($opts[0]) ? $opts[0] : AW_Sarp_Model_Period::PERIOD_TYPE_NONE;
    }

    public function processBuyRequest($product, $buyRequest)
    {
        $toReturn = parent::processBuyRequest($product, $buyRequest);
        if ($buyRequest->getData('aw_sarp_subscription_start')) $toReturn['aw_sarp_subscription_start'] = $buyRequest->getData('aw_sarp_subscription_start');
        if ($buyRequest->getData('aw_sarp_subscription_type')) $toReturn['aw_sarp_subscription_type'] = $buyRequest->getData('aw_sarp_subscription_type');
        return $toReturn;
    }

    public function beforeSave($product = null)
    {
        parent::beforeSave($product);
        if ($product->getAwSarpEnabled() && $this->getProduct($product)->getAwSarpSubscriptionPrice() == '')
            $this->getProduct($product)->setAwSarpSubscriptionPrice($product->getData('price'));
    }

}