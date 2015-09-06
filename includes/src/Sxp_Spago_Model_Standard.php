<?php

date_default_timezone_set('America/Lima');

/**
 * Sxp
 * Spago
 *
 * @category    Sxp
 * @package     Sxp_Spago
 * @copyright   Copyright (c) 2015 Sxp
 * @author      Magentoxp (Sxp)Magentoxp Team <support@magentoxp.com>
 */

class Sxp_Spago_Model_Standard extends Mage_Payment_Model_Method_Abstract
{

	protected $_code = 'spago';
    protected $_formBlockType = 'spago/standard_form';
    protected $_infoBlockType = 'spago/standard_info';
	protected $_isInitializeNeeded      = true;
	protected $_canUseInternal          = false;
	protected $_canUseForMultishipping  = false;

	public function getOrderPlaceRedirectUrl()
	{
		return Mage::getUrl('spago/standard/redirect', array('_secure' => true));
	}

    public function getSPagoUrl($type=null)
    {
    	$url_cancel = Mage::getUrl('spago/standard/cancel');
    	$url_process = Mage::getUrl('spago/standard/process');
    	$url_successfull = Mage::getUrl('spago/standard/successfull');
    	$url_submit = 'https://test.seguripago.pe/pagoin.php';

        $modo = $this->getConfigData('modo_sp');

        if($modo=='1'){
            $url_submit = 'https://pagoin.seguripago.pe/pagoin.php';
        }
        else{
            $url_submit = 'https://test.seguripago.pe/pagoin.php';
        }

    	switch ($type){
    		case 'cancel':
    			return $url_cancel;
    			break;
    		case 'process':
    			return $url_process;
    			break;
    		case 'successfull':
    			return $url_successfull;
    			break;
    		default:
    			return $url_submit;
    			break;
    	}
    }

    public function getSession()
    {
        return Mage::getSingleton('spago/session');
    }

    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    private function rangoHoras(DateTime $date)
    {
        $hora = $date->format('H');
        if( $hora >= 8 && $hora <= 16 ){
            return true;
        }

        return false;
    }

    private function esMadrugada(DateTime $date)
    {
        $hora = $date->format('H');

        if($hora >= 0 && $hora <= 7){
            return true;
        }

        return false;
    }

    private function esNoche(DateTime $date)
    {
        $hora = $date->format('H');

        if($hora >= 17 && $hora <= 23){
            return true;
        }

        return false;
    }

    public function getFechaVencimiento(DateTime $fechaVencimiento)
    {
        if($this->rangoHoras($fechaVencimiento)){

            $fechaVencimiento->add(new DateInterval('PT4H'));

            return $fechaVencimiento->getTimestamp();

        }elseif($this->esNoche($fechaVencimiento)){

            $fechaVencimiento->add(new DateInterval('P1D'));
            $fechaVencimiento->setTime(12,0,0);

            return $fechaVencimiento->getTimestamp();

        }elseif($this->esMadrugada($fechaVencimiento)){

            $fechaVencimiento->setTime(12,0,0);

            return $fechaVencimiento->getTimestamp();
        }
    }

    public function getStandardCheckoutFormFields()
    {
        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $isOrderVirtual = $order->getIsVirtual();
        $address = $isOrderVirtual ? $order->getBillingAddress() : $order->getShippingAddress();

        $dato_cliente = array();

        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();

            $dato_cliente = array(
                    $customerData->getId(),         //-- Id del cliente, en el comercio
                    $customerData->getFirstname(),  //-- Nombre(s)
                    $customerData->getLastname(),   //-- Apellido(s)
                    "",                             //-- Razón social
                    "",                             //-- Tipo de documento
                    "",                             //-- Número de documento
                    $customerData->getEmail(),      //-- Correo
                    "",                             //-- Dirección
                    "",                             //-- País del cliente
                    ""                              //-- Sexo (M)asculino o (F)emenino
            );
         }

        $arrDatos = array();
        $precio = number_format($order->getBaseGrandTotal(), 2, '.', '');

        $data['articulo'] = '';
        $data['pantalla'] = 'H';
        $data['obviar'] = '';

        /**
        * Asignación de variables
        */
        $arrDatos[] = $I1 = $this->getConfigData('id_sp');                      //-- Código de e-commerce, proporcionado por Seguripago
        $arrDatos[] = $I2 = str_pad($orderIncrementId,5,'0',STR_PAD_LEFT);      //-- Número de pedido del e-commerce
        $arrDatos[] = $I3 = time();                                             //-- Fecha/Hora de envío (Unixtime)
        $arrDatos[] = $I4 = 'PEN';                                              //-- Moneda (ISO 4217)
        $arrDatos[] = $I5 = $precio;                                            //-- Importe de venta
        $arrDatos[] = $I6 = empty($dato_cliente)?'':implode("//",$dato_cliente);            //-- Serializando array datos del cliente, dejar en blanco si no se desea enviar datos de cliente.
        $arrDatos[] = $I7 = empty($data['articulo'])?'':implode("//",$data['articulo']);    //-- Serializando array de datos de artículo, dejar en blanco si no se desea enviar datos de artículos
        $arrDatos[] = $I8 = $this->getFechaVencimiento(new DateTime());                     //-- Fecha / Hora de vencimiento (Unixtime), en este ejemplo establecemos que el pago vensa en 72 horas
        $arrDatos[] = $this->getConfigData('key_sp');                                       //-- Key proporcionado por Seguripago

        /**
        * ALGORITMO DE ENCRIPTACIÓN DE DATOS
        **/
        $cadena = implode("",$arrDatos);                                        //-- Serializando toda la data a encriptar
        $hash = hash_hmac("sha1", $cadena, $this->getConfigData('key_sp'));     //-- Encriptando data
        $I9 = $hash;                                                            //-- Asignando data encriptada al campo 9

        $I10 = empty($data['pantalla'])? 'H' : $data['pantalla'];

        $I11 = empty($data['obviar'])? '' :$data['obviar'];

        $post_array = array(
        				'I1' 		=> $I1,
        				'I2' 		=> $I2,
        				'I3'		=> $I3,
        				'I4'		=> $I4,
        				'I5'		=> $I5,
        				'I6'		=> $I6,
        				'I7'		=> $I7,
        				'I8'		=> $I8,
        				'I9'		=> $I9,
        				'I10'		=> $I10,
        				'I11'		=> $I11
        		);

        return $post_array;

    }

}