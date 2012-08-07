<?php
class Moogento_Pickpack_Block_Addresstextarea extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setStyle('height:6em;')
            ->setName($element->getName() . '[]');

        if ($element->getValue()) {
            $value = $element->getValue();
        } else {

$value = '{if company}{company},|{/if company}
{if name}{name},|{/if name}
{if street}{street},|{/if street}
{if city}{city},|{/if city}
{if region}{region},|{/if region}
{if postcode}{postcode}|{/if postcode}
{if country}{country}|{/if country}
{if telephone}|[ T: {telephone} ]{/if telephone}';
        }

        $from = $element->setValue(isset($value) ? $value : null)->getElementHtml();        
        return $from;//.'   '.Mage::helper('adminhtml')->__('items');
    }
}