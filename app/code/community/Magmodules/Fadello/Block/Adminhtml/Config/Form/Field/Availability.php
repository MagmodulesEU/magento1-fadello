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

class Magmodules_Fadello_Block_Adminhtml_Config_Form_Field_Availability
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    protected $_renders = array();

    /**
     * Magmodules_Fadello_Block_Adminhtml_Config_Form_Field_Availability constructor.
     */
    public function __construct()
    {
        $layout = Mage::app()->getFrontController()->getAction()->getLayout();
        $rendererDays = $layout->createBlock(
            'fadello/adminhtml_config_form_renderer_select', '',
            array('is_render_to_js_template' => true)
        );
        $rendererDays->setOptions(Mage::getModel('fadello/adminhtml_system_config_source_days')->toOptionArray());

        $this->addColumn(
            'day', array(
            'label'    => Mage::helper('fadello')->__('Day'),
            'style'    => 'width:100px',
            'renderer' => $rendererDays
            )
        );

        $this->addColumn(
            'from', array(
            'label' => Mage::helper('fadello')->__('From'),
            'style' => 'width:40px',
            )
        );

        $this->addColumn(
            'to', array(
            'label' => Mage::helper('fadello')->__('To'),
            'style' => 'width:40px',
            )
        );

        $this->addColumn(
            'title', array(
            'label' => Mage::helper('fadello')->__('Title'),
            'style' => 'width:100px',
            )
        );

        $this->_renders['day'] = $rendererDays;

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('fadello')->__('Add Option');
        parent::__construct();
    }

    /**
     * @param Varien_Object $row
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        foreach ($this->_renders as $key => $render) {
            $row->setData(
                'option_extra_attr_' . $render->calcOptionHash($row->getData($key)),
                'selected="selected"'
            );
        }
    }

}