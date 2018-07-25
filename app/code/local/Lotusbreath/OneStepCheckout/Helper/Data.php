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
class Lotusbreath_OneStepCheckout_Helper_Data extends Mage_Checkout_Helper_Data
{
    const XML_PATH_VAT_FRONTEND_VISIBILITY = 'customer/create_account/vat_frontend_visibility';

    public function getExtensionVersion()
    {
        return (string) @Mage::getConfig()->getNode()->modules->Lotusbreath_OneStepCheckout->version;
    }


    public function getSubmitUrl()
    {
        return $this->_getUrl('lotusbreath_onestepcheckout/index/savePost', array('_secure' => true));
    }

    public function getSaveStepUrl()
    {
        return $this->_getUrl('lotusbreath_onestepcheckout/index/saveStep', array('_secure' => true));
    }

    /**
     * For compatible with 1.4
     * @param $field
     * @return string
     */
    public function getAttributeValidationClass($attributeCode){
        $customerHelper = Mage::helper('customer/address');

        if(method_exists($customerHelper, 'getAttributeValidationClass')){
            return $customerHelper->getAttributeValidationClass($attributeCode);
        }else{
            $attribute = isset($this->_attributes[$attributeCode]) ? $this->_attributes[$attributeCode]
                : Mage::getSingleton('eav/config')->getAttribute('customer_address', $attributeCode);
            $class = $attribute ? $attribute->getFrontend()->getClass() : '';

            if (in_array($attributeCode, array('firstname', 'middlename', 'lastname', 'prefix', 'suffix', 'taxvat'))) {
                if ($class && !$attribute->getIsVisible()) {
                    $class = ''; // address attribute is not visible thus its validation rules are not applied
                }


                /** @var $customerAttribute Mage_Customer_Model_Attribute */
                $customerAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', $attributeCode);
                $class .= $customerAttribute && $customerAttribute->getIsVisible()
                    ? $customerAttribute->getFrontend()->getClass() : '';
                $class = implode(' ', array_unique(array_filter(explode(' ', $class))));
            }


            return $class;
        }

    }

    public function isVatAttributeVisible()
    {
        $customerHelper = Mage::helper('customer/address');
        if(method_exists($customerHelper, 'isVatAttributeVisible')){
            return $customerHelper->isVatAttributeVisible();
        }else{
            return (bool)Mage::getStoreConfig(self::XML_PATH_VAT_FRONTEND_VISIBILITY);
        }

    }

    public function getGeoIpUrl(){
        return $this->_getUrl('lotusbreath_onestepcheckout/service/getGeoIp', array('_secure' => true));
    }
}