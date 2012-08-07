<?php
class NGC_Installment_Block_Adminhtml_Type_Edit_Tab_Definition
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    const INSTALLMENT_DEFINITION_VALUE_TYPE_FIXED_LABEL = 'Fixed';
    const INSTALLMENT_DEFINITION_VALUE_TYPE_FIXED_VALUE = 1;
    const INSTALLMENT_DEFINITION_VALUE_TYPE_PERCENTAGE_LABEL = '%';
    const INSTALLMENT_DEFINITION_VALUE_TYPE_PERCENTAGE_VALUE = 2;


    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('installment/type/edit/definition.phtml');
    }

    /**
     * Preparing layout, adding buttons
     *
     * @return Mage_Eav_Block_Adminhtml_Attribute_Edit_Options_Abstract
     */
    protected function _prepareLayout()
    {
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label' => Mage::helper('eav')->__('Delete'),
                'class' => 'delete delete-option'
            )));

        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label' => Mage::helper('installment')->__('Add Transaction'),
                'class' => 'add',
                'id'    => 'add_new_option_button'
            )));
        return parent::_prepareLayout();
    }

    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getSelectOptions() {
        return array (
            self::INSTALLMENT_DEFINITION_VALUE_TYPE_FIXED_LABEL => self::INSTALLMENT_DEFINITION_VALUE_TYPE_FIXED_VALUE,
            self::INSTALLMENT_DEFINITION_VALUE_TYPE_PERCENTAGE_LABEL => self::INSTALLMENT_DEFINITION_VALUE_TYPE_PERCENTAGE_VALUE
        );
    }

    public function getOptionValues()
    {
        $id         = Mage::registry('current_installment_type')->getInstallmentTypeId();
        $definition = Mage::getModel('installment/definition')->getCollection();
        $values     = array();

        if ($id && $definition) {
            $definition->addFieldToFilter('installment_type_id', $id);

            foreach ($definition as $oDefinition) {
                $value = array(
                    'id'        => $oDefinition->getId(),
                    'sequence'  => $oDefinition->getInstallmentDefinitionSequenceNumber(),
                    'type'      => $oDefinition->getInstallmentDefinitionRecordType(),
                    'value'     => $oDefinition->getInstallmentDefinitionValueForType(),
                    'days'      => $oDefinition->getInstallmentDefinitionDaysToPayment()
                );

                $values[] = new Varien_Object($value);
            }

        }

        return $values;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('installment')->__('Sequence Definition');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('installment')->__('Sequence Definition');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/installment/type/' . $action);
    }
}
