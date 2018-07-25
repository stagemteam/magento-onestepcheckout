;
(function($) {
    var methods = {
        init : function(options) {
            //add index action
            $(this).find('i').each(function(indx, element) {
                    $(element).html(indx+1);
                });
            $(document).data('onestepcheckout_options', options);
            initStart();
            initValidateEvents();
            initEvents();
            createAutocomplite('postcode');
        }
    };

    var goajax = 0;
    var month = new Array('Января','Февраля','Марта','Апреля','Мая','Июня', 'Июля','Августа','Сентября','Октября','Ноября','Декабря');

    function initStart() {
        jQuery('li.address-no-pickup').hide();

        if(_getData('init_tab') == '') {
            jQuery("#pickup-field").hide();
        } else {
            jQuery('li.'+ _getData('init_tab')).show();
            initTab(_getData('init_tab'));
        }

        if(jQuery("#billing-new-address-form-register").length) {
                if(jQuery("#billing-new-address-form-register input:radio:checked").val() != '') {
                        addressRadioAction();
                    }
            }
        shippingShowHelper();
        paymentShowHelper();
        regionUpdate();
        isHideCity();
        showCalendar();

        var ua = navigator.userAgent.toLowerCase();
        var isAndroid = ua.indexOf("android") > -1;
        if(_getData('phoneMaskEnabled') == '1' && !isAndroid) {
            jQuery("#billing-telephone").inputmask({"mask": _getData('phoneMask'), androidHack: "rtfm"});
        }

        //показываем отмену купона
        if(jQuery('#onestepcheckout-sert').val()) {
            jQuery('#onestepcheckout-cancel-coupon').show();
        }

        checkPasswordField();
    }

    //function
    function initEvents()
    {
         jQuery(document).on("change keyup", "select[name='billing[country_id]']", function(){
             regionUpdate();
             isHideCity();
             jQuery('#billing-city').val('');
             jQuery('#billing-region').val('');
             reloadShippingMethod();
         });

         jQuery(document).on("change keyup", "select[name='billing[region_id]']", function(){
             prepareRegion();
             reloadShippingMethod();
         });

         jQuery("#customer-pickup").on("click", function(){
             reloadShippingMethod();
             checkPickup();
             isHideCity();
         });

         jQuery("#billing-metro").on("change keyup", function(){
             reloadShippingMethod(); //reload shipping
         });

       //if no autocomplete postcode
         jQuery("#postcode").on("keyup", function(){
             if(jQuery('#postcode').val().length > 5  && jQuery('#postcode').val().length < 7) {
                 reloadShippingMethod();
             }
         });

         jQuery(document).on("click",".onestepcheckout-shipping-container input:radio", function(){
             saveShippingMethod();
         });

         jQuery(document).on("click",".onestepcheckout-payment-container input:radio", function(){
             savePaymentMethod();
         });

         jQuery("#onestepcheckout-add-coupon").on("click", function(){
             couponAction(0);
         });

         jQuery("#onestepcheckout-sertificat .input-text").keyup(function(e) {
             if(e.keyCode == 13){
                 couponAction(0);
             }
           });

         jQuery("#onestepcheckout-cancel-coupon").on("click", function(){
             couponAction(1);
         });

         jQuery(".onestepcheckout-checout-button-container button").on("click", function(){
             if(validateBeforeSave()) {
                     jQuery(".onestepcheckout-checout-button-container button").attr('disabled', 'disabled');
                     jQuery('#co-billing-form').submit();
                 }
             return false;
         });

         jQuery(".onestepcheckout-login > a").on("click", function(){
             jQuery('.onestepcheckout-login div.onestepcheckout-login-content').slideToggle("fast", function() {
                 jQuery("#mini-login").focus();
               });
         });

         jQuery("#billing-new-address-form-register input:radio").on("click", function(){
             addressRadioAction();
         });

         jQuery('#checkout-agreements input:checkbox').on("click", function(){
             if(jQuery(this).is(':checked')) {
                 jQuery(this).parent().removeClass("onestepcheckout-error-field");
             }
         });

         jQuery(".onestepcheckout-login .button-login").on("click", function(){
             loginAction();
         });

         jQuery(".onestepcheckout-login input[name='login[password]']").keyup(function(e) {
             if(e.keyCode == 13){
                 loginAction();
             }
           });

         if(_getData('city_event') == '1') {
             jQuery(document).on("keyup", "#billing-city", function(){
                 reloadShippingMethod();
              });
         }

         if(_getData('city_event') == '2') {
             jQuery(document).on("focusout", "#billing-city", function(){
                 reloadShippingMethod();
              });
         }

         if(_getData('city_event') == '3') {
             jQuery(document).on("keyup focusout", "#billing-city", function(){
                 reloadShippingMethod();
              });
         }

         if(_getData('city_event') == '4') {
             jQuery('#billing-city').autocomplete({
                 source: _getData('yandexDeliveryUrl'),
                 minLength: 3,
                 select: function( event, ui ) {
                         setCity( ui.item ?
                             ui.item.value :
                             this.value
                             );
                         focusInId("textarea[name='billing[street][]']");
                         reloadShippingMethod();
                         return false;
                     },
                     change: function( event, ui ) {
                         setCity( ui.item ?
                             ui.item.value :
                             null
                             );
                         reloadShippingMethod();
                         return false;
                     }
             });
         }

         jQuery("#checkout_method_in_address").on("click", function(){
             checkPasswordField();
         });

         $('.btn-number').click(function(e){
                e.preventDefault();

                fieldName = jQuery(this).attr('data-field');
                type      = jQuery(this).attr('data-type');
                var input = jQuery("input[name='"+fieldName+"']");
                var currentVal = parseInt(input.val());
                if (!isNaN(currentVal)) {
                    if(type == 'minus') {
                        if(currentVal > input.attr('min')) {
                            input.val(currentVal - 1).change();
                        }
                        if(parseInt(input.val()) == input.attr('min')) {
                            jQuery(this).attr('disabled', true);
                        }
                    } else if(type == 'plus') {
                        if(currentVal < input.attr('max')) {
                            input.val(currentVal + 1).change();
                        }
                        if(parseInt(input.val()) == input.attr('max')) {
                            jQuery(this).attr('disabled', true);
                        }
                    }
                } else {
                    input.val(0);
                }
            });
            jQuery('.input-number').focusin(function(){
               jQuery(this).data('oldValue', jQuery(this).val());
            });
            jQuery('.input-number').change(function() {

                minValue =  parseInt(jQuery(this).attr('min'));
                maxValue =  parseInt(jQuery(this).attr('max'));
                valueCurrent = parseInt(jQuery(this).val());
                currentInput = jQuery(this);

                console.log(currentInput.data('oldValue'));

                if(goajax) return;
                goajax = 1;
                jQuery(this).siblings(".btn-number").attr('disabled', true);

                disableRadioSelect();
                showLoaderShipping();
                showLoaderPayment();
                showLoaderTotal();

                jQuery.getJSON(_getData('updateQtyUrl'), {
                    'id': jQuery(this).data('item-id'),
                    'qty': valueCurrent
                },
                        function(data) {
                            if(data.error == '1') {
                                showTopError(data.message);
                            } else {
                                showTopError('');
                                jQuery('#onestepcheckout-shipping-methods').html(data.shipping);
                                jQuery('#onestepcheckout-payment-methods').html(data.payment);
                                jQuery('.onestepcheckout-totals-container').html(data.totals);
                                jQuery('.item-id-subtotal-'+currentInput.data('item-id')).find('.cart-price').html(data.subtotal);
                                }

                        }).error(function()
                                {
                            hideLoaderShipping();
                            hideLoaderPayment();
                            hideLoaderTotal();
                            goajax = 0;
                            name = currentInput.attr('name');
                            if(valueCurrent >= minValue) {
                                jQuery(".btn-number[data-type='minus'][data-field='"+name+"']").removeAttr('disabled')
                            } else {
                                alert('Sorry, the minimum value was reached');
                                currentInput.val(currentInput.data('oldValue'));
                            }
                            if(valueCurrent <= maxValue) {
                                jQuery(".btn-number[data-type='plus'][data-field='"+name+"']").removeAttr('disabled')
                            } else {
                                alert('Sorry, the maximum value was reached');
                                currentInput.val(currentInput.data('oldValue'));
                            }
                            alert("Ошибка запроса! Пожалуйста, обновите страницу и попробуйте еще раз."); }
                        )
                        .complete(function() {
                            hideLoaderShipping();
                            hideLoaderPayment();
                            hideLoaderTotal();
                            enableRadioSelect();
                            goajax = 0;
                            name = currentInput.attr('name');
                            if(valueCurrent >= minValue) {
                                jQuery(".btn-number[data-type='minus'][data-field='"+name+"']").removeAttr('disabled')
                            } else {
                                alert('Sorry, the minimum value was reached');
                                currentInput.val(currentInput.data('oldValue'));
                            }
                            if(valueCurrent <= maxValue) {
                                jQuery(".btn-number[data-type='plus'][data-field='"+name+"']").removeAttr('disabled')
                            } else {
                                alert('Sorry, the maximum value was reached');
                                currentInput.val(currentInput.data('oldValue'));
                            }
                            }
                    );

            });
            jQuery(".input-number").keydown(function (e) {
                    // Allow: backspace, delete, tab, escape, enter and .
                    if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                         // Allow: Ctrl+A
                        (e.keyCode == 65 && e.ctrlKey === true) ||
                         // Allow: home, end, left, right
                        (e.keyCode >= 35 && e.keyCode <= 39)) {
                             // let it happen, don't do anything
                             return;
                    }
                    // Ensure that it is a number and stop the keypress
                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }
                });

        //add this custom events
    }

    function regionUpdate()
    {
        region_current_value = 'countryRegions' + jQuery("#billing-new-address-form select[name='billing[country_id]']").val();
        if(typeof(window[region_current_value]) != 'undefined') {
                __region_select = jQuery("#billing-new-address-form select[name='billing[region_id]']");
                jQuery("#billing-new-address-form input[name='billing[region]']").hide();

                //reset all value
                __region_select.empty();
                __region_select.append(window[region_current_value]);

                //add null element
                __region_select.prepend('<option value=""></option>');
//                __region_select.find("option[value='']").attr("selected", "selected");
                //set current value
                __region_select.find("option[value='"+ _getData('currentRegion') +"']").attr("selected", "selected");

                if(_getData('isChosenEnabled') == '1') {
                        __region_select.chosen({no_results_text: "Нет результатов"}).trigger("liszt:updated");
                        jQuery('.chzn-container-single .chzn-drop').css('width', jQuery('#ul-onestepcheckout-address').width() - 20);
                        jQuery('.chzn-container-single .chzn-search input').css('width', jQuery('#ul-onestepcheckout-address').width() - 47);
                        jQuery("#billing_region_id_chzn").show();
                    } else {
                        __region_select.show();
                    }
            } else {
                jQuery("#billing-new-address-form select[name='billing[region_id]']").hide();
                jQuery("#billing_region_id_chzn").hide();
                jQuery("#billing-new-address-form input[name='billing[region]']").show();
            }
    }

    function checkPasswordField()
    {
        if(jQuery('#checkout_method_in_address').is(':checked')) {
            jQuery('#register-customer-password input').addClass('required-entry');
            jQuery('#register-customer-password').show();
        } else {
            jQuery('#register-customer-password input').removeClass('required-entry');
            jQuery('#register-customer-password').hide();
        }
    }

    function reloadShippingMethod()
    {
        if(goajax) return;

        goajax = 1;
        disableRadioSelect();
        showLoaderShipping();
        showLoaderPayment();
        showLoaderTotal();

        jQuery.getJSON(_getData('saveaddressurl'), agregateFormData('#ul-onestepcheckout-address'),
                function(data) {
                    if(data.error == 1) {
                        showTopError(data.message);
                    } else {
                        showTopError('');
                        jQuery('#onestepcheckout-shipping-methods').html(data.shipping);
                        jQuery('#onestepcheckout-payment-methods').html(data.payment);
                        jQuery('.onestepcheckout-totals-container').html(data.totals);
                        paymentShowHelper();
                        shippingShowHelper();
                        }
                    }
                ).error(function()
                        {
                    hideLoaderShipping();
                    hideLoaderPayment();
                    hideLoaderTotal();
                    goajax = 0;
                    alert("Ошибка запроса! Пожалуйста, обновите страницу и попробуйте еще раз."); }
                )
                .complete(function() {
                    hideLoaderShipping();
                    hideLoaderPayment();
                    hideLoaderTotal();
                    enableRadioSelect();
                    showCalendar();
                    goajax = 0;}
                );
        return ;
    }

    function saveShippingMethod()
    {
        if(goajax) return;

        goajax = 1;
        disableRadioSelect();
        showLoaderPayment();
        showLoaderTotal();
        shippingShowHelper();
        showCalendar();

        jQuery.getJSON(_getData('saveshipurl'), agregateFormData('#onestepcheckout-shipping-methods'),
                function(data) {
                    if(data.error == 1) {
                        showTopError(data.message);
                    } else {
                        showTopError('');
                        jQuery('#onestepcheckout-payment-methods').html(data.payment);
                        jQuery('.onestepcheckout-totals-container').html(data.totals);
                        paymentShowHelper();
                        }

                }).error(function()
                        {
                    hideLoaderShipping();
                    hideLoaderPayment();
                    hideLoaderTotal();
                    goajax = 0;
                    alert("Ошибка запроса! Пожалуйста, обновите страницу и попробуйте еще раз."); }
                )
                .complete(function() {
                    hideLoaderPayment();
                    hideLoaderTotal();
                    enableRadioSelect();
                    goajax = 0;
                    }
            );
    }

    function savePaymentMethod()
    {
        if(goajax) return;

        goajax = 1;
        disableRadioSelect();
        paymentShowHelper();
        showLoaderTotal();

        jQuery.getJSON(_getData('savepayurl'), /*agregateFormData('#fast-payment')*/{"payment[method]": jQuery("#onestepcheckout-payment-methods input:radio:checked").val()},
            function(data) {
                    if(data.error == 1) {
                        showTopError(data.message);
                    } else {
                        showTopError('');
                        jQuery('.onestepcheckout-totals-container').html(data.totals);
                    }
        }).error(function()
                {
            hideLoaderShipping();
            hideLoaderPayment();
            hideLoaderTotal();
            goajax = 0;
            alert("Ошибка запроса! Пожалуйста, обновите страницу и попробуйте еще раз."); }
        )
        .complete(function() {
            hideLoaderTotal();
            enableRadioSelect();
            goajax = 0;
            }
        );
    }

    function addressRadioAction()
    {
        if(jQuery("#billing-new-address-form-register input:radio:checked").val() == '') {
            jQuery('#billing-new-address-form').show();
            jQuery("#billing-new-address-form-register input:radio").parent().removeClass('onestepcheckout-method-color-true');
            jQuery("#onestepcheckout-shipping-container input:radio:checked").removeAttr('checked');
        } else {
            jQuery('#billing-new-address-form').hide();
            jQuery("#billing-new-address-form-register input:radio").parent().removeClass('onestepcheckout-method-color-true');
            jQuery(this).parent().addClass('onestepcheckout-method-color-true');
        }
        reloadShippingMethod();
    }

    function loginAction()
    {
        if(goajax) return;
        goajax = 1;
        jQuery(".onestepcheckout-login-content input[name='login[password]']").addClass('ui-autocomplete-loading');
        jQuery.getJSON(_getData('loginaddressurl') ,
                {
                    "login[username]": jQuery(".onestepcheckout-login-content input[name='login[username]']").val(),
                    "login[password]": jQuery(".onestepcheckout-login-content input[name='login[password]']").val()
                },
                function(data) {
                    if(data.error == 1) {
                        showTopError(data.message);
                    } else {
                        showTopError('');
                        location.reload();
                    }
                }
                ).error(function()
                        {
                    hideLoaderShipping();
                    hideLoaderPayment();
                    hideLoaderTotal();
                    goajax = 0;
                    alert("Ошибка запроса! Пожалуйста, обновите страницу и попробуйте еще раз."); }
                )
                .complete(function() {
                    goajax = 0;
                    jQuery(".onestepcheckout-login-content input[name='login[password]']").removeClass('ui-autocomplete-loading');
                });
    }

    function showTopError(et_html)
    {
        if(et_html.length)
        {
            jQuery('#onestepcheckout-error-top').html('');
            jQuery('#onestepcheckout-error-top').show();
            jQuery('#onestepcheckout-error-top').append('<div class="alert alert-block alert-error fade in"><a class="close" data-dismiss="alert" href="#">&times;</a><p>'+et_html+'</p></div>');
            jQuery('#onestepcheckout-error-top .alert').alert();
            return false;
        } else {
            jQuery('#onestepcheckout-error-top .alert').alert('close');
            jQuery('#onestepcheckout-error-top').html('').hide();
        }
        return true;
    }

    function showError(e_obj)
    {
        if(e_obj.length) {
                jQuery('#onestepcheckout-error').html('');
                jQuery(e_obj).each(function(indx, element){
                    jQuery('#onestepcheckout-error').show();
                    jQuery('#onestepcheckout-error').append('<div class="alert alert-block alert-error fade in"><a class="close" data-dismiss="alert" href="#">&times;</a><p>'+element+'</p></div>');
                });
                jQuery('#onestepcheckout-error .alert').alert();
                return false;
            } else {
                jQuery('#onestepcheckout-error .alert').alert('close');
                jQuery('#onestepcheckout-error').html('').hide();
                jQuery('#co-billing-form .onestepcheckout-error-field').removeClass('onestepcheckout-error-field');
            }
        return true;
    }

    function showTopInfo(ei_html, ei_class)
    {
        if(ei_html.length) {
            jQuery('#onestepcheckout-error-top').html('');
            jQuery('#onestepcheckout-error-top').show();
            jQuery('#onestepcheckout-error-top').append('<div class="alert alert-block '+ ei_class +' fade in"><a class="close" data-dismiss="alert" href="#">&times;</a><p>'+ei_html+'</p></div>');
            jQuery('#onestepcheckout-error-top .alert').alert();
            return false;
        } else {
            jQuery('#onestepcheckout-error-top .alert').alert('close');
            jQuery('#onestepcheckout-error-top').html('').hide();
        }
        return true;
    }

    function couponAction(coupon_a)
    {
        if(!jQuery("#onestepcheckout-sert").val()) {
            jQuery("#onestepcheckout-sert").addClass("onestepcheckout-error-field");
            return ;
        }

        showLoaderTotal();
        if(goajax) return;
        goajax = 1;
        jQuery.getJSON(_getData('couponurl'),
                {
                    "coupon_code": jQuery("#onestepcheckout-sert").val(),
                    "remove": coupon_a
                },

                function(data) {
                    if(data.error == 1) {
                        showTopError(data.message);
                        jQuery("#onestepcheckout-sert").removeClass("onestepcheckout-success-field");
                        jQuery("#onestepcheckout-sert").addClass("onestepcheckout-error-field");
                    } else {
                        showTopError('');
                        jQuery('.onestepcheckout-totals-container').html(data.totals);

                        if(coupon_a == 0) {
                                if(data.success == 1) {
                                    jQuery("#onestepcheckout-sert").removeClass("onestepcheckout-error-field");
                                    jQuery("#onestepcheckout-sert").addClass("onestepcheckout-success-field");
                                    jQuery("#onestepcheckout-cancel-coupon").show();
                                    showTopInfo(data.message, 'alert-success');
                                } else {
                                    jQuery("#onestepcheckout-sert").removeClass("onestepcheckout-success-field");
                                }
                            }

                        if(coupon_a == 1) {
                                if(data.cancel == 1) {
                                jQuery("#onestepcheckout-sert").removeClass("onestepcheckout-error-field");
                                jQuery("#onestepcheckout-sert").removeClass("onestepcheckout-success-field");
                                jQuery("#fast-cancel-coupon").hide();
                                jQuery("#onestepcheckout-sert").val('');
                                showTopInfo(data.message, 'alert-info');
                                jQuery("#onestepcheckout-cancel-coupon").hide();
                                } else {
                                    jQuery("#onestepcheckout-sert").removeClass("onestepcheckout-success-field");
                                }
                            }
                        }
                }
                ).error(function()
                        {
                    hideLoaderShipping();
                    hideLoaderPayment();
                    hideLoaderTotal();
                    goajax = 0;
                    alert("Ошибка запроса! Пожалуйста, обновите страницу и попробуйте еще раз."); }
                )
                .complete(function() {
                    hideLoaderTotal();
                    goajax = 0;
                });
    }


    function paymentShowHelper()
    {
        jQuery("#checkout-payment-method-load div.onestepcheckout-method-color-true").removeClass("onestepcheckout-method-color-true");
        jQuery("#checkout-payment-method-load .form-list").hide();
        jQuery("#payment_form_"+jQuery("#checkout-payment-method-load input:radio:checked").val()).show();
        jQuery("#"+jQuery("#checkout-payment-method-load input:radio:checked").val()+"-method").addClass("onestepcheckout-method-color-true");
    }

    function shippingShowHelper()
    {
        jQuery('.onestepcheckout-shipping-container .onestepcheckout-method-color-true').removeClass('onestepcheckout-method-color-true');
        jQuery('.onestepcheckout-shipping-container input:radio:checked').parent().addClass('onestepcheckout-method-color-true');
    }

    function agregateFormData(id)
    {
        var filterData = new Object(); //объект с параметрами запроса

        jQuery(id + ' input.input-text:visible').each(function(){
            filterData[jQuery(this).attr('name')] = jQuery(this).val();
        });
        jQuery(id + ' select').each(function(){
            filterData[jQuery(this).attr('name')] = jQuery(this).val();
        });
        jQuery(id + ' textarea:visible').each(function(){
            filterData[jQuery(this).attr('name')] = jQuery(this).val();
        });
        jQuery(id + ' input.checkbox:visible:checked').each(function(){
            filterData[jQuery(this).attr('name')] = '1';
        });
        jQuery(id + " input[type='hidden']").each(function(){
            filterData[jQuery(this).attr('name')] = jQuery(this).val();
        });
        jQuery(id + " input.radio:checked").each(function(){
            filterData[jQuery(this).attr('name')] = jQuery(this).val();
        });
        jQuery(id + ' #billing-city').each(function(){
            filterData[jQuery(this).attr('name')] = jQuery(this).val();
//            console.log('City to server: ' + jQuery(this).val());
        });
        return filterData;
    }

    function prepareRegion()
    {
        jQuery('li.address-no-pickup').hide();
        if(jQuery("#billing-new-address-form select[name='billing[region_id]']").val() == '') {
                jQuery("#pickup-field").hide();
            } else {
                jQuery('li.' + getCurrentSelectedTab()).show();
                initTab(getCurrentSelectedTab());
                jQuery('li.address-no-pickup .input-text').val('');
                isHideCity();
//                reloadShippingMethod();
            }
    }

    function initTab(tab) {
        $data = _getData('tab_data');
        init_obj = $data[tab];
        jQuery('li .for-required').removeClass('required-entry required');
        jQuery.each(init_obj, function(indx, element){
              if(element == '2') {
                      jQuery('li.ons-' + indx + ' .for-required').addClass('required-entry required');
                  }
        });
        if(init_obj['pickup'] == '1') {
                jQuery("#pickup-field").show();
            } else {
                jQuery("#pickup-field").hide();
            }
        updateMetro(init_obj['metro_data']);
        checkPickup();
    }

    function updateMetro($metro)
    {
        if(typeof $metro != "undefined") {
            jQuery("select[name='billing[metro]']").empty();
            if($metro.length > 1 && _getData($metro).length) {
                jQuery("select[name='billing[metro]']").append(_getData($metro));
            }
        }
    }

    function checkPickup()
    {
        if(jQuery("#customer-pickup").is(':checked') && jQuery("#customer-pickup").is(':visible')) {
            jQuery('li.address-no-pickup').hide();
        } else {
            jQuery('li.address-no-pickup.'+ getCurrentSelectedTab()).show();
        }
    }

    function initValidateEvents() {
        //validate required field
        jQuery("#billing-new-address-form .required-entry:visible").on("focusout", function(){
                if(jQuery(this).val() == "" ) {
                    jQuery(this).addClass("onestepcheckout-error-field");
                } else {
                    jQuery(this).removeClass("onestepcheckout-error-field");
                }
        });
        //validate email
        jQuery("#billing-new-address-form .validate-email:visible").on("focusout", function(){
                if(isEmail(jQuery(this).val()) || (!jQuery(this).hasClass('required-entry') && jQuery(this).val() == '')) {
                    jQuery(this).removeClass("onestepcheckout-error-field");
                } else {
                    jQuery(this).addClass("onestepcheckout-error-field");
                }
        });
        //validate postcode
        jQuery("#billing-new-address-form .validate-zip-international.required-entry:visible").on("focusout", function(){
            if(jQuery(this).val().length > 5  && jQuery(this).val().length < 7 && jQuery(this).val() > 0) {
                    jQuery(this).removeClass("onestepcheckout-error-field");
                } else {
                    jQuery(this).addClass("onestepcheckout-error-field");
                }
        });
        //validate select focusout
        jQuery("#billing-new-address-form select.for-required.required-entry.validate-select:visible").on("focusout", function(){
            if(jQuery(this).val() == "") {
                jQuery(this).addClass("onestepcheckout-error-field");
            } else {
                jQuery(this).removeClass("onestepcheckout-error-field");
            }
        });
        //validate select change keyup
        jQuery("#billing-new-address-form select.for-required.required-entry.validate-select:visible").on("change keyup", function(){
            if(jQuery(this).val() == "") {
                jQuery(this).addClass("onestepcheckout-error-field");
            } else {
                jQuery(this).removeClass("onestepcheckout-error-field");
            }
        });

        jQuery("#billing-new-address-form select[name='billing[region_id]']").on("change", function(){
            if(jQuery(this).val() == "") {
                jQuery('#billing_region_id_chzn').addClass("onestepcheckout-error-field");
            } else {
                jQuery('#billing_region_id_chzn').removeClass("onestepcheckout-error-field");
            }
        });
    }

    function validateBeforeSave()
    {
        e_obj = new Array();
        onsWarningMsg = _getData('onsWarningMsg');
        //check address
        jQuery('#co-billing-form .required-entry:visible').each(function(){
            if(jQuery(this).val() == "" && !jQuery(this).hasClass('validate-email')) {
                    jQuery(this).addClass("onestepcheckout-error-field");
                    if(jQuery(this).attr('data-error')) {
                            e_obj.push(jQuery(this).attr('data-error'));
                        }
                }
            if(jQuery(this).hasClass('validate-email') && !isEmail(jQuery(this).val())) {
                    jQuery(this).addClass("onestepcheckout-error-field");
                    if(jQuery(this).attr('data-error')) {
                        e_obj.push(jQuery(this).attr('data-error'));
                    }
                }
        });

        if(jQuery('#billing_region_id_chzn').length && jQuery('#billing_region_id_chzn').is(':visible') &&
                jQuery("#billing-new-address-form select[name='billing[region_id]']").hasClass('required-entry') && !jQuery("#billing-new-address-form select[name='billing[region_id]']").val().length)
            {
                e_obj.push(jQuery("#billing-new-address-form select[name='billing[region_id]']").attr('data-error'));
                jQuery('#billing_region_id_chzn').addClass("onestepcheckout-error-field");
            }

        if(jQuery("#billing-new-address-form-register").length ) {
            if(!jQuery("#billing-new-address-form-register input:radio:checked").length) {
                e_obj.push(onsWarningMsg['check_address']);
            }
        }

        if(jQuery("#onestepcheckout-shipping-methods").length && !jQuery("#onestepcheckout-shipping-methods input:radio:checked").length) {
            e_obj.push(onsWarningMsg['check_shipping_method']);
        }

        jQuery('#checkout-agreements input:checkbox').each(function(){
            if(!jQuery(this).is(':checked')) {
                    e_obj.push(onsWarningMsg['check_agree']);
                    jQuery('#checkout-agreements .agree').addClass("onestepcheckout-error-field");
                }
        });

        if(!jQuery("#onestepcheckout-payment-methods input:radio:checked").length) {
            e_obj.push(onsWarningMsg['check_payment_method']);
        }

        if(jQuery('#checkout_method_in_address').is(':checked')) {
            if((jQuery('#register-customer-password .validate-password').val() != jQuery('#register-customer-password .validate-cpassword').val()) && jQuery('#register-customer-password .validate-password').val()) {
                e_obj.push('Пароли не совпадают!');
            }
            if(jQuery('#register-customer-password .validate-password').val().length < 6) {
                e_obj.push('Пароль должен состоять не менее чем из 6-ти символов!');
            }
        }

        /**************************pickpoint*******************************/
        if(typeof validatePostamat == 'function') {
            if(!validatePostamat()) {
                e_obj.push('Пожалуйста, выберите Постамат!');
            }
        }
        return showError(e_obj);
    }

    function createAutocomplite(id) {

        if(_getData('autoComplete') == 0) return ;

        jQuery('#'+id).autocomplete({
            source: _getData('autoCompleteUrl'),
            minLength: 4,
            select: function( event, ui ) {
                    setPostcode( ui.item ?
                            ui.item.index :
                            this.value
                            );
                    setCity( ui.item ?
                        ui.item.city :
                        this.value
                        );
                    setRegion( ui.item ?
                            ui.item.region :
                            this.value, ui.item.ems_region
                            );
                    focusInId("textarea[name='billing[street][]']");
                    reloadShippingMethod();
                    return false;
                },
                change: function( event, ui ) {
                    setPostcode( ui.item ?
                            ui.item.index :
                            this.value
                            );
                    setCity( ui.item ?
                        ui.item.city :
                        null
                        );
                    setRegion( ui.item ?
                            ui.item.region :
                            null, ui.item.ems_region
                            );
                    reloadShippingMethod();
                    return false;
                }
        });
    }

    function setCity(message) {
        if(_getData('city_autocomlete') == '1') {
            jQuery( "input[name='billing[city]']").val(message).removeClass("onestepcheckout-error-field");
        }
    }

    function setRegion(message, __ems_region) {
        if(_getData('region_autocomlete') == '1') {
            if(jQuery("input[name='billing[region]']").is(':visible')) {
                jQuery("input[name='billing[region]']").val(message);
            } else {
                jQuery("#billing-new-address-form select[name='billing[region_id]']").find("option[ems_location_code='" + __ems_region + "']").attr("selected", "selected").trigger("liszt:updated");
            }
        }
    }

    function setPostcode(message) {
        jQuery( "input[name='billing[postcode]']").val(message);
    }

    function focusInId(id) {
        jQuery(id).focus();
    }

    function getCurrentSelectedTab()
    {
        tab = jQuery("#billing-region_id  option:selected").attr('data-region-tab');
        if(!!!tab) {
            return _getData('init_tab');
        }
        return tab;
    }

    function showLoaderShipping()
    {
        jQuery('#onestepcheckout-shipping-methods .sp-methods').mask(_getData('loadingmsg'));
    }

    function hideLoaderShipping()
    {
        jQuery('#onestepcheckout-shipping-methods .sp-methods').unmask();
    }

    function showLoaderPayment()
    {
        jQuery('#checkout-payment-method-load').mask(_getData('loadingmsg'));
    }

    function hideLoaderPayment()
    {
        jQuery('#checkout-payment-method-load').unmask();
    }

    function showLoaderTotal()
    {
        jQuery('.onestepcheckout-totals-container').mask(_getData('loadingmsg'));
    }

    function hideLoaderTotal()
    {
        jQuery('.onestepcheckout-totals-container').unmask();
    }

    function disableRadioSelect()
    {
        jQuery('.onestepcheckout select').attr('disabled', 'disabled');
        jQuery('.onestepcheckout .checkbox').attr('disabled', 'disabled');
        jQuery('.onestepcheckout .radio').attr('disabled', 'disabled');
        jQuery('.chzn-select').trigger('liszt:updated');
    }

    function enableRadioSelect()
    {
        jQuery('.onestepcheckout select').removeAttr('disabled');
        jQuery('.onestepcheckout .checkbox').removeAttr('disabled');
        jQuery('.onestepcheckout .radio').removeAttr('disabled');
        jQuery('.chzn-select').trigger('liszt:updated');
    }

    function isHideCity()
    {
        if(!jQuery('#billing-region').is(':visible') && jQuery("#billing-region_id option:selected").attr('data-city') == '1' && jQuery('.ons-region').is(':visible')) {
            jQuery('#billing-city').val(jQuery("#billing-new-address-form select[name='billing[region_id]'] :selected").text());
//            console.log('City value: ' + jQuery("#billing-new-address-form select[name='billing[region_id]'] :selected").text());
            jQuery('.ons-city').hide();
        } else {
            if(jQuery("#customer-pickup").is(':checked') && jQuery("#customer-pickup").is(':visible')) {
                jQuery('.ons-city').hide();
            } else {
                jQuery('.ons-city').show();
            }
        }
    }

    function implode( glue, pieces ) {	// Join array elements with a string
        //
        // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +   improved by: _argos

        return ((pieces instanceof Array )?pieces.join(glue):pieces);
    }

    function isEmail(text)
    {
        reg = /[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/;
        if (text.match(reg)) {
                return true;
            }
        return false;
    }

    function _getData(param)
    {
        $this = $(document).data('onestepcheckout_options');
        return $this[param];
    }

    function showCalendar()
    {
        jQuery("div.shipping-picker").remove();
        jQuery("div.shipping-time").remove();
        jQuery("#datepicker").datepicker('destroy');

        if(typeof jQuery('#onestepcheckout-shipping-methods input:radio:checked').val() != 'undefined') {
            split = jQuery('#onestepcheckout-shipping-methods input:radio:checked').val().split('_');
        } else {
            split = new Array();
        }

        if(_getData('date_delivery') == '1' && typeof _getData('date_carrier')[split[0]] != 'undefined') {
            pickerDate = new Date( _getData('date_start') * 864E5 +(new Date).getTime());
            jQuery("#onestepcheckout-shipping-methods input:radio:checked").parent().append('<div class="shipping-picker">' + _getData('date_msg') + ': <br \/><input class="input-text" type="text" id="datepicker" name="datedelivery" \/><\/div>');
            jQuery("#datepicker").datepicker({
                defaultDate: 		pickerDate,
                minDate: 			pickerDate,
                maxDate: 			_getData('date_end'),
                showAnim: 			'slideDown',
                dateFormat: 		'dd MM yy',
                buttonImage: 		_getData('button_image'),
                showOn: 			"both",
                hideIfNoPrevNext: 	true,
                beforeShowDay: function (pickerDate) {
                if(_getData('date_delivery_str').search(new String(pickerDate.getDay())) != -1) {
                    return [true];
                }
                return [false];
            },
                onSelect: function(dateText, inst) {
                    dateText = $.datepicker.formatDate(
                        'dd MM yy',
                        new Date(inst.selectedYear, inst.selectedMonth, inst.selectedDay),
                        {monthNames: month}
                    );
                    inst.input.val(dateText);
                }
            });
            if(_getData('date_default') == '1') {
                jQuery("#datepicker").val(getFirstDate(pickerDate));
            }
        }

        if(_getData('time_delivery') == '1' && typeof _getData('date_carrier')[split[0]] != 'undefined') {
            jQuery("#onestepcheckout-shipping-methods input:radio:checked").parent().append('<div class="shipping-time">' + _getData('time_msg') + ': <br \/>' + time_select + '<\/div>');
        }

        return ;
    }

    function getFirstDate(pickerDate)
    {
        if(pickerDate.getDay() < 1) {
            dateText = $.datepicker.formatDate(
                    'dd MM yy',
                    new Date(1 * 864E5 + pickerDate.getTime()),
                    {monthNames: month}
                );
            return dateText;
        }
        dateText = $.datepicker.formatDate(
                'dd MM yy',
                pickerDate,
                {monthNames: month}
            );
        return dateText;
    }

    $.fn.onestepcheckout = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Метод ' + method + ' не существует');
        }
    };
})(jQuery);