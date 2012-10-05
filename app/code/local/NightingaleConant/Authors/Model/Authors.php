<?php
class NightingaleConant_Authors_Model_Authors extends Mage_Core_Model_Abstract {
    
    public function _construct() 
    {
        parent::_construct();
        $this->_init('authors/authors');
    }
    
    public function getAllOptions()
    {
        $authors = $this->getCollection()
                ->addFieldToSelect('author_id')
                ->addFieldToSelect('first_name')
                ->addFieldToSelect('last_name')
                ->addFieldToFilter('active',1);
        $ids = array();
        foreach((object)$authors as $author) {
            $ids[$author->getAuthorId()] = $author->getFirstName() . " " . $author->getLastName();
        }
        
        return $ids;
    }
    
}

?>
