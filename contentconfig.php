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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

include_once 'classes/CCForm.php';
include_once 'classes/CCField.php';

class Contentconfig extends Module
{
	protected $config_form = false;
	protected $config_file_exists = false;
	protected $config;
	protected $db;

	public function __construct()
	{
		$this->name = 'contentconfig';
		$this->tab = 'content_management';
		$this->version = '0.0.1';
		$this->author = 'Arthur Violy';
		$this->need_instance = 0;

		/**
		 * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
		 */
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('content configuration');
		$this->description = $this->l('developer module. manage custom fields');

		$this->db = Db::getInstance();

		$this->form = new CCForm($this);

	}

	/**
	 * Don't forget to create update methods if needed:
	 * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
	 */
	public function install()
	{
		include(dirname(__FILE__).'/sql/install.php');

		return parent::install() &&
			$this->registerHook('header') &&
			$this->registerHook('backOfficeHeader') &&
			$this->registerHook('displayHeader');
	}

	public function uninstall()
	{
		include(dirname(__FILE__).'/sql/uninstall.php');

		return parent::uninstall();
	}

	/**
	 * Load the configuration form
	 */
	public function getContent()
	{
		/**
		 * If values have been submitted in the form, process.
		 */
		if (((bool)Tools::isSubmit('submitContentconfigModule')) == true)
			$this->postProcess();

		$this->context->smarty->assign('module_dir', $this->_path);

		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

		return $output.$this->renderForm();
	}

	/**
	 * Create the form that will be displayed in the configuration of your module.
	 */
	protected function renderForm()
	{
		$helper = new HelperForm();

		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$helper->module = $this;
		$helper->default_form_language = $this->context->language->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitContentconfigModule';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
			.'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');

		$helper->tpl_vars = array(
			'fields_value' => $this->form->getFormValues(), /* Add values for your inputs */
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
		);

		return $helper->generateForm(array($this->form->toArray()));
	}

	public function getFieldValue($field_name, $id_lang = false)
	{
		$query = 'SELECT * FROM `'._DB_PREFIX_.'cc_value` WHERE name = \''.$field_name.'\' ';
		if ($id_lang)
			$query .= ' AND id_lang = \''.$id_lang.'\'';
		$row = $this->db->getRow($query);
		return $row ? $row['value'] : null;
	}

	public function updateFieldValue($field_name, $value, $id_lang = false)
	{
		$current_value = $this->getFieldValue($field_name, $id_lang);
		$insert = array(
			'value' => $value,
		);
		$where = 'name = \''.$field_name.'\'';
		if ($id_lang)
		{
			$where .= ' AND id_lang = '.$id_lang;
			$insert['id_lang'] = $id_lang;
		}

		if (!$current_value)
		{
			if ($value)
			{
				$insert['name'] = $field_name;
				$this->db->insert('cc_value', $insert);
			}
		}
		else
		{
			if ($value)
				$this->db->update('cc_value', $insert, $where);
		}
	}

	/**
	 * Save form data.
	 */
	protected function postProcess()
	{
		foreach ($this->form->getFields() as $field)
		{
			if ($field->lang === true)
			{
				foreach (Language::getLanguages() as $lang)
					$this->updateFieldValue($field->name, Tools::getValue($field->name.'_'.$lang['id_lang']), $lang['id_lang']);
			}
			if ($field->type == 'categories')
			{
				$selected_categories = Tools::getValue($field->name);
				$selected_categories = $selected_categories ? $selected_categories : array();
				if (gettype($selected_categories) != 'array')
					$selected_categories = [$selected_categories];
				$field->tree['selected_categories'] = $selected_categories;
				$this->updateFieldValue($field->name, serialize($selected_categories));
			}
			else if ($field->is_array)
				$this->updateFieldValue($field->name, serialize(Tools::getValue($field->name)));
			else
				$this->updateFieldValue($field->name, Tools::getValue($field->name));
		}
	}

	/**
	* Add the CSS & JavaScript files you want to be loaded in the BO.
	*/
	public function hookBackOfficeHeader()
	{
		if (Tools::getValue('configure') == $this->name)
			$this->context->controller->addJS($this->_path.'views/js/back.js');
	}

	/**
	 * Add the CSS & JavaScript files you want to be added on the FO.
	 */
	public function hookHeader()
	{
		$this->context->smarty->assign(array('cc' => $this));
	}

	/**
	 * Get the local path of the module
	 */
	public function getLocalPath()
	{
		return $this->local_path;
	}

	public function getContext()
	{
		return $this->context;
	}

	public function get($field_name)
	{
		return $this->form->getValue($field_name);
	}
}
