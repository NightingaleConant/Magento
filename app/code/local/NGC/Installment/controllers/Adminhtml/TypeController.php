<?php

class NGC_Installment_Adminhtml_TypeController extends Mage_Adminhtml_Controller_Action
{
    protected function _initType()
    {
        $this->_title($this->__('Installments'))->_title($this->__('Installment Types'));

        Mage::register('current_installment_type', Mage::getModel('installment/type'));
        $id = $this->getRequest()->getParam('id');
        if (!is_null($id)) {
            Mage::registry('current_installment_type')->load($id);
        }

    }

    public function indexAction()
    {
        $this->loadLayout()
             ->_addContent($this->getLayout()->createBlock('installment/adminhtml_type'))
             ->renderLayout();
    }

    /**
     * Edit or create installment type.
     */
    public function newAction()
    {
        $this->_initType();
        $this->loadLayout();
        $this->_setActiveMenu('installment');
        $this->_addBreadcrumb(Mage::helper('installment')->__('Installments'), Mage::helper('installment')->__('Installments'));
        $this->_addBreadcrumb(Mage::helper('installment')->__('Installment Types'), Mage::helper('installment')->__('Installment Types'), $this->getUrl('*/installment_group'));

        $currentType = Mage::registry('current_installment_type');

        if (!is_null($currentType->getId())) {
            $this->_addBreadcrumb(Mage::helper('installment')->__('Edit Type'), Mage::helper('installment')->__('Edit Installment Type'));
        } else {
            $this->_addBreadcrumb(Mage::helper('installment')->__('New Type'), Mage::helper('installment')->__('New Installment Type'));
        }

        $this->_title($currentType->getId() ? $currentType->getCode() : $this->__('New Type'));

        $block = $this->getLayout()->createBlock('installment/adminhtml_type_edit', 'type_edit');
        $this->_addContent($block->setEditMode((bool)Mage::registry('current_installment_type')->getId()))
            ->_addLeft($this->getLayout()->createBlock('installment/adminhtml_type_edit_tabs'));

        $this->renderLayout();
    }

    /**
     * Edit installment type action. Forward to new action.
     */
    public function editAction()
    {
        $this->_forward('new');
    }

    /**
     * Create or save installment type.
     */
    public function saveAction()
    {
        $type = Mage::getModel('installment/type');
        $id = $this->getRequest()->getParam('id');
        if (!is_null($id)) {
            $type->load($id);
        }

        if ($this->getRequest()->getParam('plan_default')) {
            $default = Mage::getModel('installment/type')->getCollection();
            $default->addFieldToFilter('installment_type_plan_default', 1);

            if ($default->getData()) {
                Mage::getSingleton('adminhtml/session')->addError('An existing plan is already set as default');
                Mage::getSingleton('adminhtml/session')->setInstallmentTypeData($type->getData());
                return $this->getResponse()->setRedirect($this->getUrl('*/type/edit', array('id' => $id)));
            }
        }

        try {
            $type->setInstallmentTypeId($this->getRequest()->getParam('type_id'))
                ->setInstallmentTypeDescription($this->getRequest()->getParam('description'))
                ->setInstallmentTypePlanActive($this->getRequest()->getParam('plan_active'))
                ->setInstallmentTypeAutoAdjust($this->getRequest()->getParam('auto_adjust'))
                ->setInstallmentTypePlanDefault($this->getRequest()->getParam('plan_default'))
                ->save();

            $option = $this->getRequest()->getParam('option');

            if (!is_null($option)) {
                if (!is_null($id)) {
                    $x = 1;
                    foreach ($option as $idx => $aOption) {
                        if (strstr($idx, 'option')) {
                            $definition = Mage::getModel('installment/definition')
                                ->setInstallmentTypeId($type->getInstallmentTypeId());
                        } else {
                            $definition = Mage::getModel('installment/definition')->load($idx);
                        }

                        if ($aOption['delete']) {
                            $definition->delete();
                            continue;
                        }

                        $definition->setInstallmentDefinitionSequenceNumber($x)
                                    ->setInstallmentDefinitionRecordType($aOption['type'])
                                    ->setInstallmentDefinitionValueForType($aOption['value'])
                                    ->setInstallmentDefinitionDaysToPayment($aOption['days'])
                                    ->save();
                        $x++;
                    }
                } else {
                    foreach ($option as $aOption) {
                        $definition = Mage::getModel('installment/definition');
                        $definition->setInstallmentTypeId($type->getInstallmentTypeId())
                                    ->setInstallmentDefinitionSequenceNumber($aOption['sequence'])
                                    ->setInstallmentDefinitionRecordType($aOption['type'])
                                    ->setInstallmentDefinitionValueForType($aOption['value'])
                                    ->setInstallmentDefinitionDaysToPayment($aOption['days'])
                                    ->save();
                    }

                }

            }
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('installment')->__('The installment type has been saved.'));
            $this->getResponse()->setRedirect($this->getUrl('*/type'));
            return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setInstallmentTypeData($type->getData());
            $this->getResponse()->setRedirect($this->getUrl('*/type/edit', array('id' => $id)));
            return;
        }

    }

    /**
     * Delete customer group action
     */
    public function deleteAction()
    {
        $installment = Mage::getModel('installment/type');
        if ($id = (int)$this->getRequest()->getParam('id')) {
            try {
                $installment->load($id);

                $sequences = Mage::getModel('installment/definition')->getCollection()->getItemsByColumnValue('installment_type_id', $installment->getInstallmentTypeId());
                foreach($sequences as $oSequence) {
                    $oSequence->delete();
                }

                $installment->load($id);
                $installment->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('installment')->__('The installment type has been deleted.'));
                $this->getResponse()->setRedirect($this->getUrl('*/type'));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->getResponse()->setRedirect($this->getUrl('*/type/edit', array('id' => $id)));
                return;
            }
        }

        $this->_redirect('*/type');
    }
}