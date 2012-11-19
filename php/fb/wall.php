<?php
/**
*	Пост на стену
*
*	Alexander Zagovorichev <zagovorichev@gmail.com>
*/
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
require_once($_SERVER['DOCUMENT_ROOT']."/github/socialAPI/core/social.class.php");

if( count($_POST) && isset($_POST['social'])){
	// для теста постим одно и тоже
	$data = array(
		'user_id' => get_current_user_id(),
		'message' => 'Собираем API соцсетей в одно целое',
		'name' => 'Все API соцсетей',
		'link' => network_home_url().'/socialAPI.php',
		'description' => 'Создаем библиотеку, для работы с API соцсетей',
		'picture' => 'http://blog-tree.com/images/logo.gif',
		'caption' => 'все в одном',
	);
	$res = Social::factory($_POST['social'])->wallPost( $data );

	if($res === true) exit(); // it's ok
	else echo $res;
}
echo 'wall.post.errors!';
?>
