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
?>
<?php
class Lotusbreath_OneStepCheckout_Controller_Action extends Mage_Core_Controller_Front_Action {

    public function preDispatch() {
        if(!Mage::getStoreConfig('lotusbreath_onestepcheckout/general/enabled')){
            $this->_redirect(Mage::getUrl('checkout/onepage/index'));
        }

        /**
         * Disable some event for optimization
         */
        if(!Mage::getStoreConfig('lotusbreath_onestepcheckout/speedoptimizer/disablerssobserver')){
            $eventConfig = Mage::getConfig()->getEventConfig('frontend', 'sales_order_save_after');
            if ($eventConfig->observers->notifystock->class == 'rss/observer')
                $eventConfig->observers->notifystock->type = 'disabled';
            if ($eventConfig->observers->ordernew->class == 'rss/observer')
                $eventConfig->observers->ordernew->type = 'disabled';
        }

        if(!Mage::getStoreConfig('lotusbreath_onestepcheckout/speedoptimizer/disablevisitorlog')){
            /*
            $eventConfig = Mage::getConfig()->getEventConfig('frontend', 'controller_action_predispatch');
            $eventConfig->observers->log->type = 'disabled';
            $eventConfig = Mage::getConfig()->getEventConfig('frontend', 'controller_action_postdispatch');
            $eventConfig->observers->log->type = 'disabled';
            $eventConfig = Mage::getConfig()->getEventConfig('frontend', 'sales_quote_save_after');
            $eventConfig->observers->log->type = 'disabled';
            $eventConfig = Mage::getConfig()->getEventConfig('frontend', 'checkout_quote_destroy');
            $eventConfig->observers->log->type = 'disabled';
            */
        }
        parent::preDispatch();
        if (!$this->getRequest()->getParam('allow_gift_messages')){
            $this->getRequest()->setParam('giftmessage', false);
        }
        return $this;
        
    }

}
?>
