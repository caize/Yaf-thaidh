<?php
/**
 *File: F_Cookie.php
 *Functionality: Extra Cookie functions
 */

// Clear cookie value
function clearCookie($name, $cookiedomain = ''){
	setCookie($name, '', CUR_TIMESTAMP - 3600, '/', $cookiedomain);
}


/**
 * Set search COOKIE
 *1: Clear cookie
 *2: Set cookie 
 */
function setSearchCookie($name, $value, $expire = 3600, $cookiedomain = ''){
	clearCookie($name);
	setCookie($name, $value, CUR_TIMESTAMP + $expire, '/', $cookiedomain);
}


/**
 * 获取cookie
 * @param string $name
 * @return null
 */
function getCookie($name){
	$value = $_COOKIE[$name];
	if (!isset($value)) {
		return null;
	}

	$value = trim($value);

	return $value;
}
