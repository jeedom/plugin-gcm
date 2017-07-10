
importScripts('https://www.gstatic.com/firebasejs/3.9.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/3.9.0/firebase-messaging.js');

firebase.initializeApp({
  'messagingSenderId': '#messagingSenderId#'
});
const messaging = firebase.messaging()
messaging.setBackgroundMessageHandler(function(payload) {
  return self.registration.showNotification(payload.notification.title,{
    body: payload.notification.body,
    icon: payload.notification.icon
  });
});