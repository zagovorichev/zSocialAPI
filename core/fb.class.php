<?php
/**
*	Facebook
*
*	# Facebook.com API
*	### Alexander Zagovorichev <zagovorichev@gmail.com>
*/

require_once("OpenAPI.abstract.php");


class FB extends OpenAPI {

	protected $code = 'fb';

	function wallPost( $data = array() ){
	
		$user = $this->dbGetUserBind( $data['user_id'] );
		
		$param = array(
			'access_token'=>$user['access_token'],
			'message'=>$data['message'],
			'name'=>$data['name'],
			'link'=>$data['link'],
			'description'=>$data['description'],
			'picture'=>$data['picture'],
			'caption'=>$data['caption'],
		);
		$url = 'https://graph.facebook.com/'.$user['soc_user_id'].'/feed';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
		$info = curl_exec($ch);
		curl_close($ch);

		if(strpos($info, 'error') === false) 
			return true;

		return $info;
	}


	/**
	*	Достаем данные о пользователе с фейсбука
	*/
	protected function socUserProfile( $access_token = '', $socuser_id = 0 ){

		if(empty($access_token) || !$socuser_id) return false;

		// если не указан id пользователя из соц. сети, пытаемся достать текущего
		if($socuser_id == 'nouser') $socuser_id = 'me';

		$info = file_get_contents('https://graph.facebook.com/'.$socuser_id.'?access_token='.$access_token);
		$info = json_decode($info);

		#	Т.к. соцсеть не одна, приводим данные к одной структуре
		$profile = new ProfileInfoOpenAPI;
		$profile->nickname = $info->username;
		$profile->uid = $info->id;
		$profile->first_name = $info->first_name;
		$profile->last_name = $info->last_name;
		$profile->sex = ($info->gender == 'male'?2:($info->gender=='female'?1:''));
		$profile->bdate = $info->birthday;
		$profile->mail = $info->email;
		$profile->access_token = $access_token;
		$profile->photo_big = 'https://graph.facebook.com/'.$socuser_id.'/picture?access_token='.$access_token.'&type=large';

		return $profile;
	}

	/**
	*	oAuth 2.0 Авторизация
	*/
	protected function oAuthURL(){
		return 'http://www.facebook.com/dialog/oauth?client_id='.$this->fb_config['client_id'].'&redirect_uri='.$this->host.'/github/socialAPI/php/fb/oauth_done.php&scope=email,user_birthday,offline_access,read_stream,publish_stream';
	}

	/**
	*	Разлогинивание в соц. сети
	*/
	public function SocLogoutURL($user_id){
		$soc_info = $this->getOurUser($user_id);
		if(count($soc_info))
			return 'https://www.facebook.com/logout.php?next='.$this->host.'/ajax/forms/auth/soc/fb/logout.php&access_token='.$soc_info['access_token'];
		return false;
	}

	/**
	*	яРПЮМХЖЮ, ЙНРНПЮЪ БШГНБЕРЯЪ ОПХ ПЮГКНЦХМХБЮМХХ Б ЯНЖЯЕРХ
	*/
	public function printLogoutDoneForm(){
	?>
		<html>
		<head></head>
		<body>
			<script type="text/javascript">
				window.parent.AuthForm.setLogoutStatus('<?=$this->code?>');
			</script>
		</body>
		</html>
	<?php
	} // logout done form

	
	/**
	*	Страница для redirect_uri, куда будет передан код для получения access_token
	*/
	public function oAuthDonePage(){
		?>

		<html>
		<head></head>
		<body>
		<?php
			if(isset($_GET['code'])){

				$info = file_get_contents('https://graph.facebook.com/oauth/access_token?client_id='.$this->fb_config['client_id'].'&client_secret='.$this->fb_config['client_secret'].'&code='.$_GET['code'].'&redirect_uri='.$this->host.'/github/socialAPI/php/fb/oauth_done.php');

				$err = json_decode($info);
				
				if(!count($err)) {//save

					if(!isset($info) || empty($info))

						echo "<center><font style=\"color: red; font-size: 17px;\">Error: empty access token</font></center>";

					else{

						$access_token = str_replace('access_token=', '', $info);
						
						// достали access_token пользователя, можно обработать соц. аккаунт
						$res = $this->AuthProcessing($access_token, 'nouser');

						if($res['status'] == 'error')
							
							echo "<center><font style=\"color: red; font-size: 17px;\">Ошибка авторизации в facebook (Не удалось проверить привязку соц. пользователя к нашему аккаунту)</font></center>";

//						elseif($res['status'] == 'updated')

//							echo "<script>document.domain=document.domain; window.opener.zSocialAPI.closePopupReload();</script>";

						else 
							
							echo "<script>document.domain=document.domain; window.opener.zSocialAPI.closePopupReload();</script>"; } // if(info)
				
				} else echo "<center><font style=\"color: red; font-size: 17px;\">".$err->error->message."</font></center>";

			} // if isset $_GET['code']

			else echo "<center><font style=\"color: red; font-size: 17px;\">Ошибка авторизации в facebook</font></center>";
		?>
		</body>
		</html>

	<?php
	} // oAuthDonePage
}
?>
