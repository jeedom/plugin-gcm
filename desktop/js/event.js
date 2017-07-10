try {
	$.getScript( "https://www.gstatic.com/firebasejs/4.1.3/firebase.js", function( data, textStatus, jqxhr ) {
		jeedom.config.load({
			configuration: {'apiKey':'','authDomain':'','projectId':'','messagingSenderId':''},
			plugin : 'gcm',
			error: function (error) {
				$('#div_alert').showAlert({message: error.message, level: 'danger'});
			},
			success: function (config) {
				if(!isset(config.apiKey) || config.apiKey == ''){
					return;
				}
				try {
					firebase.initializeApp(config);
					const messaging = firebase.messaging();
					navigator.serviceWorker.register('./plugins/gcm/core/js/firebase-messaging-sw.js').then((registration) => {
						messaging.useServiceWorker(registration);
						messaging.requestPermission().then(function() {
							console.log('[GCM] Notification permission granted.');
						}).catch(function(err) {
							console.log('[GCM] Unable to get permission to notify.', err);
						});
						messaging.getToken().then(function(currentToken) {
							if (currentToken) {
								$.ajax({
									type: "POST",
									url: "plugins/gcm/core/ajax/gcm.ajax.php",
									data: {
										action: "checkAndCreate",
										id : currentToken,
									},
									dataType: 'json',
									global : false,
									error: function (request, status, error) {
										handleAjaxError(request, status, error);
									},
									success: function (data) {
										if (data.state != 'ok') {
											$('#div_alert').showAlert({message: data.result, level: 'danger'});
											return;
										}
									}
								});
							} else {
								console.log('[GCM] No Instance ID token available. Request permission to generate one.');
							}
						}).catch(function(err) {
							console.log('[GCM] An error occurred while retrieving token. ', err);
						});
						messaging.onMessage(function(payload) {
							notify(payload.notification.title, payload.notification.body);
						});
						messaging.onTokenRefresh(function() {
							messaging.getToken().then(function(refreshedToken) {
								$.ajax({
									type: "POST",
									url: "plugins/gcm/core/ajax/gcm.ajax.php",
									data: {
										action: "checkAndCreate",
										id : currentToken,
									},
									dataType: 'json',
									global : false,
									error: function (request, status, error) {
										handleAjaxError(request, status, error);
									},
									success: function (data) {
										if (data.state != 'ok') {
											$('#div_alert').showAlert({message: data.result, level: 'danger'});
											return;
										}
									}
								});
							}).catch(function(err) {
								console.log('[GCM] Unable to retrieve refreshed token ', err);
							});
						});
					});
				}catch (e) {
					console.log(e);
				}
			}
		});
	});
}catch (e) {
	console.log(e);
}



