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
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */


class AW_Core_Block_Adminhtml_System_Config_Form_Fieldset extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    /**
     * Render fieldset html
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);

        foreach ($element->getElements() as $field) {
            $html .= $field->toHtml();
        }
        $html .= "<tr>
            <td class=\"label\"></td>
            <td class=\"value\">
            <button class=\"scalable\" onclick=\"window.location='" . Mage::getSingleton('adminhtml/url')->getUrl('awcore_admin/viewlog/index') . "'\" type=\"button\">
                <span>View log</span>
            </button
            </td>
         </tr>
         ";
        $html .= $this->_getFooterHtml($element);

        return $html;
    }
}