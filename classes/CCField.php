<?php
/**
 * User: arthurvioly
 * Date: 09/06/15
 * Time: 22:45
 */

class CCField {

	protected $module;
	protected $input;
	public $lang = false;
	public $wysiwyg = false;
	public $tab = 'default';

	public function __construct( &$input, $module ) {

		$this->module = $module;
		$this->input = $input;

		if ( ! isset( $input['tab'] ) ) {
			$input['tab'] = $this->tab;
		}

		foreach($input as $key=>$value){
			$this->$key = $value;
		}

		if($this->wysiwyg){
			$input['autoload_rte'] = true;
		}


	}

} 