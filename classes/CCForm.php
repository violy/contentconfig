<?php
/**
 * User: arthurvioly
 * Date: 09/06/15
 * Time: 22:14
 */

class CCForm {

	protected $configuration_data;
	protected $module;
	protected $fields = array();
	public $form;

	public function __construct($module){

		$this->module = $module;
		$this->loadJsonFile();
		$this->cleanFields();
		$this->setupForm();

	}

	public function l($args){
		return $this->module->l($args);
	}

	protected function loadJsonFile(){
		/* Load configuration file */
		$config_file_path = _PS_THEME_DIR_ . 'contentconfig.json';
		$this->config_file_exists = file_exists( $config_file_path );

		/* if configuration file don't exists, copy empty file */
		if ( ! $this->config_file_exists ) {
			$empty = Tools::file_get_contents( $this->module->local_path . 'json/empty.json' );
			file_put_contents( $config_file_path, $empty );
		}
		$configurationJSON = Tools::file_get_contents( $config_file_path );
		$this->configuration_data = Tools::jsonDecode( $configurationJSON, true );


	}

	protected function cleanFields(){

		foreach ( $this->configuration_data['input'] as $key => &$field ) {
			$this->fields[] = new CCField($field,$this->module);
		}

		if(count($this->configuration_data['tabs'])<2){
			unset ($this->configuration_data['tabs']);
		}
	}

	protected function setupForm(){

		$base = array(
			'id' => 'cc_form',
			'legend' => array(
				'title' => $this->l( 'Contents' ),
				'icon'  => 'icon-sliders',
			),
			'submit' => array(
				'title' => $this->l( 'Save' ),
			)
		);

		$this->form = array_merge($base,$this->configuration_data);
	}

	public function getFields(){
		return $this->fields;
	}

	public function getFormValues(){
		$values = array();

		foreach($this->fields as $key=>$field){
			if($field->lang){
				$values[ $field->name ] = array();
				foreach( Language::getLanguages() as $language){
					$value = $this->module->getFieldValue( $field->name, $language['id_lang'] );
					$values[ $field->name ][$language['id_lang']] = $value;
				}
			}else{
				$value = $this->module->getFieldValue( $field->name );
				$values[ $field->name ] = $value;
			}
		}

		return $values;
	}

	public function toArray(){
		return array('form'=>$this->form);
	}

}