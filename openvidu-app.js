/**
 * OpenViduPhpClient example
 *
 * openVidu is a OpenVidu is a platform to facilitate the addition of video calls 
 * in your web or mobile application. More on: www.openvidu.io
 *
 * This example is based on openvidu-tutorials/openvidu-insecure-js/web/app.js (Github)
 * But it is a secure version of it, because it doesn't store the server credentials.
 *
 * @author  bludash
 * @version 0.2
 * @license MIT
 */
 
var OV;
var session;
var mySessionId;

/* OPENVIDU METHODS */

function joinSession() {

	//Lets build a random user name:
	var myUserName = 'NameUser_' + Math.floor(Math.random() * 100);

	// --- 1) Get an OpenVidu object ---
	OV = new OpenVidu();

	// --- 2) Init a session ---
	session = OV.initSession();

	// --- 3) Specify the actions when events take place in the session ---
	// On every new Stream received...
	session.on('streamCreated', event => {

		// Subscribe to the Stream to receive it. HTML video will be appended to element with 'video-container' id
		var subscriber = session.subscribe(event.stream, 'video-container');

		// When the HTML video has been appended to DOM...
		subscriber.on('videoElementCreated', event => {

			// Add a new <p> element for the user's nickname just below its video
			appendUserData(event.element, subscriber.stream.connection);
		});
	});

	// On every Stream destroyed...
	session.on('streamDestroyed', event => {

		// Delete the HTML element with the user's nickname. HTML videos are automatically removed from DOM
		removeUserData(event.stream.connection);
	});


	// --- 4) Connect to the session with a valid user token ---
	createTokenPromise().then(token => {

		// First param is the token got from OpenVidu Server. Second param can be retrieved by every user on event
		// 'streamCreated' (property Stream.connection.data), and will be appended to DOM as the user's nickname
		session.connect(token, { clientData: myUserName })
			.then(() => {

				// --- 5) Set page layout for active call ---
				document.getElementById('session-title').innerText = mySessionId;
				document.getElementById('join').style.display = 'none';
				document.getElementById('session').style.display = 'block';

				// --- 6) Get your own camera stream with the desired properties ---
				var publisher = OV.initPublisher('video-container', {
					audioSource: undefined, // The source of audio. If undefined default microphone
					videoSource: undefined, // The source of video. If undefined default webcam
					publishAudio: true,  	// Whether you want to start publishing with your audio unmuted or not
					publishVideo: true,  	// Whether you want to start publishing with your video enabled or not
					resolution: '640x480',  // The resolution of your video
					frameRate: 30,			// The frame rate of your video
					insertMode: 'APPEND',	// How the video is inserted in the target element 'video-container'
					mirror: false       	// Whether to mirror your local video or not
				});

				// --- 7) Specify the actions when events take place in our publisher ---
				// When our HTML video has been added to DOM...
				publisher.on('videoElementCreated', function (event) {
					initMainVideo(event.element, myUserName);
					appendUserData(event.element, myUserName);
					event.element['muted'] = true;
				});

				// --- 8) Publish your stream ---
				session.publish(publisher);

			})
			.catch(error => {
				console.log('There was an error connecting to the session:', error.code, error.message);
			});
	})			
	.catch(error => {
		//console.log('There was an error connecting to the session:', error.code, error.message);
		console.log('There was an error getting the token.', error.code, error.message);
	});
}

function leaveSession() {
	// --- 9) Leave the session by calling 'disconnect' method over the Session object ---
	session.disconnect();

	// Removing all HTML elements with user's nicknames.
	// HTML videos are automatically removed when leaving a Session
	removeAllUserData();

	// Back to 'Join session' page
	document.getElementById('join').style.display = 'block';
	document.getElementById('session').style.display = 'none';
}


/* APPLICATION SPECIFIC METHODS */

window.onbeforeunload = function () {
	if (session) session.disconnect();
};

function appendUserData(videoElement, connection) {
	var userData;
	var nodeId;
	if (typeof connection === "string") {
		userData = connection;
		nodeId = connection;
	} else {
		//userData = JSON.parse(connection.data).clientData;
		nodeId = connection.connectionId;
	}
	var dataNode = document.createElement('div');
	dataNode.className = "data-node";
	dataNode.id = "data-" + nodeId;
	dataNode.innerHTML = "<p>" + userData + "</p>";
	videoElement.parentNode.insertBefore(dataNode, videoElement.nextSibling);
	addClickListener(videoElement, userData);
}

function removeUserData(connection) {
	var dataNode = document.getElementById("data-" + connection.connectionId);
	dataNode.parentNode.removeChild(dataNode);
}

function removeAllUserData() {
	var nicknameElements = document.getElementsByClassName('data-node');
	while (nicknameElements[0]) {
		nicknameElements[0].parentNode.removeChild(nicknameElements[0]);
	}
}

function addClickListener(videoElement, userData) {
	videoElement.addEventListener('click', function () {
		var mainVideo = $('#main-video video').get(0);
		if (mainVideo.srcObject !== videoElement.srcObject) {
			$('#main-video').fadeOut("fast", () => {
				$('#main-video p').html(userData);
				mainVideo.srcObject = videoElement.srcObject;
				$('#main-video').fadeIn("fast");
			});
		}
	});
}

function initMainVideo(videoElement, userData) {
	document.querySelector('#main-video video').srcObject = videoElement.srcObject;
	document.querySelector('#main-video p').innerHTML = userData;
	document.querySelector('#main-video video')['muted'] = true;
}

function createTokenPromise(sessionId) {
    return new Promise((resolve, reject) => {

		type = 'POST';
		url = './server/openvidu-php-client.php';
		data = JSON.stringify( {} ); //hier kann man daten übergeben im json-format.
		//
		var xhr = new XMLHttpRequest();
		xhr.open(type, url, true);
		xhr.setRequestHeader("Content-Type", "application/json");
		xhr.onload = function() {
		    if (this.status >= 200 && this.status < 300) { //hier bekommt man json-daten vom server.
				var json = JSON.parse(xhr.responseText);
				mySessionId = json.sessionId;
				resolve(json.token);
			} else {
				reject({
			  		status: this.status,
			  		statusText: xhr.statusText
				});
		  	}
		};
		xhr.onerror = function () {
		  	reject({
				status: this.status,
				statusText: xhr.statusText
		  	});
		};
		xhr.send(data);
    });
}
