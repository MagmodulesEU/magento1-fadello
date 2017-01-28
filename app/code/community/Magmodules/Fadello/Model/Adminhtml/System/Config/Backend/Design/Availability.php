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

class Magmodules_Fadello_Model_Adminhtml_System_Config_Backend_Design_Availability
    extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array
{

    /**
     *
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            unset($value['__empty']);
            if (count($value)) {
                $value = $this->orderData($value, 'day');
                $keys = array();
                for ($i = 0; $i < count($value); $i++) {
                    $keys[] = 'availability_' . uniqid();
                }

                foreach ($value as $key => $field) {
                    $from = str_replace('.', ':', $field['from']);
                    $to = str_replace('.', ':', $field['to']);
                    if (empty($from)) {
                        $from = '0:00';
                    }

                    if (empty($to)) {
                        $to = '23:59';
                    }

                    if ($to == '0:00') {
                        $to = '23:59';
                    }

                    $value[$key]['from'] = $from;
                    $value[$key]['to'] = $to;
                    $value[$key]['title'] = $field['title'];
                }

                $value = array_combine($keys, array_values($value));
            }
        }

        $this->setValue($value);
        parent::_beforeSave();
    }

    /**
     * @param $data
     * @param $sort
     *
     * @return mixed
     */
    function orderData($data, $sort)
    {
        $code = "return strnatcmp(\$a['$sort'], \$b['$sort']);";
        usort($data, create_function('$a,$b', $code));
        return $data;
    }

}
