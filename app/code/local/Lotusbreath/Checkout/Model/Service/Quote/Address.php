<?php
class Lotusbreath_Checkout_Model_Service_Quote_Address extends Varien_Object {



    public function saveBillingAddress($data, $customerAddressId = null){
        if (empty($data)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid data.'));
        }

        if($this->getQuote()->getCheckoutMethod() == Mage_Checkout_Model_Type_Onepage::METHOD_GUEST){
            if(isset($data['customer_password']) && !$data['customer_password']){
                unset($data['confirm_password']);
                unset($data['customer_password']);
            }
        }
        $address = $this->getQuote()->getBillingAddress();
        $errors = $this->save($address, $data, $customerAddressId, 'billing');
        $address->save();
        return $errors;
    }

    public function saveShippingAddress($data, $customerAddressId = null){

        if (empty($data)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid data.'));
        }
        $address = $this->getQuote()->getShippingAddress();
        $errors = $this->save($address, $data, $customerAddressId, 'shipping');
        if( !$errors || count($errors) == 0 ){
            $usingCase = isset($data['use_for_shipping']) ? (int)$data['use_for_shipping'] : 0;
            $address->setSameAsBilling($usingCase)->save();
        }
        $address->save();
        return $errors;

    }

    public function save($address, $data, $customerAddressId, $type){
        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
            ->setEntityType('customer_address')
            ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());

        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => Mage::helper('checkout')->__('Customer Address is not valid.')
                    );
                }

                $address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
                $addressForm->setEntity($address);
                $addressErrors  = $addressForm->validateData($address->getData());
                if ($addressErrors !== true) {
                    return array('error' => 1, 'message' => $addressErrors);
                }
            }
        } else {
            $addressForm->setEntity($address);
            // emulate request object
            $addressData    = $addressForm->extractData($addressForm->prepareRequest($data));
            /**
             * We should set data for address before validation
             */
            $addressForm->compactData($addressData);
            $addressErrors  = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                return array('error' => 1, 'message' => array_values($addressErrors));
            }

            //unset billing address attributes which were not shown in form
            foreach ($addressForm->getAttributes() as $attribute) {
                if (!isset($data[$attribute->getAttributeCode()])) {
                    $address->setData($attribute->getAttributeCode(), NULL);
                }
            }
            $address->setCustomerAddressId(null);
            // Additional form data, not fetched by extractData (as it fetches only attributes)
            $address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
        }

        // set email for newly created user
        if (!$address->getEmail() && $this->getQuote()->getCustomerEmail()) {
            $address->setEmail($this->getQuote()->getCustomerEmail());
        }

        // validate billing address
        if (($validateRes = $address->validate()) !== true) {
            return array('error' => 1, 'message' => $validateRes);
        }

        $address->implodeStreetAddress();
        if($type == 'billing'
            //&& $this->getQuote()->getCheckoutMethod() != Mage_Checkout_Model_Type_Onepage::METHOD_GUEST
         ){
            if (true !== ($result = $this->_validateCustomerData($data))) {
                return $result;
            }

            if (!$this->getQuote()->getCustomerId() && Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()) {
                if ($this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId())) {
                    return array('error' => 1, 'message' => $this->_customerEmailExistsMessage);
                }
            }
        }
    }

    protected  function _customerEmailExists($email, $websiteId = null)
    {
        $customer = Mage::getModel('customer/customer');
        if ($websiteId) {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            return $customer;
        }
        return false;
    }

    protected function _validateCustomerData(array $data)
    {
        /** @var $customerForm Mage_Customer_Model_Form */
        $customerForm = Mage::getModel('customer/form');
        $customerForm->setFormCode('checkout_register')
            ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());

        $quote = $this->getQuote();
        if ($quote->getCustomerId()) {
            $customer = $quote->getCustomer();
            $customerForm->setEntity($customer);
            $customerData = $quote->getCustomer()->getData();
        } else {
            /* @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getModel('customer/customer');
            $customerForm->setEntity($customer);
            $customerRequest = $customerForm->prepareRequest($data);
            $customerData = $customerForm->extractData($customerRequest);
        }


        $customerErrors = $customerForm->validateData($customerData);
        if ($customerErrors !== true) {
            return array(
                'error'     => -1,
                'message'   => implode(', ', $customerErrors)
            );
        }

        if ($quote->getCustomerId()) {
            return true;
        }
        $customerForm->compactData($customerData);

        if ($quote->getCheckoutMethod() == Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER) {
            // set customer password
            $customer->setPassword($customerRequest->getParam('customer_password'));
            $customer->setPasswordConfirmation($customerRequest->getParam('confirm_password'));
            $customer->setConfirmation($customerRequest->getParam('confirm_password'));

        } else {
            //if(Mage::getVersion() >= '1.9.0'){
                // spoof customer password for guest
                $password = $customer->generatePassword();
                $customer->setPassword($password);
                $customer->setPasswordConfirmation($password);
                $customer->setConfirmation($password);
                // set NOT LOGGED IN group id explicitly,
                // otherwise copyFieldset('customer_account', 'to_quote') will fill it with default group id value
                $customer->setGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
           //}
        }

        $result = $customer->validate();
        if (true !== $result && is_array($result)) {
            return array(
                'error'   => -1,
                'message' => implode(', ', $result)
            );
        }

        if ($quote->getCheckoutMethod() == Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER) {
            // save customer encrypted password in quote
            $quote->setPasswordHash($customer->encryptPassword($customer->getPassword()));
        }
        // copy customer/guest email to address
        $quote->getBillingAddress()->setEmail($customer->getEmail());

        // copy customer data to quote
        Mage::helper('core')->copyFieldset('customer_account', 'to_quote', $customer, $quote);
        return true;
    }
}