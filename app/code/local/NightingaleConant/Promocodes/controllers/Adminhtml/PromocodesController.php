<?php

class NightingaleConant_Promocodes_Adminhtml_PromocodesController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('promocodes/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Promocodes Manager'), Mage::helper('adminhtml')->__('Promocodes Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('promocodes/promocodes')->load($id);

        if ($model->getCkcEntityId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('promocodes_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('promocodes/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Promocodes Manager'), Mage::helper('adminhtml')->__('Promocodes Manager'));
            
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('promocodes/adminhtml_promocodes_edit'))
                    ->_addLeft($this->getLayout()->createBlock('promocodes/adminhtml_promocodes_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('promocodes')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {

            $model = Mage::getModel('promocodes/promocodes');
            
            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));


            try {

                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('promocodes')->__('Promocode successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('promocodes')->__('Unable to find promocodes to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('promocodes/promocodes');

                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Promocode successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function exportCsvAction() {
        $promocodesIds = $this->getRequest()->getParam('promocodes');
        $fileName = 'promocodes.csv';
        $content = $this->getLayout()->createBlock('promocodes/adminhtml_promocodes_grid')
                ->getCsv($promocodesIds);

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'promocodes.xml';
        $content = $this->getLayout()->createBlock('promocodes/adminhtml_promocodes_grid')
                ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

}
