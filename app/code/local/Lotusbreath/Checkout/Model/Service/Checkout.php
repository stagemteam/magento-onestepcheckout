<?php
class Lotusbreath_Checkout_Model_Service_Checkout extends Varien_Object{

    protected $_processedJobs = array();

    /**
     * @return Lotus_Checkout_Model_Service_Quote_Address
     */
    protected function getQuoteAddressService(){
        $quoteAddressService =  Mage::getSingleton("lotus_checkout/service_quote_address");
        $quoteAddressService->setQuote($this->getQuote());
        return $quoteAddressService;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    protected function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * @return Mage_Core_Controller_Request_Http
     */
    protected function getRequest(){
        return Mage::app()->getRequest();
    }

    /**
     * @param $text
     * @return string
     */
    protected function __($text){
        return Mage::helper("lotusbreath_onestepcheckout")->__($text);
    }

    /**
     * @return Lotusbreath_OneStepCheckout_Model_Session
     */

    protected function getSession()
    {
        return Mage::getSingleton('lotusbreath_onestepcheckout/session');
    }



    public function saveBillingAddress()
    {
        $stepData = $this->getSession()->getStepData('osc_billing');
        if(isset($stepData['the_same']) && $stepData['the_same'] == true){
            return isset($stepData['errors']) ? $stepData['errors'] : false;
        }
        $billingAddressId = $this->getRequest()->getPost('billing_address_id', null);
        $data = $this->getRequest()->getPost('billing', null);
        $errors = $this->getQuoteAddressService()->saveBillingAddress($data, $billingAddressId);
        $success = true;
        if($errors){
            $success = false;
        }
        $stepData = $this->getSession()->getStepData('osc_billing');
        if($stepData == false)
            $stepData = array();
        $stepData = array_merge($stepData, array(
                'success' => $success,
                'previous_data' => $data,
                'errors' => $errors
            )
        );
        $this->getSession()->setStepData('osc_billing', $stepData);
        return $errors;
    }

    /**
     * @todo Save shipping address
     * @return bool|arrray
     */
    public function saveShippingAddress()
    {
        $stepData = $this->getSession()->getStepData('osc_shipping');

        if(isset($stepData['the_same']) && $stepData['the_same'] == true){
            return isset($stepData['errors']) ? $stepData['errors'] : false;
        }
        $shippingAddressId = $this->getRequest()->getPost('shipping_address_id', null);
        $data = $this->getRequest()->getPost('billing', null);
        $isUseForShipping = !empty($data['use_for_shipping']) ? $data['use_for_shipping'] : null;
        if ($isUseForShipping){
            $shippingData = $data;
        }else{
            $shippingData = $this->getRequest()->getPost('shipping', null);
        }

        $errors =  $this->getQuoteAddressService()->saveShippingAddress($shippingData, $shippingAddressId);
        $success = true;
        if($errors){
            $success = false;
        }
        $stepData = $this->getSession()->getStepData('osc_shipping');
        if($stepData == false)
            $stepData = array();

        $stepData = array_merge($stepData, array(
                'success' => $success,
                'previous_data' => $shippingData,
                'errors' => $errors
            )
        );
        $this->getSession()->setStepData('osc_shipping', $stepData);
        return $errors;
    }

    /**
     * @todo Process all jobs [shipping,billing,shipping_method,payment,totals calculationg]
     * @param $jobs
     * @return array
     */
    public function process($jobs = array()){
        $this->_processedJobs = array();
        $result = array();
        $this->getQuote()->setTotalsCollectedFlag(true);
        foreach ($jobs as $jobIdx => $jobParams){
            if($jobIdx == 'shipping_method'){
                if(array_key_exists('shipping', $jobs)){
                    if(!in_array('shipping', $this->_processedJobs)){
                        continue;
                    }
                }
            }
            $this->_processedJobs[] = $jobIdx;
            switch($jobIdx){
                case 'shipping_method':
                    $result['shipping_method'] = $this->saveShippingMethod();
                    break;
                case 'payment_method':
                    $result['payment'] = $this->savePayment();
                    break;
                case 'billing':
                    $result['billing'] = $this->saveBillingAddress();
                    break;
                case 'shipping':
                    $result['shipping'] = $this->saveShippingAddress();
                    break;
                case 'update_quote':
                    $result['quote'] = $this->updateQuote();
                    break;
            }
            $this->afterSaveProcessJob($jobIdx);

        }
        return $result;
    }

    /**
     * @todo Save shipping rate/method
     * @return array|bool
     */
    public function saveShippingMethod(){
        $shippingMethod = $this->getRequest()->getPost('shipping_method', '');
        if(empty($shippingMethod)){
            $groupRates = $this->getQuote()->getShippingAddress()->getGroupedAllShippingRates();
            if(count($groupRates) == 1){
                $_sole = count($groupRates) == 1;
                $_rates = $groupRates[key($groupRates)];
                $_sole = $_sole && count($_rates) == 1;
                if ($_sole){
                    $shippingMethod = reset($_rates)->getCode();
                }
            }
        }
        if (empty($shippingMethod)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid shipping method.'));
        }
        $rate = $this->getQuote()->getShippingAddress()->getShippingRateByCode($shippingMethod);
        if (!$rate) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid shipping method.'));
        }
        $this->getQuote()->getShippingAddress()
            ->setShippingMethod($shippingMethod);


        Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method',
            array('request' => $this->getRequest(),
                'quote' => $this->getQuote()));

        return true;
    }

    /**
     * @todo Save payment method
     * @return bool|array
     */
    public function savePayment(){
        $data = $this->getRequest()->getPost('payment');
        if (empty($data['method'])){
            return false;
        }
        try {
            $result = $this->getOnepage()->savePayment($data);
            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = 1;
            $result['message'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = 1;
            $result['message'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = 1;
            $result['message'] = $this->__('Unable to set Payment Method.');
        }
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }
        return $result;
    }

    /**
     * @todo Update quote and calculate all totals
     * @return bool
     */
    public function updateQuote(){
        $this->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
        $this->getQuote()->save();
        return true;
    }

    public function afterSaveProcessJob($jobIndex){
        if($jobIndex == 'shipping'){
            //collect shipping rates again
            $this->getQuote()->getShippingAddress()
                ->setCollectShippingRates(true)
                //->collectShippingRates()
                //->save()
            ;
        }
    }


    public function submitOrder()
    {

        try {

            $data = $this->getRequest()->getPost('payment', array());
            if ($data) {
                $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
                    | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
                    | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }

            //save comment
            if (Mage::getStoreConfig('lotusbreath_onestepcheckout/general/allowcomment')) {
                Mage::getSingleton('customer/session')->setOrderCustomerComment($this->getRequest()->getPost('order_comment'));
            }
            $this->subscribeNewsletter();
            $result = array();
            $dispatchParams = array(
                'data' => $this->getRequest()->getPost(),
                'result' => $result
            );

            Mage::dispatchEvent('lotus_checkout_submit_order_before',$dispatchParams);



            $this->getOnepage()->saveOrder();

            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error'] = false;

        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            $result['success'] = false;
            $result['error'] = true;
            if (!empty($message)) {
                $result['error_messages'] = $message;
            }
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();

            if ($gotoSection = $this->getOnepage()->getCheckout()->getGotoSection()) {
                $result['goto_section'] = $gotoSection;
                $this->getOnepage()->getCheckout()->setGotoSection(null);
            }

        } catch (Exception $e) {
            Mage::logException($e);
            //echo $e->getMessage();
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
        }
        $this->getOnepage()->getQuote()->save();
        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }
        return $result;
    }



    protected function subscribeNewsletter()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('newsletter')) {
            $customerSession = Mage::getSingleton('customer/session');

            if ($customerSession->isLoggedIn())
                $email = $customerSession->getCustomer()->getEmail();
            else {
                $bill_data = $this->getRequest()->getPost('billing');
                $email = $bill_data['email'];
            }
            try {
                if (!$customerSession->isLoggedIn() && Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1)
                    Mage::throwException($this->__('Sorry, subscription for guests is not allowed. Please <a href="%s">register</a>.', Mage::getUrl('customer/account/create/')));
                $ownerId = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email)->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId())
                    Mage::throwException($this->__('Sorry, you are trying to subscribe email assigned to another user.'));
                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                return $status;
            } catch (Mage_Core_Exception $e) {
                return false;
            }
            catch (Exception $e) {
                return false;
            }
        }
    }


}