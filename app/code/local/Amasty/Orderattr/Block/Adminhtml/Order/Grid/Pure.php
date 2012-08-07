<?php
if ('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Orderattach/active')){
    class Amasty_Orderattr_Block_Adminhtml_Order_Grid_Pure extends Amasty_Orderattach_Block_Adminhtml_Sales_Order_Grid {}
} 
else {
    class Amasty_Orderattr_Block_Adminhtml_Order_Grid_Pure extends Mage_Adminhtml_Block_Sales_Order_Grid {}
}