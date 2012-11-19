<?php
require_once("OpenAPI.abstract.php");

class VK extends OpenAPI {
	
	protected $code = 'vk';

	/***
	*	Публикация на стену вКонтакте
	*/
	public function wallPost( $data = array() ){

		$suser = $this->dbGetUserBind( $data['user_id'] );

		// Достаем права доступа пользователя
		$info = file_get_contents('https://api.vkontakte.ru/method/getUserSettings?uids='.$suser['soc_user_id'].'&fields=nickname,sex,bdate,photo_big&access_token='.$suser['access_token']);

		$info = json_decode($info);

		if($info->response == 73728) { // Маска прав доступа offline + wall

			$info = file_get_contents('https://api.vkontakte.ru/method/wall.post?owner_id='.$suser['soc_user_id'].'&access_token='.$suser['access_token'].'&message='.urlencode( 'hello, amigo!!!' ));
		
		} else return 'upVkPermission';
	}

	/**
	*	Данные из аккаунта вКонтакте
	*/
	protected function socUserProfile( $access_token = '', $socuser_id = 0 ){

		$info = file_get_contents('https://api.vkontakte.ru/method/users.get?uids='.$socuser_id.'&fields=nickname,sex,bdate,photo_big&access_token='.$access_token);
		$info = json_decode($info);
		if(!count($info)) return false;
		$info = $info->response[0];
		
		#   Используем структуру данных
		$profile = new ProfileInfoOpenAPI;
		$profile->nickname = $info->nickname;
		$profile->uid = $info->uid;
		$profile->first_name = $info->first_name;
		$profile->last_name = $info->last_name;
		$profile->sex = $info->sex;
		$profile->bdate = $info->bdate;
		$profile->access_token = $access_token;
		$profile->photo_big = $info->photo_big;

		return $profile;
	}

	/**
	*	oAuth 2.0 вКонтакте
	*/
	protected function oAuthURL(){
		
		return 'http://api.vkontakte.ru/oauth/authorize?response_type=token&redirect_uri='.urlencode($this->host.'/github/socialAPI/php/vk/oauth_done.php').'&client_id='.urlencode($this->vk_config['client_id']).'&scope='.urlencode('wall,offline').'&display=page';
	}

	public function SocLogoutURL($user_id){return false;}

	/*
	*	Страница redirect_uri, получаем access_token
	*/
	public function oAuthDonePage(){
		?>
		<html>
		<head></head>
		<body>
		<script>
			if(document.location.href.indexOf('#') != -1 && document.location.href.indexOf('access_token') != -1)
				document.location = document.location.href.replace(/#/g, "?");
		</script>
		<?php
			if(isset($_GET['access_token']) && isset($_GET['user_id'])){

					// Обрабатываем полученый access_token
					$res = $this->AuthProcessing($_GET['access_token'], $_GET['user_id']);

					echo "<script>document.domain=document.domain; window.opener.zSocialAPI.closePopupReload();</script>";

			}
			else echo "<center><font style=\"color: red; font-size: 15px>Invalid access_token</font></font></center>";
		?>
		</body>
		</html>
	<?php
	}
}
?>
