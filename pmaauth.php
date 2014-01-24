<?php
/* 
	pmaAuth for phpMyAdmin
	
	A simple user management extension for phpMyAdmin that allows to secure phpMyAdmin independent from MySQL users.

	This program is protected by copyright laws and international treaties.
	Unauthorized reproduction or distribution of this program, or any 
	portion thereof, may result in serious civil and criminal penalties.
	
	This software is released under the GPLv3 License. (http://www.gnu.org/licenses/gpl.txt)

	@git		https://github.com/flyandi/pmaAuth
	@author		Andreas Gulley (http://github.com/flyandi)
	@package 	pmaAuth
	@module		CLI Manager
	@version	1.0
*/

# (constants)
define("PMA_CONFIG", dirname(__FILE__)."/pmaauth.conf");
define("PMA_SESSIONS", dirname(__FILE__)."/sessions/");
define("PMA_USERS", dirname(__FILE__)."/secure/users");
define("PMA_TEMPLATE_SIGNIN", dirname(__FILE__)."/template/signin.template");
define("PMA_GROUPS", dirname(__FILE__)."/groups/");


# (pmaGetVar)
function pmaGetVar($name, $default = "") {
	foreach(array($_REQUEST, $_COOKIE, $_GET, $_POST, $GLOBALS) as $target) {
		if(isset($target[$name])) return urldecode($target[$name]);
	}
	return $default;
}	

# (pmaDefault)
function pmaDefault($value, $default = null) {
	return (empty($value)||(is_string($value)&&strlen(trim($value))==0)||$value==null) ? $default : $value;
}


# (configuration)
$config = json_decode(file_get_contents(PMA_CONFIG));
if(!is_object($config)) $config = (object)array();

# (presets)
$users = json_decode(file_get_contents(PMA_USERS), true);
$signin = file_get_contents(PMA_TEMPLATE_SIGNIN);
$fields = is_object(pmaDefault($config->html, false)) ? (array)$config->html : array();

# (authentification)
if($sid = pmaGetVar(pmaDefault($config->sessionname, "pmaAuthSession"))) {
	$sfn = PMA_SESSIONS.$sid;
	if(file_exists($sfn)) {
		$session = json_decode(file_get_contents($sfn));
		if(is_object($session)) {
			if(time() < (pmaDefault($session->timestamp, time()) + (pmaDefault($config->sessiontimeout, 60) * 60 * 1000))) {
				// check user
				if(isset($users[$session->user])) {
					// get group(s)
					$user = $users[$session->user];
					$groups = (array)$config->groups;
					$group = isset($groups[$user[1]]) ? $user[1] : (pmaDefault($config->defaultgroup, false) ? $config->defaultgroup : false);
					// check group
					if(isset($groups[$group])) {
						// include group
						$config = $groups[$group];
						// include file
						if(file_exists(PMA_GROUPS.$config)) {
							include(PMA_GROUPS.$config);
							return;
						}
					} 
					// set notice
					$fields["notice"] = "No valid security group was assigned to this user.";
					$fields["noticetype"] = "warn";
				}
			}
			// kill session
			@unlink($sfn);
		}
	}
}


# (login)
if(pmaGetVar("username", false)) {
	// process login
	$uname = sha1(pmaGetVar("username"));
	if(!is_array($users)) $users = array();
	// check
	if(isset($users[$uname]) && $users[$uname][0] == sha1(pmaGetVar("password"))) {
		// create session
		$sid = md5(uniqid(rand(), true));
		file_put_contents(PMA_SESSIONS.$sid, json_encode(array(
			"timestamp"=>time(),
			"user"=>$uname
		)));
		// issue cookie
		setcookie(
			pmaDefault($config->sessionname, "pmaAuthSession"), 
			$sid, 
			time() + (pmaDefault($config->sessiontimeout, 60) * 60 * 1000), 
			pmaDefault($config->sessionpath, null), 
			pmaDefault($config->sessiondomain, null), 
			pmaDefault($config->sessionsecure, null),
			pmaDefault($config->sessionhttp, false)
		);
		// redirect
		header('Location: '.$_SERVER['PHP_SELF']);
		exit;
		
	}
	// failed
	$fields["notice"] = "The entered credentials are not valid.";
	$fields["noticetype"] = "error";
	$fields["username"] = pmaGetVar("username");
}

// output headers
foreach(array(
	sprintf("pmaAuthRequest: %s", time()),
	"Cache-Control: nocache, nostore, must-revalidate",
	"Pragma: no-cache",
	"Expires: 0"
) as $header) header($header);

// parse signin
if(preg_match_all("/{(.*?)}/", $signin, $matches, PREG_PATTERN_ORDER)) {
	foreach($matches[1] as $k) {
		if(substr($k, 0, 3) == "pma") {
			$signin = str_replace(sprintf("{%s}", $k), isset($fields[substr($k, 4)]) ? $fields[substr($k, 4)] : "", $signin);
		}
	}
}
// show login page
echo $signin;
// end execution
exit;