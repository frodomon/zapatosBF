<?php
/**
 * Sxp
 * Spago
 *
 * @category    Sxp
 * @package     Sxp_Spago
 * @copyright   Copyright (c) 2011 Sxp (http://www.magentoxp.com)
 * @author      Magentoxp (Sxp)Magentoxp Team <support@magentoxp.com>
 */
?>

<?php
class Sxp_Spago_Block_Standard_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {

        $standard = Mage::getModel('spago/standard');

        $urlLogo = Mage::getBaseUrl('skin').'frontend/spago/default/images/sp_logo.png';
        $html = '<html>';
        $html .= '<body onLoad="document.frm.submit();">';
        $html .= '<center>';
        $html .= '<img src='.$urlLogo.' style="margin: 100px 0px 0px 0px;"/>';
        $html .= '</br>';
        $html .= $this->__('Redirigiendo a la pasarela SeguriPago');
        $html .= '</center>';
        $html .='<form name=frm action="'.$standard->getSPagoUrl().'" method=POST>';

            foreach ($standard->getStandardCheckoutFormFields() as $field=>$value)
            {
                $html.='<input type="hidden" name="'.$field.'" value = "'.$value.'">';
            }

        $html .= '</form>';
        $html .= '</body>';
        $html .= '</html>';

        return $html;
    }
}
