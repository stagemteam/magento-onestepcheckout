<?php
class Lotusbreath_OneStepCheckout_Block_Checkout_Agreements extends Mage_Core_Block_Template {

    public function getAgreements()
    {
        if (!$this->hasAgreements()) {
            $agreements = Mage::getModel('checkout/agreement')->getCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addFieldToFilter('is_active', 1);
            $this->setAgreements($agreements);
        }
        return $this->getData('agreements');
    }
}