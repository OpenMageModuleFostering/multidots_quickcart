<?php

/*
 * Filename         : Data.php
 * Author           : Multidots
 * Date             : 27/06/2016
 * */

class Multidots_Quickcart_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * It check either extension enabled or not
     * */
    public function isEnable() {
        return Mage::getStoreConfig('quickcartlist/general/boolean');
    }

    /**
     * it get the redirect setting from admin configuration and work accordingly
     * */
    public function isRedirection() {
        return Mage::getStoreConfig('quickcartlist/general/redirection');
    }

    /**
     * it will return the button title of quick cart as per the admin set it from admin configuration
     * */
    public function getButtonTitle() {
		$quick_cart_button_title = Mage::getStoreConfig('quickcartlist/general/button_name');
		if(empty($quick_cart_button_title))
		{
			return 'Quick Cart';
		}else{
			return Mage::getStoreConfig('quickcartlist/general/button_name');
		}
	}

}