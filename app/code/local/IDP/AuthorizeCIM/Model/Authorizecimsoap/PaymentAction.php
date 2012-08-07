<?php
/**
 */
 
class IDP_AuthorizeCIM_Model_authorizecimsoap_PaymentAction
{
	public function toOptionArray()
	{
		return array(
			array(
				'value' => IDP_AuthorizeCIM_Model_authorizecimsoap::PAYMENT_ACTION_AUTH,
				'label' => Mage::helper('authorizeCIM')->__('Authorize')
			),
			array(
				'value' => IDP_AuthorizeCIM_Model_authorizecimsoap::PAYMENT_ACTION_AUTH_CAPTURE,
				'label' => Mage::helper('authorizeCIM')->__('Authorize and Capture')
			)
		);
	}
}

?>

