<?php
/*******
*	Абстрактный класс для работы с OpenAPI различных сайтов
*
*	# Alexander Zagovorichev (zagovorichev@gmail.com)
*******/

require_once('ConfigOpenAPI.abstract.php');

# Структура для хранения информации о пользователе из соцсети
class ProfileInfoOpenAPI{
	public	$nickname = '',
			$last_name = '',
			$first_name = '',
			$photo_big = '',
			$uid = 0,
			$access_token = 0,
			$sex = 0, //1 - 'f', 2 - 'm'
			$bdate = '', //birthday
			$mail = '';
}


abstract class OpenAPI extends ConfigOpenAPI{

	protected   $code,  # код подключаемого сайта
				$host; // текущий хост сайта

	/**
	*	В конструкторе инициализируем хост, с которым работает API
	*/
	public function __construct($h = ''){
		$this->setHost($h);
	}

	/**
	*	Инициализируем хост
	*/
	private function setHost($h){
		if(!isset($h) || !$h) $this->host = 'http://'.$_SERVER['HTTP_HOST'];
		else $this->host = $h;
	}

	/**
	*	Вывод Popup (шаблон страницы)
	*/
	protected function printPage( $url ){
		if(empty($url)) $url = $this->host.'/404.php';
		?>
		<html>
		<head></head>
		<body>
			<script>
				document.location = '<?php echo $url; ?>';
			</script>
		</body>
		</html>
		<?php
	}

	/**
	*	Страница авторизации пользователя в соцсети
	*/
	public function oAuthPage(){

		$url = $this->oAuthURL();

		$this->printPage( $url );
	} //oAuthPage

	/**
	*		Обработка соц. аккаунта
	*	Проверяем, привязан ли данный социальный пользователь к нашей системе
	*
	*	# access_token 	- access_token из соц. сети
	*	# socuser_id 		- ID пользователя в соц. сети
	*	### $socuser_id = 'nouser' - значит еще не получали инфо о пользователе из соц. сети, достали только access_token
	*
	*	## return:
	*	#		'new' 		- аккаунт не привязан к нашей системе
	*	#		'updated'	- аккаунт привязан к нашей системе
	*/
	public function AuthProcessing( $access_token = '', $socuser_id = 0 ){
		
		if(empty($access_token) || !$socuser_id) return array('status' => 'error');

		$status = 'new';		// статус соц. пользователя в системе
		$not_logged = true;		// пользователь не залогинен в системе

		// Проверяем, привязан ли соц. аккаунт к пользователю, который есть в нашей системе
		if($socuser_id == 'nouser') {

			# структурированные данные (структура ProfileInfoOpenAPI) - инфо о пользователе из соц. сети
			$profile = $this->socUserProfile($access_token, $socuser_id);
			
			# инфо о привязке пользователя из нашей бд по ID пользователя из соц. сети
			$socuser_info = $this->dbGetUserSocial($profile->uid);

			$socuser_id = $profile->uid;

		} else $socuser_info = $this->dbGetUserSocial($socuser_id);


		// достаем пользователя, который сейчас залогинен в системе
		// # если залогинен, то привязываем к нему этот соц. аккаунт
		// # если аккаунт уже привязан к кому-то он просто перепривяжется к текущему пользователю
		if( $logged_user_id = $this->getLoggedUser() ){
			
			// привязываем соц. пользователя, к профайлу залогиненого пользователя
			$this->dbMergeSocAccount($access_token, $socuser_id, $logged_user_id);
			$status = 'updated';
			$not_logged = false;

		} elseif(!$socuser_info || !count($socuser_info)){ // не привязан
				
			// создание нового пользователя и привязываем к нему соц. аккаунт
			$this->dbCreateUser($access_token, $socuser_id);

		} else {
			
			$this->dbUpdateAccessToken($access_token, $socuser_info['soc_user_id']);
			$status = 'updated';
		}

		return array('status' => $status);

	} // AuthProcessing

	/**
	*	Проверяем есть ли привязка у пользователя к соц. сети
	*/
	public function isUserBind( $user_id ){
		
		$user_bind = $this->dbGetUserBind($user_id);

		if($user_bind && isset($user_bind['id']) && $user_bind['id']) return true;

		return false;
	}

	/**
	*	Привязываем пользователя к соц. сети
	*
	*	return true/false
	*/
	protected function dbMergeSocAccount( $access_token, $socuser_id, $local_user_id ){
		
		$socuser = $this->dbGetUserSocial( $socuser_id );
		
		if(count($socuser) && isset($socuser['id']) && $socuser['id'])
			//если такой соц. пользователь уже создан, меняем хозяина, и access_token
			$this->dbChangeSocUserMaster($access_token, $socuser_id, $local_user_id);
		else {
			// если привязки пользователя еще нет, создаем и привязываем
			$profile = $this->socUserProfile($access_token, $socuser_id);

			if($profile instanceof ProfileInfoOpenAPI)
				$this->dbCreateSocUser($profile, $local_user_id);
			else return false;
		}
		return true;
	}

	# Вернет url на страницу в соц. сети с oauth авторизацией
	abstract protected function oAuthURL();
	
	# Страница для redirect_uri, куда будет передан код для получения access_token
	abstract public function oAuthDonePage();

	# Данные о пользователе из соц. сети вернет структуру ProfileInfoOpenAPI
	abstract protected function socUserProfile();
}
?>
