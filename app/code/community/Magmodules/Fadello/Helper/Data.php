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

class Magmodules_Fadello_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * @param int $storeId
     * @param int $detailed
     *
     * @return array|bool
     */
    public function getConfig($storeId = 0, $detailed = 0)
    {
        $api = array();

        $afterCutoff = Mage::getStoreConfig('shipping/fadello/after_cutoff', $storeId);
        $cutoff = str_replace(' ', '', Mage::getStoreConfig('shipping/fadello/cutoff_time', $storeId));
        $date = $this->getDate($afterCutoff, $cutoff);

        $api['api_id'] = Mage::getStoreConfig('shipping/fadello/api_id', $storeId);
        $api['api_token'] = Mage::getStoreConfig('shipping/fadello/api_token', $storeId);
        $api['cutoff_time'] = $cutoff;
        $api['pickup_time'] = str_replace(' ', '', Mage::getStoreConfig('shipping/fadello/pickup_time', $storeId));
        $api['del_time'] = str_replace(' ', '', Mage::getStoreConfig('shipping/fadello/delivery_time', $storeId));
        $api['pu_date'] = $date;
        $api['del_date'] = $date;
        $api['url_params'] = 'apiID=' . $api['api_id'] . '&apitoken=' . $api['api_token'];
        $api['url'] = 'https://api.fadello.nl/desktopmodules/fadello_retailAPI/API/v1/';
        $api['ship_type'] = 'DC';
        $api['label'] = 'PDF';

        if ($detailed) {
            $api['pu_name'] = Mage::getStoreConfig('shipping/fadello/pu_name', $storeId);
            $api['pu_street'] = Mage::getStoreConfig('shipping/fadello/pu_street', $storeId);
            $api['pu_homeno'] = Mage::getStoreConfig('shipping/fadello/pu_homeno', $storeId);
            $api['pu_homeno_add'] = Mage::getStoreConfig('shipping/fadello/pu_homeno_add', $storeId);
            $api['pu_postalcode'] = Mage::getStoreConfig('shipping/fadello/pu_postalcode', $storeId);
            $api['pu_city'] = Mage::getStoreConfig('shipping/fadello/pu_city', $storeId);
            $api['pu_country'] = Mage::getStoreConfig('shipping/fadello/pu_country', $storeId);
            $api['pu_phone'] = preg_replace(
                "/[^0-9]/", "",
                Mage::getStoreConfig('shipping/fadello/pu_phone', $storeId)
            );
            $api['pu_email'] = Mage::getStoreConfig('shipping/fadello/pu_email', $storeId);
            $api['homeno'] = Mage::getStoreConfig('shipping/fadello/seperate_homenumber', $storeId);
        }

        if (!empty($api['api_id']) && !empty($api['api_token'])) {
            return $api;
        }

        return false;
    }

    /**
     * @param int $afterCutoff
     * @param     $cutoff
     *
     * @return mixed
     */
    public function getDate($afterCutoff = 0, $cutoff)
    {
        $today = Mage::getModel('core/date')->date('d-m-Y');
        $tomorrow = Mage::getModel('core/date')->date("d-m-Y", time() + 86400);
        if ($afterCutoff) {
            $time = Mage::getModel('core/date')->date('Hi');
            $cutoff = (str_replace(':', '', $cutoff) + 100);
            if ($time > $cutoff) {
                return $tomorrow;
            }
        }

        return $today;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        $time = date('Hi', Mage::getModel('core/date')->timestamp(time()));
        $day = date('N', Mage::getModel('core/date')->timestamp(time()));
        $availability = @unserialize(Mage::getStoreConfig('carriers/fadello/availability'));
        foreach ($availability as $option) {
            if ($option['day'] == $day) {
                if (!empty($option['from']) && !empty($option['to'])) {
                    $from = str_replace(':', '', $option['from']);
                    $to = str_replace(':', '', $option['to']);
                    if (($time > $from) && ($time < $to)) {
                        return $option;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param $postcode
     *
     * @return bool
     */
    public function checkPostcode($postcode)
    {
        $postcode = substr($postcode, 0, 4);
        $nonDelivery = array(
            "1791",
            "1792",
            "1793",
            "1794",
            "1795",
            "1796",
            "1797",
            "8881",
            "8882",
            "8883",
            "8884",
            "8885",
            "8891",
            "8892",
            "8893",
            "8894",
            "8895",
            "8896",
            "8897",
            "8899",
            "9161",
            "9162",
            "9163",
            "9164",
            "9166",
            "9988"
        );
        if (!in_array($postcode, $nonDelivery)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $data
     */
    public function addToLog($data)
    {
        if (Mage::getStoreConfig('shipping/fadello/debug')) {
            if (is_array($data)) {
                Mage::log(json_encode($data), null, 'fadello.log', false);
            } else {
                Mage::log($data, null, 'fadello.log', false);
            }
        }
    }

}