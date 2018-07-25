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
class Lotusbreath_OneStepCheckout_Model_Adminhtml_System_Config_Source_Locationfields {
    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => Mage::helper('lotusbreath_onestepcheckout')->__('None choose') ),
            array('value' => 'country_id', 'label' => Mage::helper('lotusbreath_onestepcheckout')->__('Country') ),
            array('value' => 'postcode', 'label' => Mage::helper('lotusbreath_onestepcheckout')->__('Post code/Zip code') ),
            array('value' => 'region_id', 'label' => Mage::helper('lotusbreath_onestepcheckout')->__('Region') ),
            array('value' => 'city', 'label' => Mage::helper('lotusbreath_onestepcheckout')->__('City') ),
        );
    }
}
