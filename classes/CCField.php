<?php
/**
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class CCField {

	protected $module;
	public $input;
	public $lang = false;
	public $wysiwyg = false;
	public $tab = 'default';
	public $is_array = false;

	public function __construct(&$input, $module)
	{
		$this->module = $module;
		$this->input = &$input;
		foreach ($input as $key => $value)
			$this->$key = $value;

		switch ($this->type)
		{
			case 'categories' :
				$this->tree = &$input['tree'];
				$this->is_array = $this->tree['use_checkbox'] ? true:false;
				break;
		}

		if ($this->type == 'categories')
		{
			$root_category = isset($this->input['tree']['root_category']) ? (integer)$this->input['tree']['root_category'] : 1;
			$root_category = $root_category > 0 ? $root_category : 2;
			$this->input['tree']['root_category'] = $root_category;
			$selected_categories = unserialize($this->module->getFieldValue($this->name));
			$selected_categories = $selected_categories ? $selected_categories : [];
			$this->input['tree']['selected_categories'] = $selected_categories;
		}

		if (!isset($input['tab']))
			$input['tab'] = $this->tab;

		/* alias for wysiwyg editor */
		if ($this->wysiwyg)
			$input['autoload_rte'] = true;

	}

	public function getValue()
	{
		$raw_value = $this->module->getFieldValue($this->name, $this->lang ? $this->module->getContext()->language->id : null);
		return $this->is_array ? unserialize($raw_value) : $raw_value;
	}

}