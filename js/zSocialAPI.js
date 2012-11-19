/**
*	zSocialAPI.js
*
*	не используются js framework'и во избежание конфликтов 	
*
*	# Alexander Zagovorichev
*/

var zSocialAPI = (function(){
	
	this.hSocialPopup; // Handler on window.open

	this.social_popup_options; // On success function

	/**
	*	like PHP isset()
	*/
	function isset() {
		if( arguments.length == 0) return false;

		var buff = arguments[0];

		for( var i=0; i < arguments.length; i++){

			if (typeof(buff)==='undefined' || buff === null) return false;
			buff = buff[arguments[i+1]];
		}

		return true;
	}

	/***
	*	Http connect
	*/
	function get_connect(){
		
		if(isset(zSocialAPI.xmlHttp)) return;

		var xmlHttp = false;

		try {
			xmlHttp = new XMLHttpRequest();
		} catch (trymicrosoft) {
			try {
				xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (othermicrosoft) {
				try {
					xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (failed) {
				  xmlHttp = false;
				}
			}
		}
		if (!xmlHttp) alert("Error init XMLHttpRequest");

		zSocialAPI.xmlHttp = xmlHttp;
	}

	/**
	*	Ajax query
	*
	*	# options: {
	*		type : 'post'/'get'
	*		url: URL
	*		params: Query string (param1=1&param2=2...)
	*		success: function
	*	# }
	*/
	function query( _options ){
	
		get_connect();

		var options = {
				type: 'post',
				url: '',
				params: '',
				success: function(){}
			};

		if(isset(_options.type)) options.type = _options.type;
		if(isset(_options.url)) options.url = _options.url;
		else alert('undefined remove url query options.url');
		if(isset(_options.params)) options.params = _options.params;
		if(isset(_options.success)) options.success = _options.success;

		zSocialAPI.xmlHttp.open(options.type, options.url, true);
    	
		//Send the proper header information along with the request
	    zSocialAPI.xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    	zSocialAPI.xmlHttp.setRequestHeader("Content-length", options.params.length);
	    zSocialAPI.xmlHttp.setRequestHeader("Connection", "close");

  		// Onsuccess function
		zSocialAPI.xmlHttp.onreadystatechange = options.success;

  		zSocialAPI.xmlHttp.send(options.params);
	}

	return {

		/**
		*	Попап окно доступа к аккаунтам соцсетей
		*	
		*	options = {link: '', width: 800, height: 600, onSuccess: function()}
		*/
		openSocialPopupWindow: function( _options ) {

			this.social_popup_options = {
				href: '',
				width: 800,
				height: 600,
				onSuccess: function(){
					zSocialAPI.hSocialPopup.close();
				}
			};

			if( isset(_options.href) )
				this.social_popup_options.href = _options.href;
			if( isset(_options.width) )
				this.social_popup_options.width = _options.width;
			if( isset(_options.height) )
				this.social_popup_options.height = _options.height;
			if( isset(_options.onSuccess) )
				this.social_popup_options.onSuccess = _options.onSuccess;

			// вычилсляем расположение открываемого попап окна
			var top,left;

			if(window.innerHeight > this.social_popup_options.height)
				top = (window.innerHeight - this.social_popup_options.height)/2;
			else top = 10;
			if(window.innerWidth > this.social_popup_options.width)
				left = (window.innerWidth - this.social_popup_options.width)/2;
			else left = 10;
			left += window.screenX;
			top += window.screenY;

			// Создание попап окна
			this.hSocialPopup = window.open(this.social_popup_options.href, 'zSocialOpenAPI', 'width='+this.social_popup_options.width+',height='+this.social_popup_options.height+',left='+left+',top='+top);

		}, // open SocialPopupWindow


		/**
		*	Popup onSuccess
		*/
		closePopupReload: function(){
			this.social_popup_options.onSuccess();
		},

		unlinkSocialAccount: function( _options ){
			
			var options = {
					social: '', // fb..vk..ok
					url: '/github/socialAPI/php/unlink.soc.account.php',
					onSuccess: function(){}
				};

			if(isset(_options.social)) options.social = _options.social;
			else alert('social undefined');
			if(isset(_options.url)) options.url = _options.url;
			if(isset(_options.onSuccess)) options.onSuccess = _options.onSuccess;
	
			query({
				url: options.url,
				params: 'social='+escape(options.social),
				success: options.onSuccess 
			});


		},

		wallPost: function( _options ){
			
			var options = {
				social: '', // fb..vk..ok
				url: '/github/socialAPI/php/wall.php',
				onSuccess: function(){}
			};

			if(isset(_options.url)) options.url = _options.url;
			if(isset(_options.social)) options.social = _options.social;
			else alert('social undefined');
			if(isset(_options.onSuccess)) options.onSuccess = _options.onSuccess;

			query({
				url: options.url,
				params: 'social='+escape(options.social),
				success: options.onSuccess
			});
		},

		/**
		*	Обрабатываем результат поста на стену вКонтакте
		*/
		vkWallPostSuccess: function(){

			if(!isset(zSocialAPI.xmlHttp)) return;

			if (zSocialAPI.xmlHttp.readyState == 4) {
				if (zSocialAPI.xmlHttp.status == 200 ){
					
					var res = zSocialAPI.xmlHttp.responseText;

				} // 200
			} // 4
		} // vkSuccess

	} //return
})()
