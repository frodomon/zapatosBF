<?php
/**
 * Magento
 *
 * @category
 * @package
 * @copyright  Copyright (c) 2015
 */

class Sxp_Spago_Model_Source_TransactionMode
{
    public function toOptionArray()
    {
        $options =  array();

        $options[] = array(
            	   'value' => '0',
            	   'label' => 'Testing'
         );

        $options[] = array(
            	   'value' => '1',
            	   'label' => 'Produccion'
         );

        return $options;
    }
}