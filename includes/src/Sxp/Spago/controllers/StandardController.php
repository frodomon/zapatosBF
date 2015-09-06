<?php
/**
 * Sxp
 * Spago
 *
 * @category    Sxp
 * @package     Sxp_Spago
 * @copyright   Copyright (c) 2015
 */

class Sxp_Spago_StandardController extends Mage_Core_Controller_Front_Action
{

    protected $_order;

    public function getOrder()
    {
        if ($this->_order == null) {
        }
        return $this->_order;
    }

    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function getStandard()
    {
        return Mage::getSingleton('spago/standard');
    }

    public function redirectAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setMpStandardQuoteId($session->getQuoteId());
        $this->getResponse()->setBody($this->getLayout()->createBlock('spago/standard_redirect')->toHtml());
        $session->unsQuoteId();
    }

    public function getMedioPagoById($id)
    {
        $result = '';

        switch ($id) {
            case '0':
                $result = 'SeguriCash';
            break;
            case '1':
                $result = 'SeguriPago Visa'; //Visa Credito
                break;
            case '2':
                $result = 'SeguriPago Mastercard';
                break;
            case '3':
                $result = 'SeguriPago American Express';
                break;
            case '13':
                $result = 'SeguriPago Visa'; //Visa Debito
                break;
            default:
                $result = 'No definido';
                break;
        }

        return $result;

    }

    public function dataRecepcion(){

        $data = null;

        if ($_POST) {
            $data = array(
                "idsocio"               => $_POST['O1'],
                "num_pedido"            => $_POST['O2'],
                "num_transaccion"       => $_POST['O3'],
                "fecha_hora_trans"      => $_POST['O4'],
                "moneda"                => $_POST['O5'],
                "importe"               => $_POST['O6'],
                "resultado"             => $_POST['O7'],
                "cod_respuesta"         => $_POST['O8'],
                "txt_respuesta"         => $_POST['O9'],
                "medio_pago"            => $_POST['O10'],
                "tipo_respuesta"        => $_POST['O11'],
                "cod_autoriza"          => $_POST['O12'],
                "num_referencia"        => $_POST['O13'],
                "hash"                  => $_POST['O14'],
                "cod_producto"          => $_POST['O15'],
                "num_tarjeta"           => $_POST['O16'],
                "nom_tarjetahabiente"   => $_POST['O17'],
                "fecha_vencimiento"     => $_POST['O18'],
            );
        }

        return $data;
    }

    /*
     * Controlladores
    */

    public function successfullAction()
    {
        //header( 'Content-Type:text/html; charset=UTF-8' );

        //URL RESPONSE ONLINE
        if ($this->getRequest()->isPost())
        {

            $seguripago = Mage::getModel('spago/standard');
            // Recogemos datos de respuesta
            $data = $this->dataRecepcion();

            // Inicializamos el valor del status del pedido
            $status = "";

            // Cálculo del SHA1
            $firma_remota   = $data['hash'];
            $socio          = $data['idsocio'];
            $key            = $seguripago->getConfigData('key_sp');
            $pedido         = $data['num_pedido'];
            $autoriza       = $data['cod_autoriza'];
            $referencia     = $data['num_referencia'];

            $firma_local = $this->generacionHashRecepcion($socio, $key, $pedido, $autoriza, $referencia);

            if ($firma_local == $firma_remota)
            {

                if( $data['cod_producto'] == "1")
                {
                    if($data['resultado'] == "1")
                    {
                        $ord                = $data['num_pedido'];
                        $order              = Mage::getModel('sales/order')->loadByIncrementId($ord);
                        $state              = 'new';
                        $status             = 'complete';
                        $comment            = 'Pedido pagado por : <br> '.$this->getMedioPagoById($data['medio_pago'])
                                             ." <br> Código Seguripago : <strong>". $data['num_transaccion']."</strong>"
                                             ." <br> Código Autorización : <strong>". $data['cod_autoriza']."</strong>";
                        $isCustomerNotified = true;

                        $order->setState($state, $status, $comment, $isCustomerNotified);
                        $order->save();
                        $this->success($data);

                    }
                    elseif($data['resultado'] == "2")
                    {
                        $ord                = $data['num_pedido'];
                        $order              = Mage::getModel('sales/order')->loadByIncrementId($ord);
                        $state              = 'new';
                        $status             = 'canceled';
                        $comment            = 'Pedido cancelado';
                        $isCustomerNotified = true;
                        $order->setState($state, $status, $comment, $isCustomerNotified);
                        $order->save();

                        $this->success($data);
                    }
                }
                elseif( $data['cod_producto'] == "2")
                {
                    if($data['resultado'] == "0"){
                        $urlInstrucciones = Mage::getBaseUrl()."spago/standard/instrucciones?codSeguripago="
                                        .$data['num_transaccion']."&fecha=".$data['fecha_vencimiento'];
                        $ord        = $data['num_pedido'];
                        $order      = Mage::getModel('sales/order')->loadByIncrementId($ord);
                        $state      = 'new';
                        $status     = 'pending';
                        $comment    = 'Pedido realizado por : <br> '.$this->getMedioPagoById($data['medio_pago'])
                                      ." <br> Código Seguripago : <strong>". $data['num_transaccion']."</strong>"
                                      ." <br> Fecha de Vencimiento : <strong>". $data['fecha_vencimiento']."</strong>"
                                      ." <br> Instrucciones para realizar el pago  <strong><a href='$urlInstrucciones'>AQUI</strong></a>.";
                        $isCustomerNotified = true;

                        $order->setState($state, $status, $comment, $isCustomerNotified);
                        $order->save();
                        $this->success($data);
                    }
                    else if($data['resultado'] == "1"){

                        $ord                = $data['num_pedido'];
                        $order              = Mage::getModel('sales/order')->loadByIncrementId($ord);
                        $state              = 'new';
                        $status             = 'complete';
                        $comment            = 'Pedido pagado por : ' . $this->getMedioPagoById($data['medio_pago'])
                                            .'<br>Código Seguripago : <strong>'. $data['num_transaccion'].'</strong>';
                        $isCustomerNotified = true;

                        $order->setState($state, $status, $comment, $isCustomerNotified);
                        $order->save();
                        $this->success($data);
                    }


                }elseif( $data['cod_producto'] == "6"){

                    if($data['resultado'] == "1"){

                            $ord                = $data['num_pedido'];
                            $order              = Mage::getModel('sales/order')->loadByIncrementId($ord);
                            $state              = 'new';
                            $status             = 'complete';
                            $comment            = 'Pedido pagado por : <br> '.$this->getMedioPagoById($data['medio_pago'])
                                                    ." <br> Código Seguripago : <strong>". $data['num_transaccion']."</strong>"
                                                    ." <br> Código Autorización : <strong>". $data['cod_autoriza']."</strong>";
                            $isCustomerNotified = true;

                            $order->setState($state, $status, $comment, $isCustomerNotified);
                            $order->save();

                            $this->success($data);

                    }elseif($data['resultado'] == "2"){
                            $ord                = $data['num_pedido'];
                            $order              = Mage::getModel('sales/order')->loadByIncrementId($ord);
                            $state              = 'new';
                            $status             = 'canceled';
                            $comment            = 'Pedido cancelado';
                            $isCustomerNotified = true;
                            $order->setState($state, $status, $comment, $isCustomerNotified);
                            $order->save();

                            $this->success($data);
                    }
                }

            }
            else {

                $this->_redirect('checkout/cart');
            }

        }
        else{

            $this->_redirect('checkout/cart');
        }

    }

    public function diferidoAction()
    {
        header('Content-Type:text/html; charset=UTF-8');

        if ($this->getRequest()->isPost()) //URL RESP. ONLINE
        {
            $seguripago = Mage::getModel('spago/standard');

            // Recogemos datos de respuesta
            $data = $this->dataRecepcion();

            // Inicializamos el valor del status del pedido
            $status = "";

            // Cálculo del SHA1
            $firma_remota   = $data['hash'];
            $socio          = $data['idsocio'];
            $key            = $seguripago->getConfigData('key_sp');
            $pedido         = $data['num_pedido'];
            $autoriza       = $data['cod_autoriza'];
            $referencia     = $data['num_referencia'];

            $firma_local    = $this->generacionHashRecepcion($socio, $key, $pedido, $autoriza, $referencia);

            if ($firma_local == $firma_remota) {

                if ($data["cod_producto"] == "1") {
                    if ($data['resultado'] == "1") {
                        $data['ff'] = 'aprobado';
                        $ord        = $data['num_pedido'];
                        $orde       = substr($ord, -9);
                        $order      = Mage::getModel('sales/order')->loadByIncrementId($orde);
                        $state      = 'new';
                        $status     = 'complete';
                        $comment    = 'Actualizado estado del pedido : Seguripago - '.$this->getMedioPagoById($data['medio_pago'])
                                   ." <br> Código = <strong>". $data['num_transaccion']."</strong>";
                        $isCustomerNotified = true;
                        $order->setState($state, $status, $comment, $isCustomerNotified);
                        $order->save();

                        if ($data['medio_pago'] != 1 and $data['medio_pago'] != 2 and $data['medio_pago'] != 3) {
                            $texto = "Hemos recibido el pago de su <b>código SeguriPago<b>:" . $data['num_transaccion'] . ".<br>
                                En unos momentos nos ponemos en contacto con ud.";
                            $order->sendOrderUpdateEmail(true, $texto);
                        }

                        $I3 = $this->getHashAcuse($data["idsocio"], $data["num_transaccion"]);
                        $acuseNowParams = "I1=" . $data["idsocio"] . "&I2=" . $data["num_transaccion"] . "&I3=" . $I3;
                        $this->getConfirmacionRecepcion($acuseNowParams);

                    }
                    else if($data['resultado'] == "2"){
                            $ord                = $data['num_pedido'];
                            $order              = Mage::getModel('sales/order')->loadByIncrementId($ord);
                            $state              = 'new';
                            $status             = 'canceled';
                            $comment            = 'Pedido cancelado';
                            $isCustomerNotified = true;
                            $order->setState($state, $status, $comment, $isCustomerNotified);
                            $order->save();

                             $I3 = $this->getHashAcuse($data["idsocio"], $data["num_transaccion"]);
                            $acuseNowParams = "I1=" . $data["idsocio"] . "&I2=" . $data["num_transaccion"] . "&I3=" . $I3;
                            $this->getConfirmacionRecepcion($acuseNowParams);
                    }


                    print 1;
                    exit();
                } elseif ($data["cod_producto"] == "2") {

                    if ($data['resultado'] == "1") {

                        $data['ff'] = 'aprobado';
                        $ord = $data['num_pedido'];
                        $orde = substr($ord, -9);
                        $order = Mage::getModel('sales/order')->loadByIncrementId($orde);
                        $state = 'new';
                        $status = 'complete';
                        $comment = 'Actualizado estado del pedido : Seguripago - '.$this->getMedioPagoById($data['medio_pago'])
                                   ." <br> Código = <strong>". $data['num_transaccion']."</strong>";
                        $isCustomerNotified = true;
                        $order->setState($state, $status, $comment, $isCustomerNotified);
                        $order->save();

                        if ($data['medio_pago'] != 1 and $data['medio_pago'] != 2 and $data['medio_pago'] != 3) {
                            $texto = "Hemos recibido el pago de su <b>código SeguriPago<b>:" . $data['num_transaccion'] . ".<br>
                                En unos momentos nos ponemos en contacto con ud.";
                            $order->sendOrderUpdateEmail(true, $texto);
                        }

                        $I3 = $this->getHashAcuse($data["idsocio"], $data["num_transaccion"]);
                        $acuseNowParams = "I1=" . $data["idsocio"] . "&I2=" . $data["num_transaccion"] . "&I3=" . $I3;
                        $this->getConfirmacionRecepcion($acuseNowParams);


                        print 1;
                        exit();

                    } else {

                        print 0;
                        exit();

                    }
                }
                else if($data['cod_producto'] == "6"){
                    if ($data['resultado'] == "1") {
                        $data['ff'] = 'aprobado';
                        $ord = $data['num_pedido'];
                        $orde = substr($ord, -9);
                        $order = Mage::getModel('sales/order')->loadByIncrementId($orde);
                        $state = 'new';
                        $status = 'complete';
                        $comment = 'Actualizado estado del pedido : '.$this->getMedioPagoById($data['medio_pago'])
                                   ." <br> Código = <strong>". $data['num_transaccion']."</strong>";
                        $isCustomerNotified = true;
                        $order->setState($state, $status, $comment, $isCustomerNotified);
                        $order->save();

                        if ($data['medio_pago'] != 1 and $data['medio_pago'] != 2 and $data['medio_pago'] != 3) {
                            $texto = "Hemos recibido el pago de su <b>código SeguriPago<b>:" . $data['num_transaccion'] . ".<br>
                                En unos momentos nos ponemos en contacto con ud.";
                            $order->sendOrderUpdateEmail(true, $texto);
                        }

                        $I3 = $this->getHashAcuse($data["idsocio"], $data["num_transaccion"]);
                        $acuseNowParams = "I1=" . $data["idsocio"] . "&I2=" . $data["num_transaccion"] . "&I3=" . $I3;
                        $this->getConfirmacionRecepcion($acuseNowParams);

                    }
                    else if($data['resultado'] == "2"){
                            $ord                = $data['num_pedido'];
                            $order              = Mage::getModel('sales/order')->loadByIncrementId($ord);
                            $state              = 'new';
                            $status             = 'canceled';
                            $comment            = 'Pedido cancelado';
                            $isCustomerNotified = true;
                            $order->setState($state, $status, $comment, $isCustomerNotified);
                            $order->save();

                             $I3 = $this->getHashAcuse($data["idsocio"], $data["num_transaccion"]);
                            $acuseNowParams = "I1=" . $data["idsocio"] . "&I2=" . $data["num_transaccion"] . "&I3=" . $I3;
                            $this->getConfirmacionRecepcion($acuseNowParams);
                    }

                    print 1;
                    exit();
                }

                print "Error al validar producto";
                exit();
            } else {
                print "Error al validar hash";
                exit();
            }


        }else{

            $this->_redirect('checkout/cart');

        }
    }

    public function getRutaAcuseSPago()
    {
        $seguripago = Mage::getModel('spago/standard');
        $modo = $seguripago->getConfigData('modo_sp');
        if ( $modo == '1' ) {
            $urlAcuse = 'https://pagoin.seguripago.pe/pagoin_acuse.php';
        } else {
            $urlAcuse = 'https://test.seguripago.pe/pagoin_acuse.php';
        }

        return $urlAcuse;
    }

    public function getHashAcuse($idSocio, $numeroTransaccion)
    {
        $seguripago = Mage::getModel('spago/standard');
        $I3 = hash_hmac("sha1",$idSocio.$numeroTransaccion,$seguripago->getConfigData('key_sp'));
        return $I3;
    }

    public function getIsCurlActive()
    {
        return function_exists('curl_version');
    }

    public function getConfirmacionRecepcion($parameters)
    {
        if ( $this->getIsCurlActive() ) {

            $_h = curl_init ();
            curl_setopt ( $_h, CURLOPT_HEADER, FALSE );
            curl_setopt ( $_h, CURLOPT_RETURNTRANSFER, TRUE );
            curl_setopt ( $_h, CURLOPT_POST, TRUE );
            curl_setopt ( $_h, CURLOPT_URL, $this->getRutaAcuseSPago() );
            curl_setopt ( $_h, CURLOPT_POSTFIELDS, $parameters );
            curl_setopt ( $_h, CURLOPT_RETURNTRANSFER, TRUE );
            curl_setopt ( $_h, CURLOPT_SSL_VERIFYHOST, FALSE );
            curl_setopt ( $_h, CURLOPT_SSL_VERIFYPEER, FALSE );

            if (($result = curl_exec($_h)) === FALSE) {
                throw new Exception('cURL error: ' . curl_error ( $_h ) . "<br />\n");
            }

            curl_close ( $_h );

            if ( $result == "Proceso concluido satisfactoriamente.") {

                return TRUE;

            } else {

                return $result;
            }


        } else {

            return "Curl no instalado..";
        }
    }

    public function cancelAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getMpStandardQuoteId(true));
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
                $order->cancel()->save();
            }
        }
        $this->_redirect('checkout/cart');
    }

    public function success($data)
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getMpStandardQuoteId(true));
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
        $order = Mage::getModel('sales/order')->loadByIncrementId($data['num_pedido']);


        if($data['resultado'] != "2"){

            if (!$order->getEmailSent()) {
                $order->sendNewOrderEmailSpago();
            }
        }

        $this->loadLayout();
        $this->renderLayout();

    }

    public function instruccionesAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    private function generacionHashRecepcion($socio, $key, $pedido, $autoriza, $referencia) {

        $salt = "SEGURIPAGO";
        $arrDatos = array();
        $arrDatos[] = $socio;
        $arrDatos[] = $key;
        $arrDatos[] = $pedido;
        $arrDatos[] = $autoriza;
        $arrDatos[] = $referencia;
        $arrDatos[] = $salt;

        $cadena = implode("", $arrDatos);

        $hash = hash_hmac("sha1", $cadena, $key);

        return $hash;
    }

}
