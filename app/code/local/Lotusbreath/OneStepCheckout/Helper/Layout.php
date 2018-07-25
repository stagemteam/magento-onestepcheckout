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
class Lotusbreath_OneStepCheckout_Helper_Layout extends Mage_Checkout_Helper_Data{

    public function switchTemplate(){
        $layout = Mage::getStoreConfig('lotusbreath_onestepcheckout/layout/layout');
        switch ($layout){
            case '2cols':
                return 'lotusbreath/onestepcheckout/onepage.phtml';
                break;
            case '3cols':
                return 'lotusbreath/onestepcheckout/onepage_3columns.phtml';
                break;
            default:
                return 'lotusbreath/onestepcheckout/onepage.phtml';
                break;
        }
    }
}