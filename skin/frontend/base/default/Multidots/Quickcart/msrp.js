/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    design
 * @package     base_default
 * @copyright   Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!window.Catalog) {
    window.Catalog = {};
}

Catalog.Map = {
    bindProductForm: function() {
        if (('undefined' != typeof productAddToCartForm) && productAddToCartForm) {
            productAddToCartFormOld = productAddToCartForm;
            productAddToCartForm = new VarienForm('product_addtocart_form_from_popup');
            productAddToCartForm.submitLight = productAddToCartFormOld.submitLight;
            productAddToCartForm.submitConfe = productAddToCartFormOld.submitConfe;
        } else if (!$('product_addtocart_form_from_popup')) {
            return false;
        } else if ('undefined' == typeof productAddToCartForm) {
            productAddToCartForm = new VarienForm('product_addtocart_form_from_popup');
        }

        productAddToCartForm.submit = function(button, url) {
            if (('undefined' != typeof productAddToCartFormOld) && productAddToCartFormOld) {
                if (Catalog.Map.active) {
                    Catalog.Map.hideHelp();
                }
                if (productAddToCartForm.qty && $('qty')) {
                    $('qty').value = productAddToCartForm.qty;
                }
                parentResult = productAddToCartFormOld.submit();
                return false;
            }
            if (window.opener) {
                var parentButton = button;
                new Ajax.Request(this.form.action, {
                    parameters: {isAjax: 1, method: 'GET'},
                    onSuccess: function(transport) {
                        window.opener.focus();
                        if (parentButton && parentButton.href) {
                            setPLocation(parentButton.href, true);
                            Catalog.Map.hideHelp();
                        }
                    }
                });
                return;
            }
            if (this.validator.validate()) {
                var form = this.form;
                var oldUrl = form.action;

                if (url) {
                    form.action = url;
                }
                if (!form.getAttribute('action')) {
                    form.action = productAddToCartForm.action;
                }
                try {
                    this.form.submit();
                } catch (e) {
                    this.form.action = oldUrl;
                    throw e;
                }
                this.form.action = oldUrl;

                if (button && button != 'undefined') {
                    button.disabled = true;
                }
            }
        };
    }
};

Event.observe(window, 'resize', function(event) {
    if (Catalog.Map.active) {
        Catalog.Map.showHelp(event);
    }
})


