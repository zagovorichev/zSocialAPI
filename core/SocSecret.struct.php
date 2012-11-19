<?php
/**
*	Секретные ключи доступа из соц. сетей
*	
*	Alexander Zagovorichev <zagovorichev@gmail.com>
*/

class SocSecret{
	/**
	*	Ключи приложения из Facebook.com
	*/
	protected $fb_config = array(
					'client_id' => '<FB CLIENT ID>',
					'client_secret' => '<FB CLIENT SECRET>',
				);

	/**
	* Ключи приложение вКонтакте
	*/
	protected $vk_config = array(
					'client_id' => '<VK CLIENT ID>',
					'client_secret' => '<VK CLIENT SECRET>'
				);
	
	/**
	* Ключи приложение на Одноклассниках
	*/
	protected $ok_config = array(
					'client_id' => '<OK CLIENT ID>',
					'client_secret' => '<OK CLIENT SECRET>',
					'client_pub' => '<OK CLIENT PUBLIC>',
				);
}
?>
