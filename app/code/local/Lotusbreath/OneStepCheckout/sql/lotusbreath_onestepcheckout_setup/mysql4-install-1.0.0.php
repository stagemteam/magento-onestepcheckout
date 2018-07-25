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
$installer = $this;
$installer->startSetup();
$stores = Mage::getModel('core/store')->getCollection()->addFieldToFilter('store_id', array('gt' => 0))->getAllIds();
$stores = array(0);
$staticBlocks = array(
    array(
        'name' => 'onestepcheckout_top_block',
        'content' => '<div style="border : 1px solid; padding: 20px; margin-top: 20px;">
        <p> Onestepcheckout - Top Static Block. Edit content of this block from Admin panel > CMS > Static Blocks.
        Hide it by set value of the config [Admin panel > Configuration > Lotusbreath > OnstepCheckout > Content > Display top static block] to "No"
        </p></div>',
        'title' => 'Onestepcheckout - Top Static Block'
    ),
    array(
        'name' => 'onestepcheckout_bottom_block',
        'content' => '<div style="border : 1px solid; padding: 20px; margin-top: 20px;">
        <p> Onestepcheckout - Bottom Static Block. Edit content of this block from Admin panel > CMS > Static Blocks.
        Hide it by set value of  the config [Admin panel > Configuration > Lotusbreath > OnstepCheckout > Content > Display bottom static block] to "No"

        </p></div>',
        'title' => 'Onestepcheckout - Bottom Static Block'
    ),
);
foreach ($staticBlocks as $block) {
    $blockName = $block['name'];
    $blockContent = $block['content'];
    $blockTitle = $block['title'];
    if (!Mage::getModel('cms/block')->getCollection()
        ->addFieldToFilter('identifier', $blockName)
        ->count()
    ) {
        foreach ($stores as $store) {
            $block = Mage::getModel('cms/block');
            $block->setTitle($blockTitle);
            $block->setIdentifier($blockName);
            $block->setStores(array($store));
            $block->setIsActive(1);
            $block->setContent($blockContent);
            $block->save();
        }
    }
}
$installer->endSetup();
