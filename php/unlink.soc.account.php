<?php
/**
*	Отвязываем пользователя от соцсети
*
*	Alexander Zagovorichev <zagovorichev@gmail.com>
*/
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
require_once($_SERVER['DOCUMENT_ROOT']."/github/socialAPI/core/social.class.php");

if( count($_POST) && isset($_POST['social']))
	Social::factory($_POST['social'])->unlinkSocAccount(get_current_user_id());

?>
