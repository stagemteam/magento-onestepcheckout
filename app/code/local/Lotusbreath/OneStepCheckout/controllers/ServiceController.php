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
class Lotusbreath_OneStepCheckout_ServiceController extends Mage_Core_Controller_Front_Action
{
    public function getGeoIpAction(){
        $data = array();

        /*
         * $region = Mage::getModel('directory/region')->load($regionId);
            if ($region->getId()) {
                return $region->getCode();
            }
         */
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $json = file_get_contents("http://ip-api.com/json/$ip");
        try{
            $data = (array)json_decode($json);
            $data = array(
                'country_code' => !empty($data['countryCode']) ? $data['countryCode'] : '',
                'region_code' => !empty($data['region']) ? $data['region'] : '' ,
                'region_name' => !empty($data['regionName']) ? $data['regionName'] : '' ,
                'city' => !empty($data['city']) ? $data['city'] : '' ,
                'zip' => !empty($data['zip']) ? $data['zip'] : '' ,
            );
        }catch (Exception $ex){

        }

        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($data));
    }
}