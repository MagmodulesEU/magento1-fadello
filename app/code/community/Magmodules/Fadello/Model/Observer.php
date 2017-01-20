<?php
/**
 * *
 *  * Magmodules.eu - http://www.magmodules.eu
 *  *
 *  * NOTICE OF LICENSE
 *  * This source file is subject to the Open Software License (OSL 3.0)
 *  * that is bundled with this package in the file LICENSE.txt.
 *  * It is also available through the world-wide-web at this URL:
 *  * http://opensource.org/licenses/osl-3.0.php
 *  * If you did not receive a copy of the license and are unable to
 *  * obtain it through the world-wide-web, please send an email
 *  * to info@magmodules.eu so we can send you a copy immediately.
 *  *
 *  * @category      Magmodules
 *  * @package       Magmodules_Fadello
 *  * @author        Magmodules <info@magmodules.eu>
 *  * @copyright     Copyright (c) 2017 (http://www.magmodules.eu)
 *  * @license       http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */

class Magmodules_Fadello_Model_Observer
{

    /**
     * @param $observer
     */
    public function addDataToOrderGrid($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block->getType() == 'adminhtml/sales_order_grid') {
            if (Mage::getStoreConfig('shipping/fadello/enabled')) {
                $block->addColumnAfter(
                    'fadello', array(
                    'header'   => 'Fadello',
                    'type'     => 'text',
                    'index'    => 'fadello',
                    'filter'   => false,
                    'sortable' => false,
                    'renderer' => 'Magmodules_Fadello_Block_Adminhtml_Renderer_Shipment',
                    'width'    => '120px',
                    ), 'status'
                );
            }
        }
    }

    /**
     * @param $observer
     */
    public function core_block_abstract_to_html_after($observer)
    {
        if ($observer->getBlock() instanceof Mage_Checkout_Block_Onepage_Shipping_Method_Available) {
            $enabled = Mage::getStoreConfig('carriers/fadello/active');
            $logoStyle = Mage::getStoreConfig('carriers/fadello/logo_style');
            if ($enabled && $logoStyle) {
                $html = $observer->getTransport()->getHtml();
                $header = '<dt>' . Mage::getStoreConfig('carriers/fadello/title') . '</dt>';
                $headerNew = '<dt class="fadello-class">' . Mage::getStoreConfig('carriers/fadello/title') . '</dt>';
                $html = str_replace($header, $headerNew, $html);
                $observer->getTransport()->setHtml($html);
            }
        }
    }

}