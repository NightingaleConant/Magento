<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Model_Attribute extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('amorderattr/attribute');
    }
    
    public function dropAttributeField($code)
    {
        $connection = Mage::getSingleton('core/resource') ->getConnection('core_write');
        $sql = 'ALTER TABLE `' . $this->getResource()->getTable('amorderattr/order_attribute') . '` DROP `' . $code . '`';
        $connection->query($sql);
    }
    
    public function addAttributeField($code, $type)
    {
        $connection = Mage::getSingleton('core/resource') ->getConnection('core_write');
        $sql = 'ALTER TABLE `' . $this->getResource()->getTable('amorderattr/order_attribute') . '` ADD `' . $code . '` ' . $this->_getSqlType($type);
        $connection->query($sql);
    }
    
    protected function _getSqlType($fieldType)
    {
        $type = '';
        switch ($fieldType)
        {
            case 'textarea':
                $type = 'TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL';
            break;
            case 'text':
                $type = 'VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL';
            break;
            case 'date':
                $type = 'DATE NULL';
            break;
            case 'datetime':
                $type = 'DATETIME NULL';
            break;
            default: // all others: select, checkboxes etc. for future use
                $type = 'VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL';
            break;
        }
        return $type;
    }
    
    protected function _beforeSave()
    {
        $connection = Mage::getSingleton('core/resource') ->getConnection('core_read');
        $sql = 'DESCRIBE `' . $this->getResource()->getTable('amorderattr/order_attribute') . '`';
        $tableInfo = $connection->fetchAssoc($sql);
        foreach ($tableInfo as $column)
        {
            if ('date' == $column['Type'])
            {
                $formattedDate = date('Y-m-d H:i:s', strtotime($this->getData($column['Field'])));
                $this->setData($column['Field'], $formattedDate);
            }
            if ('datetime' == $column['Type'])
            {
                $formattedDate = date('Y-m-d H:i:s', strtotime($this->getData($column['Field'])));
                $this->setData($column['Field'], $formattedDate);
            }
        }
        return parent::_beforeSave();
    }
}
