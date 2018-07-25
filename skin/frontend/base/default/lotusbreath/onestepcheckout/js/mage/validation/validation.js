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
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint jquery:true*/
(function($) {
    "use strict";
    /**
     * Validation rule for grouped product, with multiple qty fields,
     * only one qty needs to have a positive integer
     */
    $.validator.addMethod(

        "validate-grouped-qty",
        function(value, element, params) {
            var result = false;
            var total = 0;
            $(params).find('input[data-validate*="validate-grouped-qty"]').each(function(i, e) {
                var val = $(e).val();
                if (val && val.length > 0) {
                    result = true;
                    var valInt = parseInt(val, 10) || 0;
                    if (valInt >= 0) {
                        total += valInt;
                    } else {
                        result = false;
                        return result;
                    }
                }
            });
            return result && total > 0;
        },
        'Please specify the quantity of product(s).'
    );

    $.validator.addMethod(
        "validate-one-checkbox-required-by-name",
        function(value, element, params) {
            var checkedCount = 0;
            if (element.type === 'checkbox') {
                $('[name="' + element.name + '"]').each(function() {
                    if ($(this).is(':checked')) {
                        checkedCount += 1;
                        return false;
                    }
                });
            }
            var container = '#' + params;
            if (checkedCount > 0) {
                $(container).removeClass('validation-failed');
                $(container).addClass('validation-passed');
                return true;
            } else {
                $(container).addClass('validation-failed');
                $(container).removeClass('validation-passed');
                return false;
            }
        },
        'Please select one of the options.'
    );

    $.validator.addMethod(
        "validate-date-between",
        function(value, element, params) {
            var minDate = new Date(params[0]),
                maxDate = new Date(params[1]),
                inputDate = new Date(element.value);
            minDate.setHours(0);
            maxDate.setHours(0);
            if (inputDate >= minDate && inputDate <= maxDate) {
                return true;
            }
            this.dateBetweenErrorMessage = $.mage.__('Please enter a date between %min and %max.').replace('%min', minDate).replace('%max', maxDate);
            return false;
        },
        function(){
            return this.dateBetweenErrorMessage;
        }
    );
})(jQuery);