<?php
/*
Lotus Breath - One Step Checkout
Copyright (C) 2014  Lotus Breath
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
class Lotusbreath_OneStepCheckout_Block_Onepage_Js extends Mage_Checkout_Block_Onepage_Abstract {

    protected $_billingAddress = null;
    protected $_shippingAddress = null;
    public function getBillingAddress()
    {
        if (is_null($this->_billingAddress)) {
            if ($this->isCustomerLoggedIn()) {
                $this->_billingAddress = $this->getQuote()->getBillingAddress();
                if(!$this->_billingAddress->getFirstname()) {
                    $this->_billingAddress->setFirstname($this->getQuote()->getCustomer()->getFirstname());
                }
                if(!$this->_billingAddress->getLastname()) {
                    $this->_billingAddress->setLastname($this->getQuote()->getCustomer()->getLastname());
                }
            } else {
                $this->_billingAddress = Mage::getModel('sales/quote_address');
            }
        }

        return $this->_billingAddress;
    }
    public function getShippingAddress()
    {
        if (is_null($this->_shippingAddress)) {
            if ($this->isCustomerLoggedIn()) {
                $this->_shippingAddress = $this->getQuote()->getBillingAddress();
                if(!$this->_shippingAddress->getFirstname()) {
                    $this->_shippingAddress->setFirstname($this->getQuote()->getCustomer()->getFirstname());
                }
                if(!$this->_shippingAddress->getLastname()) {
                    $this->_shippingAddress->setLastname($this->getQuote()->getCustomer()->getLastname());
                }
            } else {
                $this->_shippingAddress = Mage::getModel('sales/quote_address');
            }
        }
        return $this->_shippingAddress;
    }

}