<?php
class Moogento_Pickpack_Model_Image extends Mage_Adminhtml_Model_System_Config_Backend_Image
{
 protected function _getAllowedExtensions()
    {
        return array('tif', 'tiff', 'png', 'jpg', 'jpe', 'jpeg','gif');
    }
}
