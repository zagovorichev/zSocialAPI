<?php
/**
*	VK oAuth 2.0 ext
*
*	# Alexander Zagovorichev <zagovorichev@gmail.com>
*/

require_once('../../core/vk.class.php');

$uid = get_current_user_id();
if($uid){
	$oVK = new VK();
	if( !$oVK->checkPermission($uid) )
		echo 'permission_denied';
}else echo 'no_user';
?>
