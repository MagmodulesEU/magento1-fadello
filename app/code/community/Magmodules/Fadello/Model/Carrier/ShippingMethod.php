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

class Magmodules_Fadello_Model_Carrier_ShippingMethod extends Mage_Shipping_Model_Carrier_Abstract
{

    protected $_code = 'fadello';

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return mixed
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {

        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if (!Mage::getStoreConfig('shipping/fadello/enabled')) {
            return false;
        }

        if ($maxWeight = Mage::getStoreConfig('carriers/fadello/weight')) {
            if ($request->getPackageWeight() > $maxWeight) {
                return false;
            }
        }

        $active = Mage::helper('fadello')->isActive();
        if (!$active) {
            return false;
        }

        $postcode = $request->getDestPostcode();
        if (!empty($postcode)) {
            $postcodeCheck = Mage::helper('fadello')->checkPostcode($request->getDestPostcode());
            if (!$postcodeCheck) {
                return false;
            }
        }

        foreach ($request->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            $stockQty = $item->getProduct()->getStockItem()->getQty();
            if ($item->getQty() > $stockQty) {
                return false;
            }
        }

        $prices = @unserialize($this->getConfigData('shipping_price'));
        $total = $request->getBaseSubtotalInclTax();
        $shippingCost = '0.00';

        foreach ($prices as $shippingPrice) {
            if (($total >= $shippingPrice['from']) && ($total <= $shippingPrice['to'])) {
                $shippingCost = $shippingPrice['cost'];
            }
        }

        $result = Mage::getModel('shipping/rate_result');
        $method = Mage::getModel('shipping/rate_result_method');
        $name = Mage::getStoreConfig('carriers/fadello/name');

        $method->setCarrier('fadello');
        $method->setCarrierTitle($name);
        $method->setMethod('fadello');
        $method->setMethodTitle($active['title']);
        $method->setPrice($shippingCost);
        $method->setCost('0.00');
        $result->append($method);
        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('name'));
    }

}