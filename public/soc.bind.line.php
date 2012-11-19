<?php
//чет ../ не сработало, пропишем полный путь
require_once($_SERVER['DOCUMENT_ROOT']."/github/socialAPI/core/social.class.php");
?>

<table>
<tr> 
	<td> 

		<?php $bind = Social::factory('fb')->isUserBind($user_id); ?>

		<span id="fb_link" style="float:left; cursor: pointer; <?php if($bind) echo 'display: none;' ?>" onclick="zSocialAPI.openSocialPopupWindow({href: '/github/socialAPI/php/fb/oauth.php', width: 1000, height: 600, onSuccess: function(){ zSocialAPI.hSocialPopup.close(); document.getElementById('fb_link').style.display='none'; document.getElementById('fb_nolink').style.display=''; } })"> 
			<img src="/github/socialAPI/img/small/facebook.png" style="float: left"><span style="margin-left: 10px; font-size: 15px; font-weight: bold; color: #4183C4; line-height: 180%;">Привязать</span>
		</span>

		<span id="fb_nolink" style="float: left; cursor: pointer; <?php if(!$bind) echo 'display: none;'; ?>" onclick="zSocialAPI.unlinkSocialAccount({social: 'fb', onSuccess: function(){ document.getElementById('fb_link').style.display=''; document.getElementById('fb_nolink').style.display='none';} })">
			<img src="/github/socialAPI/img/small/facebook.png" style="float: left"><span style="margin-left: 10px; font-size: 15px; font-weight: bold; color: #777; line-height: 180%;">Отвязать</span>
		</span> 
	</td> 
</tr>

<tr> 
	<td> 

		<?php $bind = Social::factory('vk')->isUserBind($user_id); ?>

		<span id="vk_link" style="float:left; cursor: pointer; <?php if($bind) echo 'display: none;' ?>" onclick="zSocialAPI.openSocialPopupWindow({href: '/github/socialAPI/php/vk/oauth.php', width: 1000, height: 600, onSuccess: function(){ zSocialAPI.hSocialPopup.close(); document.getElementById('vk_link').style.display='none'; document.getElementById('vk_nolink').style.display=''; } })"> 
			<img src="/github/socialAPI/img/small/vkontakte.png" style="float: left"><span style="margin-left: 10px; font-size: 15px; font-weight: bold; color: #4183C4; line-height: 180%;">Привязать</span>
		</span>

		<span id="vk_nolink" style="cursor: pointer; <?php if(!$bind) echo 'display: none;'; ?>" onclick="zSocialAPI.unlinkSocialAccount({social: 'vk', onSuccess: function(){ document.getElementById('vk_link').style.display=''; document.getElementById('vk_nolink').style.display='none';} })">
			<img src="/github/socialAPI/img/small/vkontakte.png" style="float: left"><span style="margin-left: 10px; font-size: 15px; font-weight: bold; color: #777; line-height: 180%;">Отвязать</span>
		</span> 
	</td> 
</tr>

<tr> 
	<td> 

		<?php $bind = Social::factory('ok')->isUserBind($user_id); ?>

		<span id="ok_link" style="float:left; cursor: pointer; <?php if($bind) echo 'display: none;' ?>" onclick="zSocialAPI.openSocialPopupWindow({href: '/github/socialAPI/php/ok/oauth.php', width: 800, height: 600, onSuccess: function(){ zSocialAPI.hSocialPopup.close(); document.getElementById('ok_link').style.display='none'; document.getElementById('ok_nolink').style.display=''; } })"> 
			<img src="/github/socialAPI/img/small/odnoklassniki.png" style="float: left"><span style="margin-left: 10px; font-size: 15px; font-weight: bold; color: #4183C4; line-height: 180%;">Привязать</span>
		</span>

		<span id="ok_nolink" style=" cursor: pointer; <?php if(!$bind) echo 'display: none'; ?>;" onclick="zSocialAPI.unlinkSocialAccount({user_id: '<?php echo $user_id; ?>', social: 'ok', onSuccess: function(){ document.getElementById('ok_link').style.display=''; document.getElementById('ok_nolink').style.display='none';} })">
			<img src="/github/socialAPI/img/small/odnoklassniki.png" style="float: left"><span style="margin-left: 10px; font-size: 15px; font-weight: bold; color: #777; line-height: 180%;">Отвязать</span>
		</span> 
	</td> 
</tr>

</table>
