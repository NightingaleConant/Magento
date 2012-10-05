<?php

class NightingaleConant_Royalties_Adminhtml_RoyaltiesController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('royalties/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Royalties Manager'), Mage::helper('adminhtml')->__('Royalties Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('royalties/rate')->load($id);

        if ($model->getRoyaltyRateId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
                $model->setUpdatedAt(now());
            }

            Mage::register('royalties_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('royalties/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Royalty Rate Manager'), Mage::helper('adminhtml')->__('Royalty Rate Manager'));
            //$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Author News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('royalties/adminhtml_royalties_edit'))
                    ->_addLeft($this->getLayout()->createBlock('royalties/adminhtml_royalties_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('royalties')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {

            $model = Mage::getModel('royalties/rate');
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

                    $path = Mage::getBaseDir('media') . DS . 'royalties';
                    if(!is_dir($path))
                        mkdir($path, 0777);
                    
                    $p = pathinfo($_FILES[$k]['name']);
                    $file_name = $p['filename'] . '_' . time() . '.' . $p['extension'];

                    $uploader->save($path, $file_name);

                    $data[$k] = 'royalties/' . $file_name;

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
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('royalties')->__('Royalty Rate information successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('royalties')->__('Unable to find royalty rate to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('royalties/rate');

                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Royalty Rate successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $royaltiesIds = $this->getRequest()->getParam('royalties');
        if (!is_array($royaltiesIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select royalty rate(s)'));
        } else {
            try {
                foreach ($royaltiesIds as $rateId) {
                    $rate = Mage::getModel('royalties/rate')->load($rateId);
                    $rate->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d royalty rate(s) were successfully deleted', count($royaltiesIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction() {
        $rateIds = $this->getRequest()->getParam('royalties');
        $fileName = 'royalties.csv';
        $content = $this->getLayout()->createBlock('royalties/adminhtml_royalties_grid')
                ->getCsv($rateIds);

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'royalties.xml';
        $content = $this->getLayout()->createBlock('royalties/adminhtml_royalties_grid')
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
