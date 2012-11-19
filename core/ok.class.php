<?php
require_once("OpenAPI.abstract.php");

class OK extends OpenAPI {
	protected $code = 'ok';

	/**
	*	Данные из аккаунта вКонтакте
	*/
	protected function socUserProfile( $access_token = '', $user_id = 0 ){
		
		$param = array(
					'application_key' => $this->ok_config['client_pub'],
					'client_id' => $this->ok_config['client_id'],
					'format' => 'JSON',
					'method' => 'users.getCurrentUser',
				);
		ksort($param);
		
		$param = http_build_query($param);
		
		$sig = md5(str_replace('&','',$param).md5($access_token.$this->ok_config['client_secret']));
		
		$param .= '&access_token='.$access_token.'&sig='.$sig;
		
		$url = 'http://api.odnoklassniki.ru/fb.do?'.$param;
		$info = file_get_contents('http://api.odnoklassniki.ru/fb.do?'.$param);
		$info = json_decode($info);
		if(!count($info)) return false;

		$info->first_name = $info->first_name;
		$info->last_name = $info->last_name;
		
		if($info->gender=='male') $info_sex = 2;
		elseif($info->gender=='female') $info_sex = 1;
		else $info_sex = 0;

		# Структурируем данные
		$profile = new ProfileInfoOpenAPI;
		$profile->uid = $info->uid;
		$profile->first_name = $info->first_name;
		$profile->last_name = $info->last_name;
		$profile->sex = $info_sex;
		$profile->bdate = $info->birthday;
		$profile->access_token = $access_token;
		$profile->photo_big = $info->pic_2;
		
		return $profile;
	}

    /**
	*	oAuth 2.0 вКонтакте
	*/
    public function oAuthURL(){
		return 'http://www.odnoklassniki.ru/oauth/authorize?client_id='.$this->ok_config['client_id'].'&redirect_uri='.$this->host.'/github/socialAPI/php/ok/oauth_done.php&response_type=code';
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
		<?php
			if(isset($_GET['code'])){
				$opts = array('http'=>array(
								'method'=>'POST',
								'header'=>"Content-Type: application/x-www-form-urlencoded\r\n",
								'content'=>http_build_query(array(
								'client_id'=>$this->ok_config['client_id'],
								'client_secret'=>$this->ok_config['client_secret'],
								'code'=>$_GET['code'],
								'grant_type'=>'authorization_code',
								'redirect_uri'=>$this->host.'/github/socialAPI/php/ok/oauth_done.php'
								))
							)
						);
				$context = stream_context_create($opts);
				$info = file_get_contents('http://api.odnoklassniki.ru/oauth/token.do?', false, $context);
				$info = json_decode($info); // access_token -> key, user_id
				
				if(isset($info->access_token)) {//save
					$res = $this->AuthProcessing($info->access_token, 'nouser'/*$info->user_id*/);
					
					//	if($res['status']=='updated') 
					//	echo "<script>document.domain=document.domain; window.opener.AuthForm.closePopupReload();</script>";
					//else echo "<script>document.domain=document.domain; window.opener.AuthForm.socAuthStep2(".$res['user_id'].");</script>";
					echo "<script>document.domain=document.domain; window.opener.zSocialAPI.closePopupReload();</script>";
					
				} elseif(isset($info->error_description))
				echo "<center><font style=\"color: red; font-size: 17px;\">".$info->error_description."</font></center>";
			}
			else echo "<center><font style=\"color: red; font-size: 17px;\">Ошибка авторизации через Одноклассники</font></center>";
		?>
		</body>
		</html>
	<?php
	}
}
?>
