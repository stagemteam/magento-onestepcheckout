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
class Lotusbreath_OneStepCheckout_Model_Session extends Mage_Checkout_Model_Session{


    public function compareAll(){
        $steps = array('osc_billing','osc_shipping','osc_shipping_method', 'osc_payment');
        foreach($steps as $step){
            $this->compareRequestData($step);
        }
        //return $steps;
    }

    public function compareRequestData($step){
        $stepData = $this->getStepData($step);
        $requestData = $this->getRequestData($step);
        $compareResult = false;
        if($requestData && $stepData){
            $previous = isset($stepData['previous_data']);
            if(is_array($requestData) && is_array($previous)){
                $diff = array_diff($requestData, $previous);
                if($step == 'osc_shipping'){
                    //print_r($requestData);
                    //print_r($previous);
                }
                if(count($diff) == 0){
                    $compareResult =  true;
                }
            }else{
                if(is_string($requestData))
                    if($previous === $requestData)
                        $compareResult =  true;
            }
        }
        if($stepData == false){
            $stepData = array();
        }
        $stepData['the_same'] = $compareResult;
        $this->setStepData($step, $stepData);
        return $compareResult;

    }

    public function getRequestData($step){
        switch($step){
            case 'osc_billing':
                return Mage::app()->getRequest()->getParam('billing');
                break;
            case 'osc_shipping':
                $data = Mage::app()->getRequest()->getParam('billing');
                $isUseForShipping = !empty($data['use_for_shipping']) ? $data['use_for_shipping'] : null;
                if($isUseForShipping){
                    return Mage::app()->getRequest()->getParam('billing');
                }else{
                    return Mage::app()->getRequest()->getParam('shipping');
                }
                break;
            case 'osc_shipping_method':
                return Mage::app()->getRequest()->getParam('shipping_method');
                break;
            case 'osc_payment':
                return Mage::app()->getRequest()->getParam('payment');
                break;
        }
        return false;
    }
}