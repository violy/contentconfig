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

class CCForm {

	protected $configuration_data;
	protected $module;
	protected $fields = array();
	public $form;

	public function __construct($module)
	{
		$this->module = $module;
		$this->loadJsonFile();
		$this->cleanFields();
		$this->setupForm();
	}

	public function l($args)
	{
		return $this->module->l($args);
	}

	protected function loadJsonFile()
	{
		/* Load configuration file */
		$config_file_path = $this->module->getLocalPath().'contentconfig.json';
		$this->config_file_exists = file_exists($config_file_path);

		/* if configuration file don't exists, copy empty file */
		if (!$this->config_file_exists)
		{
			$empty = Tools::file_get_contents($this->module->getLocalPath().'json/empty.json');
			file_put_contents($config_file_path, $empty);
		}
		$configuration_json = Tools::file_get_contents($config_file_path);
		$this->configuration_data = Tools::jsonDecode($configuration_json, true);

	}

	protected function cleanFields()
	{
		foreach ($this->configuration_data['input'] as &$field)
			$this->fields[] = new CCField($field, $this->module);

		if (count($this->configuration_data['tabs']) < 2)
			unset ($this->configuration_data['tabs']);
	}

	protected function setupForm()
	{
		$base = array(
			'id' => 'cc_form',
			'legend' => array(
				'title' => $this->l('Contents'),
				'icon'  => 'icon-sliders',
			),
			'submit' => array(
				'title' => $this->l('Save'),
			)
		);

		$this->form = array_merge($base, $this->configuration_data);
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function getValue($field_name)
	{
		$field = $this->getField($field_name);
		if (!isset($field))
			return null;
		return $field->getValue();
	}

	public function getField($field_name)
	{
		$fields_count = count($this->fields);
		for ($i = 0; $i < $fields_count; $i++)
		{
			$field = $this->fields[$i];
			if ($field->name == $field_name)
				return $field;
		}
		return null;
	}

	public function getFormValues()
	{
		$values = array();

		foreach ($this->fields as $field)
		{
			if ($field->lang)
			{
				$values[$field->name] = array();
				foreach (Language::getLanguages() as $language)
				{
					$value = $this->module->getFieldValue($field->name, $language['id_lang']);
					$values[$field->name][$language['id_lang']] = $value;
				}
			}
			elseif ($field->is_array)
			{
				$raw_value = $this->module->getFieldValue($field->name);
				$values[$field->name] = unserialize($raw_value);
			}
			else
			{
				$value = $this->module->getFieldValue($field->name);
				$values[$field->name] = $value;
			}
		}

		return $values;
	}

	public function toArray()
	{
		return array('form'=>$this->form);
	}

}