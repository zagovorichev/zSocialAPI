<?php
/***
*	Блок авторизации через соцсети
*
*	# Alexander Zagovorichev
*/
?>

<link type="text/css" href="github/socialAPI/css/style.css" rel="stylesheet">
<script src="github/socialAPI/js/zSocialAPI.js" type="text/javascript"></script>

<span class="tp_facebook_butt" onclick="zSocialAPI.openSocialPopupWindow({href: '/github/socialAPI/php/fb/oauth.php', width: 1000, height: 600, onSuccess: function(){ zSocialAPI.hSocialPopup.close(); window.location.reload();}})"></span>
<!--span class="tp_google_butt"></span-->
<span class="tp_vkontakte_butt" onclick="zSocialAPI.openSocialPopupWindow({href: '/github/socialAPI/php/vk/oauth.php', width: 800, height: 600, onSuccess: function(){ zSocialAPI.hSocialPopup.close(); window.location.reload();}})"></span>

<span class="tp_odnoklassniki_butt" onclick="zSocialAPI.openSocialPopupWindow({href: '/github/socialAPI/php/ok/oauth.php', width: 800, height: 600, onSuccess: function(){ zSocialAPI.hSocialPopup.close(); window.location.reload();}})"></span>
