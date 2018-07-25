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
class Lotusbreath_OneStepCheckout_Model_Observer {

    const CONFIG_ENABLE_MODULE = 'lotusbreath_onestepcheckout/general/enabled';

    public function addHistoryComment($data)
    {
        if(Mage::getStoreConfig(self::CONFIG_ENABLE_MODULE)){
            if(Mage::getStoreConfig('lotusbreath_onestepcheckout/general/allowcomment')){
                $comment	= Mage::getSingleton('customer/session')->getOrderCustomerComment();
                $comment	= trim($comment);
                if (!empty($comment))
                    if(!empty($data['order'])){
                        $order = $data['order'];
                        $order->addStatusHistoryComment($comment)->setIsVisibleOnFront(true)->setIsCustomerNotified(false);
                        $order->setCustomerComment($comment);
                        $order->setCustomerNoteNotify(true);
                        $order->setCustomerNote($comment);
                    }
            }
        }
        return $this;
    }

    public function redirectToOnestepcheckout($observer){
        $isRedirectAfterAddToCart = Mage::getStoreConfig('lotusbreath_onestepcheckout/general/redirect_to_afteraddtocart');
        if ($isRedirectAfterAddToCart){
            $url = Mage::getUrl('lotusbreath_onestepcheckout');
            $observer->getEvent()->getResponse()->setRedirect($url);
            Mage::getSingleton('checkout/session')->setNoCartRedirect(true);
        }

    }
}