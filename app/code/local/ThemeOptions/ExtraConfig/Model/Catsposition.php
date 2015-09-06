<?php class ThemeOptions_ExtraConfig_Model_Catsposition
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'top', 'label'=>Mage::helper('ExtraConfig')->__('Above content')),
            array('value'=>'bottom', 'label'=>Mage::helper('ExtraConfig')->__('Under content')) 
        );
    }

}?>