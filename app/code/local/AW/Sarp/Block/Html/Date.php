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
 */class AW_Sarp_Block_Html_Date extends Mage_Core_Block_Html_Date
{

    public function getExcludedWeekDays()
    {
        return $this->getPeriod()->getExcludedWeekdays();
    }

    public function getPeriodsExcludedData()
    {
        $out = array('excluded_weekdays' => array(), 'first_allowed_day' => array());

        foreach (Mage::getModel('sarp/period')->getCollection() as $Period) {
            $zDate = new Zend_Date($this->formatDate($Period->getNearestAvailableDay(), Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), null, Mage::app()->getLocale()->getLocaleCode());
            $date = $zDate->toString(preg_replace(array('/M/', '/d/'), array('MM', 'dd'), Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)));

            $out['excluded_weekdays'][$Period->getId()] = $Period->getExcludedWeekdays();
            $out['first_allowed_day'][$Period->getId()] = $date;

        }
        return $out;
    }

    protected function _toHtml()
    {
        $periodData = ($this->getPeriodsExcludedData());


        $html = '<input type="text" name="' . $this->getName() . '" id="' . $this->getId() . '" ';
        $html .= 'value="' . $this->getValue() . '" class="' . $this->getClass() . '" style="width:100px" ' . $this->getExtraParams() . '/> ';

        $html .= '<img src="' . $this->getImage() . '" alt="" class="v-middle" ';
        $html .= 'title="' . $this->helper('core')->__('Select Date') . '" id="' . $this->getId() . '_trig" />';

        $html .=

                '<script type="text/javascript">
            AwSarpDisabledWeekdays = ' . (Zend_Json::encode(@$periodData['excluded_weekdays'])) . ';
            AwSarpFirstAvailDays = ' . (Zend_Json::encode(@$periodData['first_allowed_day'])) . ';

            Calendar.setup({
                inputField  : "' . $this->getId() . '",
                ifFormat    : "' . $this->getFormat() . '",
                button      : "' . $this->getId() . '_trig",
                align       : "Bl",
                disableFunc : function(){
                    var els = document.getElementsByName("aw_sarp_subscription_type");
                    for(var n=els.length-1;n>=0;n--){
                        if($F(els[n])){
                            var periodId = $F(els[n]);
                            break;
                        }
                    }
                    if(!periodId){
                        throw("Cannt detect subscription type")
                    }
                    if(periodId == ' . AW_Sarp_Model_Period::PERIOD_TYPE_NONE . '){
                        return true;
                    }



                    var D = new Date();
                    minDate = (Date.parseDate(AwSarpFirstAvailDays[periodId], "' . $this->getFormat() . '"))
                    if(D.getTime() < minDate.getTime()){
                        D = minDate
                    }

                    var seedToday = D.getFullYear()*10000 + (D.getMonth()+1)*100 + D.getDate();
                    var seedArgument =   arguments[0].getFullYear()*10000 +  (arguments[0].getMonth()+1)*100 +  arguments[0].getDate();



                    var wd = arguments[0].getDay();
                    for(var i=AwSarpDisabledWeekdays[periodId].length-1; i>=0; i--){
                        if(wd == AwSarpDisabledWeekdays[periodId][i] || seedToday > seedArgument){
                            if(seedToday == seedArgument){
                                $("' . $this->getId() . '").value = AwSarpFirstAvailDays[periodId];

                            }
                            return true;
                        }
                    }
                    if( i< 0 && seedToday > seedArgument){
                        return true
                    }
                },
                singleClick : true
            });
        </script>';


        return $html;
    }

}
