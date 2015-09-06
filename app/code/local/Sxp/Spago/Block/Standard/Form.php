<?php
/**
 * Sxp
 * Spago
 *
 * @category    Sxp
 * @package     Sxp_Spago
 * @copyright   Copyright (c) 2015
 */

class Sxp_Spago_Block_Standard_Form extends Mage_Payment_Block_Form
{
    /**
     * Set template and redirect message
     */
    protected function _construct()
    {

        parent::_construct();

        $mark = Mage::getConfig()->getBlockClassName('core/template');
        $mark = new $mark;
        $mark->setTemplate('spago/mark.phtml');

        $this->setTemplate('spago/form.phtml')->setMethodTitle('')
            ->setMethodLabelAfterHtml($mark->toHtml());
    }

}
