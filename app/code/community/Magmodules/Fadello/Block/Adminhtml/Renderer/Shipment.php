<?php
/**
 * Magmodules.eu - http://www.magmodules.eu
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magmodules.eu so we can send you a copy immediately.
 *
 * @category      Magmodules
 * @package       Magmodules_Fadello
 * @author        Magmodules <info@magmodules.eu>
 * @copyright     Copyright (c) 2017 (http://www.magmodules.eu)
 * @license       http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magmodules_Fadello_Block_Adminhtml_Renderer_Shipment
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    /**
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $html = '';

        $orderId = $row->getEntityId();
        $status = $row->getFadelloStatus();

        $showAllShipments = Mage::getStoreConfig('shipping/fadello/show_all_shipments');
        if (!$showAllShipments) {
            if ($row->getShippingMethod() != 'fadello_fadello') {
                return $html;
            }
        }

        // Cancel Shipment
        $cUrl = $this->getUrl('*/fadello/cancelShipment', array('order_id' => $orderId));
        $cMsg = $this->__('Cancel this Fadello Shipment');
        $cImg = $this->getSkinUrl('images/fadello/close.png');

        // Open PDF
        $pUrl = $this->getUrl('*/fadello/getPdf', array('order_id' => $orderId));
        $pMsg = $this->__('Open PDF');
        $pImg = $this->getSkinUrl('images/fadello/pdf.png');

        // Ship Order
        $sUrl = $this->getUrl('*/fadello/shipOrder', array('order_id' => $orderId));
        $sMsg = $this->__('Ship This Order');
        $sImg = $this->getSkinUrl('images/fadello/export.png');

        // Ship Order
        $csUrl = $this->getUrl('*/fadello/createShipment', array('order_id' => $orderId));
        $csMsg = $this->__('Create Shipmentr');
        $csImg = $this->getSkinUrl('images/fadello/ship.png');

        if (!empty($status)) {
            if ($status == 'created') {
                $html .= '<a href="' . $cUrl . '"><img title="' . $cMsg . '" src="' . $cImg . '"></a>';
                $html .= '&nbsp; ';
                $html .= '<a href="' . $pUrl . '"><img title="' . $pMsg . '" src="' . $pImg . '"></a>';
                $html .= '&nbsp; ';
                $html .= '<a href="' . $sUrl . '"><img title="' . $sMsg . '" src="' . $sImg . '"></a>';
            }

            if ($status == 'shipped') {
                $html .= '<a href="' . $cUrl . '"><img title="' . $cMsg . '" src="' . $cImg . '"></a>';
                $html .= '&nbsp; ';
                $html .= '<a href="' . $pUrl . '"><img title="' . $pMsg . '" src="' . $pImg . '"></a>';
            }
        } else {
            $html .= '<a href="' . $csUrl . '"><img title="' . $csMsg . '" src="' . $csImg . '"></a>';
        }

        return $html;
    }

}