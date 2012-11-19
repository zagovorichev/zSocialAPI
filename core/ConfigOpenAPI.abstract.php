<?php
/**
*	Config OpenAPI
*
*	# Внутренние функции вашего сайта
*/

/*** just mine ***/
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');

require_once('ConfigOpenAPI.interface.php');
require_once('SocSecret.struct.php');


class ConfigOpenAPI extends SocSecret implements iConfigOpenAPI{


	/********************* DATABASE *********************/
	
	private $db;
	private $tbl_user_social = 'userSocial';

	/**
	*	Подключаемся к нашей БД
	*/
	private function connectDB(){
		
		if(!$this->db){
			try {
				$this->db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
			} catch ( Exeption $e ) {
				echo "Failed: " . $e->getMessage();
				$this->db->rollBack();
			}
		}
	}

	
	
	/***
	*	Создаем нового пользователя, после чего привязываем его к соц.сети
	*	
	*	# return user_id
	*/
	protected function dbCreateUser( $access_token, $socuser_id ){

		$profile = $this->socUserProfile($access_token, $socuser_id);
		
		if($profile instanceof ProfileInfoOpenAPI){

			$login = $this->getCorrectLogin( $profile );
			
			if($this->mailExists($profile->mail))
				$mail = 'NULLME';
			else $mail = $profile->mail;

			$userdata = array(
					'user_pass' => $this->generatePWD(),
					'user_login' => $login,
					'user_nicename' => $login,
					'user_email' => $mail,
					'first_name' => $profile->first_name,
					'last_name' =>$profile->last_name,
				);

			// WP
			$user_id = wp_insert_user( $userdata );

			if($user_id instanceof WP_ERROR){
				var_dump($user_id);
				exit();
			}

			$this->dbCreateSocUser($profile, $user_id);

			$this->saveAvatar($profile, $user_id);

			$this->authorization($user_id);

			return $user_id;

		} else return false;
	}

	/**
	*	Отвязываем пользователя от соц. сети
	*/
	public function unlinkSocAccount( $user_id ){
	
		if(!isset($user_id) || !$user_id || empty($this->code)) return false;

		$this->connectDB();

		$sql = "DELETE FROM ".$this->tbl_user_social." 
					WHERE
						user_id=".$this->db->quote($user_id, PDO::PARAM_INT)."
						AND social=".$this->db->quote($this->code, PDO::PARAM_STR);
		
		$query = $this->db->prepare($sql);
		
		try {
			$query->execute();

			return true;

		} catch( PDOExecption $e ){

			print "Error!: " . $e->getMessage() . "</br>";
			$this->db->rollback();
		}

		return false;
	}

	/**
	*	Создание новой привязки к аккаунту соц. сети
	*/
	protected function dbCreateSocUser($profile, $user_id){
		
		if(!isset($user_id) || !$user_id || empty($profile->access_token)) return false;

		$this->connectDB();

		$sql = "INSERT INTO ".$this->tbl_user_social." SET
					user_id=".$this->db->quote($user_id, PDO::PARAM_INT)."
					, social=".$this->db->quote($this->code, PDO::PARAM_STR)."
					, access_token=".$this->db->quote($profile->access_token, PDO::PARAM_STR)."
					, soc_user_id=".$this->db->quote($profile->uid, PDO::PARAM_INT)."
					, soc_user_mail=".$this->db->quote($profile->mail, PDO::PARAM_STR)."
					, `date`=NOW()";
		
		$query = $this->db->prepare($sql);
		
		try {
			$query->execute();

			$r = $this->db->lastInsertId();

		} catch( PDOExecption $e ){

			print "Error!: " . $e->getMessage() . "</br>";
			$this->db->rollback();
		}

		return false;
	}

	/**
	*	Сохраняем аватар пользователя
	*/
	private function saveAvatar($profile, $user_id){
	
	}

	/**
	*	Изменяем хозяина у привязки к соц. сети
	*/
	protected function dbChangeSocUserMaster( $access_token, $socuser_id, $local_user_id ) {
		
		if(!isset($socuser_id) || !$socuser_id || empty($this->code)) return false;

		$this->connectDB();

		$sql = "UPDATE ".$this->tbl_user_social." 
					SET access_token=".$this->db->quote($access_token, PDO::PARAM_STR).",
						user_id=".$this->db->quote($local_user_id, PDO::PARAM_INT)."

					WHERE social=".$this->db->quote($this->code, PDO::PARAM_STR)." 
						AND soc_user_id=".$this->db->quote($socuser_id, PDO::PARAM_INT);
		
		$query = $this->db->prepare($sql);
		
		try {

			$query->execute();
			
			$this->authorization( $socuser_id );

			return true;

		} catch( PDOExecption $e ){

			print "Error!: " . $e->getMessage() . "</br>";
			$this->db->rollback();
		}

		return false;
	}

	/**
	*	Обновляем access_token пользователя
	*/
	protected function dbUpdateAccessToken( $access_token, $socuser_id ){

		if(!isset($socuser_id) || !$socuser_id || empty($this->code)) return false;

		$this->connectDB();

		$sql = "UPDATE ".$this->tbl_user_social." 
					SET access_token=".$this->db->quote($access_token, PDO::PARAM_STR)."
					WHERE social=".$this->db->quote($this->code, PDO::PARAM_STR)." 
						AND soc_user_id=".$this->db->quote($socuser_id, PDO::PARAM_INT);
		
		$query = $this->db->prepare($sql);
		
		try {

			$query->execute();
			
			$this->authorization( $socuser_id );

			return true;

		} catch( PDOExecption $e ){

			print "Error!: " . $e->getMessage() . "</br>";
			$this->db->rollback();
		}

		return false;

	}
	
	/**
	*	Достаем привязку пользователя к соц. сети по ID пользователя из соц. сети
	*/
	protected function dbGetUserSocial( $socuser_id ){
		
		if(!$socuser_id) return false;
		
		$this->connectDB();

		$sql = "SELECT *
					FROM ".$this->tbl_user_social."
					WHERE social=".$this->db->quote($this->code, PDO::PARAM_STR)."
						AND soc_user_id=".$this->db->quote($socuser_id, PDO::PARAM_INT);

		return $this->db->query($sql)->fetch();
	}

	/**
	*	Достаем привязку пользователя к соцсети по пользователю из нашей БД
	*/
	protected function dbGetUserBind( $user_id ) {
		
		if(!$user_id) return false;
		
		$this->connectDB();

		$sql = "SELECT *
					FROM ".$this->tbl_user_social."
					WHERE social=".$this->db->quote($this->code, PDO::PARAM_STR)."
						AND user_id=".$this->db->quote($user_id, PDO::PARAM_INT);

		return $this->db->query($sql)->fetch();
	}


	/*********************** Sys func ****************/

	/**
	*	Генерация пароля
	*/
	private function generatePWD(){
		$i = 0;
		$pass = '';
		while($i<8){ 
			$symb = chr(mt_rand(48,122));
			if(preg_match("/[0-9a-z]/",$symb)){
				$pass .= $symb;
				$i++;
			}
		}
		return $pass;
	}

	/**
	*	Делаем строку в латинице
	*/
	private function Transliter( $str ){
		$_rus = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','щ','ш','ъ','ы','ь','э','ю','я','А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Щ','Ш','Ъ','Ы','Ь','Э','Ю','Я',' ',',','.');
		$_lat = array('a','b','v','g','d','e','yo','zh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','cz','ch','sh','','i','','e','yu','ya','a','b','v','g','d','e','yo','zh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','cz','ch','sh','','i','','e','yu','ya','_','','');
		return str_replace($_rus, $_lat, $str);
	}

	/**
	*	Проверяем, существует ли е-мейл в системе
	*/
	private function mailExists( $mail ){
		
		return email_exists( $mail );
	}

	/**
	*	Проверяем, существует ли логин в системе
	*/
	private function loginExists( $login ){
		
		return username_exists( $login );
	}

	/**
	*	собираем логин
	*/
	private function getLogin($str){

		$login = $this->Transliter($str);
		$login = preg_replace('/[\W]/', '', $login);
		$err = $this->loginExists($login);
		$i=0;
		$login1 = $login;
		
		if(!empty($err)) do {
			$login = $login1.++$i;
			$err = $this->loginExists($login);
			if($i==500) break;
		} while(!empty($err));
		
		return $login;
	}

	/**
	*	Собираем логин пользователя (уникальный)
	*/
	private function getCorrectLogin($profile){

		if(isset($profile->nickname) && !empty($profile->nickname) && strlen($profile->nickname)>=3)
			$login = $this->getLogin($profile->nickname);
		elseif(isset($profile->last_name) && !empty($profile->last_name) && strlen($profile->last_name)>=3)
			$login = $this->getLogin($profile->last_name);
		elseif(isset($profile->first_name) && !empty($profile->first_name) && strlen($profile->first_name)>=3)
			$login = $this->getLogin($profile->first_name);
		else do {
			$login = $this->generatePWD();
			$err = $this->loginExists($login);
		} while(!empty($err));

		return $login;
	}

	/**
	*	Достаем залогиненого на текущий момент пользователя
	*	
	*	# return user_id
	*/
	protected function getLoggedUser(){
		return get_current_user_id();
	}

	/**
	*	Авторизация пользователя по социальному аккаунту
	*/
	private function authorization( $socuser_id ){
		
		$soc_info = $this->dbGetUserSocial( $socuser_id );
		wp_set_current_user( $soc_info['user_id'] );
		
		$current_user = wp_get_current_user();
		$creds = array();
		$creds['user_login'] = $soc_info['user_id'];
		$creds['remember'] = true;
		$creds['user_password'] = 'secretpass';
		$user = wp_signon($creds, false);
		
		if ( is_wp_error($user) ){
			var_dump($user->get_error_message());
			die;
		}
		
	}

}
?>
