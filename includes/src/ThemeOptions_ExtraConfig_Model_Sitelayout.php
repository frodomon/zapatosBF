<?php class ThemeOptions_ExtraConfig_Model_Sitelayout
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'wide', 'label'=>Mage::helper('ExtraConfig')->__('Wide')),
            array('value'=>'boxed', 'label'=>Mage::helper('ExtraConfig')->__('Boxed')) 
        );
    }

}?>