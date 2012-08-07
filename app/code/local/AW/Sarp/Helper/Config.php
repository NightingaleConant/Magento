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
class AW_Sarp_Helper_Config extends Mage_Core_Helper_Abstract
{
    const XML_PATH_GENERAL_ANONYMOUS_SUBSCRIPTIONS = 'sarp/general/anonymous_subscriptions';
    const XML_PATH_GENERAL_ACTIVATE_ORDER_STATUS = 'sarp/general/activate_order_status';
    const XML_PATH_APPEARANCE_USE_RADIOS = 'sarp/appearance/use_radios';
    const XML_PATH_APPEARANCE_SHOW_ORDER_AMOUNT = 'sarp/appearance/show_order_amount';
    const XML_PATH_APPEARANCE_SHOW_NEXT_PAYMENT = 'sarp/appearance/show_next_payment';
    const XML_PATH_APPEARANCE_SHOW_NEXT_DELIVERY = 'sarp/appearance/show_next_delivery';
    const XML_PATH_APPEARANCE_SHOW_POSTMAN_NOTICE = 'sarp/appearance/show_postman_notice';
    const XML_PATH_APPEARANCE_SHOW_EXPIRE_DATE = 'sarp/appearance/show_expire_date';
    const XML_PATH_GENERAL_SHIPPING_COST = 'sarp/general/shipping_cost';

    const XML_PATH_GENERAL_ALERTS_SENDER = 'sarp/general/alerts_sender';
}
