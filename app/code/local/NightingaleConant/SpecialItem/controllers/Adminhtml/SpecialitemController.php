<?php

class NightingaleConant_SpecialItem_Adminhtml_SpecialitemController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('sales/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('SpecialItem Manager'), Mage::helper('adminhtml')->__('SpecialItem Manager'));

        return $this;
    }

    public function indexAction() { 
        $this->_initAction()
                ->renderLayout();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('specialitem/specialitem')->load($id);

        if ($model->getSpecialitemId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
                $model->setUpdatedAt(now());
            }

            Mage::register('specialitem_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('specialitem/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Author Manager'), Mage::helper('adminhtml')->__('Author Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Author News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('specialitem/adminhtml_specialitem_edit'))
                    ->_addLeft($this->getLayout()->createBlock('specialitem/adminhtml_specialitem_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('specialitem')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {

            $model = Mage::getModel('specialitem/specialitem');
            $currUser = Mage::getSingleton('admin/session')->getUser()->getId();
            $data['created_at'] = now();
            $data['created_by'] = $currUser;
            
            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));


            foreach ((array) $_FILES as $k => $v) { 
                if ($v['name'] != "") {
                    $uploader = new Varien_File_Uploader($k);

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);

                    $path = Mage::getBaseDir('media') . DS . 'specialitem';
                    if(!is_dir($path))
                        mkdir($path, 0777);
                    
                    $p = pathinfo($_FILES[$k]['name']);
                    $file_name = $p['filename'] . '_' . time() . '.' . $p['extension'];

                    $uploader->save($path, $file_name);

                    $data[$k] = 'specialitem/' . $file_name;

                    $model->setImage($data[$k]);
                } else {
                    if ($data[$k]['value'] != "") {                        
                        $model->setImage($data[$k]['value']);
                    }
                }
            }


            try {
                if ($model->getCreatedAt() == NULL) {
                    $model->setCreatedAt(now());
                }

                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('specialitem')->__('Author information successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('specialitem')->__('Unable to find author to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('specialitem/specialitem');

                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Author successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $specialitemIds = $this->getRequest()->getParam('specialitem');
        if (!is_array($specialitemIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select author(s)'));
        } else {
            try {
                foreach ($specialitemIds as $authorId) {
                    $author = Mage::getModel('specialitem/specialitem')->load($authorId);
                    $author->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d author(s) were successfully deleted', count($specialitemIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction() {
        $authorIds = $this->getRequest()->getParam('specialitem');
        $fileName = 'specialitem.csv';
        $content = $this->getLayout()->createBlock('specialitem/adminhtml_specialitem_grid')
                ->getCsv($authorIds);

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'specialitem.xml';
        $content = $this->getLayout()->createBlock('specialitem/adminhtml_specialitem_grid')
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