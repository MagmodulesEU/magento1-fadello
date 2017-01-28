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

class Magmodules_Fadello_Model_Adminhtml_System_Config_Source_Days
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $days = array();
        $days[] = array('value' => '1', 'label' => Mage::helper('adminhtml')->__('Monday'));
        $days[] = array('value' => '2', 'label' => Mage::helper('adminhtml')->__('Tuesday'));
        $days[] = array('value' => '3', 'label' => Mage::helper('adminhtml')->__('Wednesday'));
        $days[] = array('value' => '4', 'label' => Mage::helper('adminhtml')->__('Thursday'));
        $days[] = array('value' => '5', 'label' => Mage::helper('adminhtml')->__('Friday'));
        $days[] = array('value' => '6', 'label' => Mage::helper('adminhtml')->__('Saturday'));
        $days[] = array('value' => '7', 'label' => Mage::helper('adminhtml')->__('Sunday'));
        return $days;
    }

} 