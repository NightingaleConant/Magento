<?php
class Exinent_Invoicestatus_Adminhtml_InvoicestatusController extends Mage_Adminhtml_Controller_Action
{
    CONST REQUESTURI = 'https://api.unleashedsoftware.com/';
    CONST API_KEY = 'TORP+1eAdSyTAwbEkCCC07nq3TpY2+hav9t1/aU9wWeu/JG5V32U34tX8LVm7JhCrM4roiTrbUUCxkLyHpETQw==';
    CONST API_ID = 'cb139c99-c554-4b15-a8d9-9f22d1f13806';
    protected $_endPoint;
    
    public function indexAction() {
        $response = $this->_sendRequest();
        
        $invoicesList = $response['Items'];
        $session = Mage::getSingleton('adminhtml/session');
        if (sizeof($invoicesList) > 0) {
            foreach ($invoicesList as $invoiceItem) {
                if ('completed' == strtolower($invoiceItem['OrderStatus'])) {
                    // load the invoice
                    $mageOrder = Mage::getModel('sales/order')->loadByIncrementId($invoiceItem['OrderNumber']);
                    $mageOrder->setState('ready_to_print');
                    $mageOrder->setStatus('ready_to_print');
                    $mageOrder->save();
                }
            }
            $session->addSuccess(Mage::helper('invoicestatus')->__('Status of invoices where successfully change to \'Ready To Print\''));
        } else {
            $session->addError(Mage::helper('invoicestatus')->__('There was no invoice item to change the status'));
        }

        $this->_redirectUrl(Mage::getBaseUrl(). 'admin');
        
    }
    
    protected function _sendRequest() {
        $this->_endPoint = 'SalesInvoices';
        $startDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')));//'2012-08-30';
        $endDate = date('Y-m-d', mktime(23, 59, 59, date('m'), date('d'), date('Y')));//'2012-08-31';
        $request = 'startDate=' . $startDate . '&endDate=' . $endDate;
        $requestUrl = '';
        if (!empty($request)) $requestUrl = '?' . $request; 
        
        $method_signature = base64_encode(hash_hmac('sha256', $request, self::API_KEY, true)); 
        try {
            $curl = curl_init(self::REQUESTURI . $this->_endPoint . $requestUrl);
            curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
            curl_setopt($curl, CURLOPT_HEADER , 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json',"Accept: application/json", "api-auth-id: " . self::API_ID, "api-auth-signature: $method_signature"));
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    
            $curl_result = curl_exec($curl);
            error_log($curl_result);
            curl_close($curl);
             
            return json_decode($curl_result, true);
         }
         catch (Exception $e) {
             Mage::throwException('Unleashed Error: ' . $e->getMessage());
         }
    }
}