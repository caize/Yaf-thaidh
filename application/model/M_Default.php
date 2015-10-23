<?php
/**
 * File: M_Default.php
 * Functionality: Default model
 */
class M_Default extends M_Model {

	function __construct($table) {
		$this->table = TB_PREFIX.$table;
		parent::__construct();
	}

}
