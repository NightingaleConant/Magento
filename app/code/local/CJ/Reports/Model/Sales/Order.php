<?php

/**
 *
 */
class CJ_Reports_Model_Sales_Order extends Amasty_Orderattr_Model_Sales_Order
{
    /**
     * @var array
     */
    protected $customQbData;

    public function getData($key='', $index=null)
    {
        $data = $this->getCustomQBData();

        if (false === $data) {
            return parent::getData($key, $index);
        }

        switch ($key) {
            case 'order_type_id':
                return $data['orderTypeId'];
                break;
            case 'order_type_code':
                return $data['OrderTypeCode'];
                break;
            case 'qb_sales_account':
                return $data['qbSalesAccount'];
                break;
            case 'qb_sales_class':
                return $data['qbSalesClass'];
                break;
            case 'qb_cost_account':
                return $data['qbCostAccount'];
                break;
            case 'qb_cost_class':
                return $data['qbCostClass'];
                break;
            case 'qb_return_account':
                return $data['qbReturnAccount'];
                break;
            case 'qb_return_class':
                return $data['qbReturnClass'];
                break;
            case 'qb_allowances_account':
                return $data['qbAllowancesAccount'];
                break;
            case 'qb_allowances_class':
                return $data['qbAllowancesClass'];
                break;
        }
        return parent::getData($key, $index);
    }

    protected function getCustomQBData()
    {
        if (null === $this->customQbData) {
            $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
            $sql        = 'SELECT order_type_code from amasty_amorderattr_order_attribute' .
                          ' WHERE order_id = :id';
            $result     = $connection->fetchAll($sql, array('id' => $this->getId()));
            if (count($result) != 1) {
                $this->customQbData = false;
                return false;
            }
            $sql = 'SELECT * FROM custom_qb_map WHERE orderTypeId = :code';
            $map = $connection->fetchAll($sql, array('code' => $result[0]['order_type_code']));
            if (count($map) != 1) {
                $this->customQbData = false;
                return false;
            }
            $this->customQbData = $map[0];
        }
        return $this->customQbData;
    }
}
