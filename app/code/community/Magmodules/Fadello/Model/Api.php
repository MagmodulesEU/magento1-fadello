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

class Magmodules_Fadello_Model_Api extends Mage_Core_Helper_Abstract
{

    /**
     * @param $orderId
     *
     * @return array
     */
    public function createShipment($orderId)
    {
        $result = array();
        $order = Mage::getModel('sales/order')->load($orderId);
        $storeId = $order->getStoreId();
        $config = Mage::helper('fadello')->getConfig($storeId, 1);

        if (empty($config)) {
            $result['status'] = 'Error';
            $result['error_msg'] = $this->__('Missing API details in Admin');
            return $result;
        }

        if (empty($order)) {
            $result['status'] = 'Error';
            $result['error_msg'] = $this->__('Could not find Order');
            return $result;
        }

        $post = json_encode($this->getPostOrderArray($config, $order));

        $request = curl_init();
        $requestUrl = $config['url'] . 'postOrder?' . $config['url_params'];
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($request, CURLOPT_URL, $requestUrl);
        curl_setopt($request, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_POSTFIELDS, $post);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($request);
        $apiResult = json_decode($content, true);

        if (!empty($apiResult['Status'])) {
            if ($apiResult['Status'] == 'OK') {
                $transId = $apiResult['TransID'];
                $deliverId = $apiResult['TransDeliverID'][0]['Deliver1'];
                $barcode = $apiResult['TransDeliverID'][0]['Barcode1'];
                $status = 'created';
                
                $order->setFadelloTransId($transId)
                    ->setFadelloDeliverId($deliverId)
                    ->setFadelloBarcode($barcode)
                    ->setFadelloStatus($status)
                    ->save();
                
                $url = Mage::helper("adminhtml")->getUrl('*/fadello/getPdf', array('order_id' => $orderId));
                $result['status'] = 'Success';
                $result['success_msg'] = $this->__(
                    'Fadello shipment created for order, %s',
                    '<a href="' . $url . '">Download PDF</a>'
                );
                return $result;
            } else {
                $result['status'] = $apiResult['Status'];
                $result['error_msg'] = 'Fadello API: ' . $apiResult['Message'];
                return $result;
            }
        } else {
            $result['status'] = 'Error';
            $result['error_msg'] = $this->__('General error in API call');
            return $result;
        }
    }

    /**
     * @param     $orderId
     * @param int $magento
     *
     * @return array
     */
    public function cancelShipment($orderId, $magento = 0)
    {
        $result = array();
        $order = Mage::getModel('sales/order')->load($orderId);
        $storeId = $order->getStoreId();
        $config = Mage::helper('fadello')->getConfig($storeId, 0);

        if (empty($config)) {
            $result['status'] = 'Error';
            $result['error_msg'] = $this->__('Missing API details in Admin');
            return $result;
        }

        if (empty($order)) {
            $result['status'] = 'Error';
            $result['error_msg'] = $this->__('Could not find Order');
            return $result;
        }

        $transId = $order->getFadelloTransId();
        if (empty($transId)) {
            $result['status'] = 'Error';
            $result['error_msg'] = $this->__('TransID Missing');
            return $result;
        }

        $request = curl_init();
        $requestUrl = $config['url'] . 'cancelOrder?' . $config['url_params'] . '&transID=' . $transId;
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($request, CURLOPT_URL, $requestUrl);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($request);
        $apiResult = json_decode($content, true);

        if (!empty($apiResult['Status'])) {
            if ($apiResult['Status'] == 'OK') {
                if (!empty($apiResult['ErrorCode'])) {
                    $result['status'] = 'Error';
                    $result['error_msg'] = 'Fadello API: ' . $apiResult['Message'];
                    return $result;
                } else {
                    $order->setFadelloTransId('')->setFadelloDeliverId('')->setFadelloBarcode('')->setFadelloStatus('');
                    if ($magento == 1) {
                        $shipments = $order->getShipmentsCollection();
                        foreach ($shipments as $shipment) {
                            $shipment->delete();
                        }

                        $items = $order->getAllVisibleItems();
                        foreach ($items as $i) {
                            $i->setQtyShipped(0);
                            $i->save();
                        }

                        $order->setData('state', 'processing')->setStatus('processing');
                    }

                    $order->save();
                    $result['status'] = 'Success';
                    $result['success_msg'] = $apiResult['Message'];
                    return $result;
                }
            } else {
                $result['status'] = $apiResult['Status'];
                $result['error_msg'] = $apiResult['Message'];
                return $result;
            }
        } else {
            $result['status'] = 'Error';
            $result['error_msg'] = $this->__('General error in API call');
            return $result;
        }
    }

    /**
     * @param $orderId
     *
     * @return array
     */
    public function getPdf($orderId)
    {
        $order = Mage::getModel('sales/order')->load($orderId);
        $storeId = $order->getStoreId();
        $config = Mage::helper('fadello')->getConfig($storeId, 0);
        $result = array();

        if (empty($config)) {
            $result['status'] = 'Error';
            $result['error_msg'] = $this->__('Missing API details in Admin');
            return $result;
        }

        if (empty($order)) {
            $result['status'] = 'Error';
            $result['error_msg'] = $this->__('Could not find Order');
            return $result;
        }

        $transId = $order->getFadelloTransId();
        $transDeliverId = $order->getFadelloDeliverId();
        if (empty($transId) || empty($transDeliverId)) {
            $result['status'] = 'Error';
            $result['error_msg'] = $this->__('Trans Data Missing');
            return $result;
        }

        $request = curl_init();
        $requestUrl = $config['url'] . 'getLabel?' . $config['url_params'] . '&transID=' . $transId . '&transDeliverID=' . $transDeliverId . '&Lformat=' . $config['label'];
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($request, CURLOPT_URL, $requestUrl);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($request);
        $apiResult = json_decode($content, true);

        if (!empty($apiResult['Status'])) {
            if ($apiResult['Status'] == 'OK') {
                $labelUrl = $apiResult['Labelurl'];
                $result['status'] = 'Success';
                $result['increment_id'] = $order->getIncrementId();
                $result['label_url'] = $labelUrl;
                $result['file_name'] = 'Fadello-' . $order->getIncrementId() . '.pdf';
                return $result;
            } else {
                $result['status'] = $apiResult['Status'];
                $result['error_msg'] = $apiResult['Message'];
                return $result;
            }
        } else {
            $result['status'] = 'Error';
            $result['error_msg'] = $this->__('General error in API call');
            return $result;
        }
    }

    /**
     * @param $orderId
     *
     * @return array
     */
    public function shipOrder($orderId)
    {
        $order = Mage::getModel('sales/order')->load($orderId);
        $barcode = $order->getFadelloBarcode();
        $result = array();

        if (empty($order)) {
            $result['status'] = 'Error';
            $result['error_msg'] = $this->__('Could not find Order');
            return $result;
        }

        if (empty($barcode)) {
            $result['status'] = 'Error';
            $result['error_msg'] = $this->__('Could not find Track & Trace info');
            return $result;
        }

        $shipments = $order->getShipmentsCollection();
        if (count($shipments)) {
            $result['status'] = 'Error';
            $result['error_msg'] = $this->__('Order %s allready shipped', $order->getInrementId());
            return $result;
        }

        try {
            $name = Mage::getStoreConfig('carriers/fadello/name');
            $shipment = $order->prepareShipment();
            $arrTracking = array('carrier_code' => 'fadello', 'title' => $name, 'number' => $barcode);
            $track = Mage::getModel('sales/order_shipment_track')->addData($arrTracking);
            $shipment->addTrack($track);
            $shipment->register();
            $shipment->sendEmail(true);
            $shipment->setEmailSent(true);
            $shipment->getOrder()->setIsInProcess(true);
            Mage::getModel('core/resource_transaction')->addObject($shipment)->addObject($shipment->getOrder())->save();
            unset($shipment);
        } catch (Exception $e) {
            $result['status'] = 'Error';
            $result['error_msg'] = $e->getMessage();
            return $result;
        }

        $order->setData('state', "complete")->setStatus("complete")->setFadelloStatus('shipped')->save();
        $result['status'] = 'Success';
        $result['success_msg'] = $this->__('Order %s shipped and completed', $order->getInrementId());
        return $result;
    }

    /**
     * @param $config
     * @param $order
     *
     * @return array
     */
    public function getPostOrderArray($config, $order)
    {
        $post = array();
        $post['Name'] = $config['pu_name'];
        $post['Phone'] = $config['pu_phone'];
        $post['YourRef'] = $order->getIncrementId() . time();
        $post['Note'] = '';
        $post['Email'] = $config['pu_email'];
        $post['ShipType'] = $config['ship_type'];
        $post['PUname'] = $config['pu_name'];
        $post['PUstreet'] = $config['pu_street'];
        $post['PUpostalcode'] = $config['pu_postalcode'];
        $post['PUhomeno'] = $config['pu_homeno'];
        $post['PUhomenoAdd'] = $config['pu_homeno_add'];
        $post['PUcity'] = $config['pu_city'];
        $post['PUcountry'] = $config['pu_country'];
        $post['PUphone'] = $config['pu_phone'];
        $post['PUemail'] = $config['pu_email'];
        $post['PUdate'] = $config['pu_date'];
        $post['PUtime'] = $config['pickup_time'];
        $post['PUnote'] = '';
        $post['Deliver'][] = $this->getDeliveryData($config, $order);
        return $post;
    }

    /**
     * @param $config
     * @param $order
     *
     * @return array
     */
    public function getDeliveryData($config, $order)
    {
        $delivery = array();
        $shippingAddress = $order->getShippingAddress();
        $address = $this->splitStreet($shippingAddress->getStreet(), $config['homeno']);
        $delivery['DELCompanyName'] = $shippingAddress->getData('company');
        $delivery['DELname'] = $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname();
        $delivery['DELstreet'] = $address['street'];
        $delivery['DELpostalcode'] = $shippingAddress->getPostcode();
        $delivery['DELhomeno'] = $address['homeno'];
        $delivery['DELhomenoAdd'] = $address['homeno_add'];
        $delivery['DELcity'] = $shippingAddress->getCity();
        $delivery['DELcountry'] = $shippingAddress->getCountry();
        $delivery['DELphone'] = preg_replace("/[^0-9]/", "", $shippingAddress->getTelephone());
        $delivery['DELemail'] = $order->getCustomerEmail();
        $delivery['DELdate'] = $config['del_date'];
        $delivery['DELtime'] = $config['del_time'];
        $delivery['DELAaantalColli'] = 1;
        $delivery['DELbarcodes'] = '';
        $delivery['CreateLabel'] = 'True';
        $delivery['DELnote'] = '';
        return $delivery;
    }

    /**
     * @param     $streetArr
     * @param int $homeNoSep
     *
     * @return array
     */
    public function splitStreet($streetArr, $homeNoSep = 0)
    {
        if ($homeNoSep && isset($streetArr[1])) {
            $street = $streetArr[0];
            $homeNoString = str_replace(array('-', ','), ' ', $streetArr[1]);
            $homeNo = @reset(array_filter(preg_split("/\D+/", $homeNoString)));
            $homeNoAdd = trim(str_replace($homeNo, '', $homeNoString));
            return array('street' => $street, 'homeno' => $homeNo, 'homeno_add' => $homeNoAdd);
        } else {
            $street = $streetArr[0];
            preg_match('/^([^\d]*[^\d\s]) *(\d.*)$/', $street, $match);
            $street = (isset($match[1])) ? $match[1] : '';
            $homeNoString = (isset($match[2])) ? str_replace('-', ' ', $match[2]) : '';
            $homeNo = @reset(array_filter(preg_split("/\D+/", $homeNoString)));
            $homeNoAdd = trim(str_replace($homeNo, '', $homeNoString));
            return array('street' => $street, 'homeno' => $homeNo, 'homeno_add' => $homeNoAdd);
        }
    }

}