<?php
/**
 * Data helper
 */
class IDP_AuthorizeCIM_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getInstallmentTypes()
    {
        $types = Mage::getModel('installment/type')->getCollection();
        $types->addFieldToFilter('installment_type_plan_active', 1)
              ->addOrder('installment_type_id', Varien_Data_Collection_Db::SORT_ORDER_ASC);

        $default = 0;
        $aData = array(
            array('value'   => '0',
                  'key'     => 'None',
                  'default' => false));

        foreach ($types as $type) {
            $aData[] = array(
                    'value'     => $type->getId(),
                    'key'       => $type->getInstallmentTypeId() .' ('. $type->getInstallmentTypeDescription() .')',
                    'default'   => $type->getInstallmentTypePlanDefault()
            );

            $default = ($type->getInstallmentTypePlanDefault())
                        ? $type->getInstallmentTypePlanDefault()
                        : $default;

        }

        //  If no default installment type set then make 'None' default
        if (!$default) {
            $aData[0]['default'] = true;
        }

        return $aData;
    }
} 
