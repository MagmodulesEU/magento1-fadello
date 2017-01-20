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

class Magmodules_Fadello_Adminhtml_FadelloController extends Mage_Adminhtml_Controller_Action
{

    /**
     *
     */
    public function createShipmentAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId > 0) {
            $result = Mage::getModel('fadello/api')->createShipment($orderId);
            if (!empty($result['success_msg'])) {
                Mage::getSingleton('core/session')->addSuccess($result['success_msg']);
            }

            if (!empty($result['error_msg'])) {
                Mage::getSingleton('core/session')->addError($result['error_msg']);
            }
        } else {
            $msg = $this->__('Order not found!');
            Mage::getSingleton('core/session')->addError($msg);
        }

        $this->_redirect('adminhtml/sales_order');
    }

    /**
     *
     */
    public function shipOrderAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId > 0) {
            $result = Mage::getModel('fadello/api')->shipOrder($orderId);
            if (!empty($result['success_msg'])) {
                Mage::getSingleton('core/session')->addSuccess($result['success_msg']);
            }

            if (!empty($result['error_msg'])) {
                Mage::getSingleton('core/session')->addError($result['error_msg']);
            }
        } else {
            $msg = $this->__('Order not found!');
            Mage::getSingleton('core/session')->addError($msg);
        }

        $this->_redirect('adminhtml/sales_order');
    }

    /**
     *
     */
    public function cancelShipmentAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $magento = $this->getRequest()->getParam('magento');
        if ($orderId > 0) {
            $result = Mage::getModel('fadello/api')->cancelShipment($orderId, $magento);
            if (!empty($result['success_msg'])) {
                Mage::getSingleton('core/session')->addSuccess($result['success_msg']);
            }

            if (!empty($result['error_msg'])) {
                Mage::getSingleton('core/session')->addError($result['error_msg']);
            }
        } else {
            $msg = $this->__('Order not found!');
            Mage::getSingleton('core/session')->addError($msg);
        }

        $this->_redirect('adminhtml/sales_order');
    }

    /**
     *
     */
    public function getPdfAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId > 0) {
            $result = Mage::getModel('fadello/api')->getPdf($orderId);
            if (!empty($result['label_url']) && !empty($result['file_name'])) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename=' . $result['file_name']);
                header('Pragma: no-cache');
                readfile($result['label_url']);
                exit;
            } else {
                if (!empty($result['error_msg'])) {
                    Mage::getSingleton('core/session')->addError($result['error_msg']);
                }
            }
        } else {
            $msg = $this->__('Order not found!');
            Mage::getSingleton('core/session')->addError($msg);
        }

        $this->_redirect('adminhtml/sales_order');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }

}