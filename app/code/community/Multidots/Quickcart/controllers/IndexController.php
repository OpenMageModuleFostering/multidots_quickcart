<?php

/* * *
 * Filename         : IndexController.php
 * $status          : This variable is check the module is on/off.
 * $pageRedirect    : This variable is give offer to user want to redirct after click on buynow or not.
 * $params          : This variable get the all information about the product.
 * $productUrl      : This variable get is use to redirect on previous page when try become false.
 * $cart            : This varible define or initialize the cart and update the card.
 * Author           : Multidots
 * Date             : 27/06/2016
 * ** */

class Multidots_Quickcart_IndexController extends Mage_Core_Controller_Front_Action {

    /**
     * load product object and return product information for further usag
     * $productId : get product id as a param
     */
    protected function loadProduct($productId) {
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /**
     * It is return current checkout session. 
     *
     */
    protected function _getSession() {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Set back redirect url to response
     *
     * @return Mage_Checkout_CartController
     * @throws Mage_Exception
     */
    protected function _goBack() {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl) {

            if (!$this->_isUrlInternal($returnUrl)) {
                throw new Mage_Exception('External urls redirect to "' . $returnUrl . '" denied!');
            }

            $this->_getSession()->getMessages(true);
            $this->getResponse()->setRedirect($returnUrl);
        } elseif (!Mage::getStoreConfig('checkout/cart/redirect_to_cart') && !$this->getRequest()->getParam('in_cart') && $backUrl = $this->_getRefererUrl()
        ) {
            $this->getResponse()->setRedirect($backUrl);
        } else {
            if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
                $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
            }
            $this->_redirect('checkout/cart');
        }
        return $this;
    }

    /**
     * Add product in cart and redirect to checkout page
     *
     */
    public function ProductAction() {
        $status = Mage::helper('quickcart/data')->isEnable();
        $pageRedirect = Mage::helper('quickcart/data')->isRedirection();
        if ($status == 1) {
            $params = $this->getRequest()->getParams();
            if ($params) {
                try {
                    $magentoVersion = Mage::getVersion();
                    if (version_compare($magentoVersion, '1.8', '>=')) {
                        if (!$this->_validateFormKey()) {
                            $this->_goBack();
                            return;
                        }
                    }
                    if (isset($params['qty'])) {
                        $filter = new Zend_Filter_LocalizedToNormalized(
                                array('locale' => Mage::app()->getLocale()->getLocaleCode())
                        );
                        $_params['qty'] = $filter->filter($params['qty']);
                    }
                    $product = $this->loadProduct($params['product']);
                    /**
                     * Check product availability
                     */
                    if (!$product) {
                        Mage::app()->getFrontController()->getResponse()->setRedirect($productUrl)->sendResponse();
                        return;
                    }
                    $productUrl = $product->getProductUrl();
                    $cart = Mage::getModel('checkout/cart')->init();
                    
                    /**
                     * Add product in cart with custom options
                     */
                    $cart->addProduct($product, $params);
                    $cart->save();
                    $this->_getSession()->setCartWasUpdated(true);

                    Mage::dispatchEvent('checkout_cart_add_product_complete', array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                    );
                    if ($pageRedirect == 1) {
                        Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('checkout/onepage'))->sendResponse();
                    } else {
                        Mage::app()->getFrontController()->getResponse()->setRedirect($productUrl)->sendResponse();
                    }
                } catch (Mage_Core_Exception $e) {
                    if ($this->_getSession()->getUseNotice(true)) {
                        $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
                    } else {
                        $messages = array_unique(explode("\n", $e->getMessage()));
                        foreach ($messages as $message) {
                            $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                        }
                    }
                    $url = $this->_getSession()->getRedirectUrl(true);
                    if ($url) {
                        $this->getResponse()->setRedirect($url);
                    } else {
                        $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
                    }
                } catch (Mage_Core_Exception $e) {
                    $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
                    Mage::logException($e);
                    $this->_goBack();
                }
            }
        }
    }

    protected function _isAllowed() {
        return true;
    }

}