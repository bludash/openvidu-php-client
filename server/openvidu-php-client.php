<?php
/**
 * OpenViduPhpClient example
 *
 * openVidu is a OpenVidu is a platform to facilitate the addition of video calls 
 * in your web or mobile application. More on: www.openvidu.io
 *
 * OpenViduPhpClient example gives your frontend (JS, vueJS, etc.) control over
 * your openVidu server instance: Create, manage, and delete openVidu sessions
 * and connection. Run this script on your own server and keep your client secret
 * secret!
 *
 * Inspired by legolabs/openvidu-php-rest-api (Github)
 *
 * @author  bludash
 * @version 0.2
 * @license MIT
 */
namespace blufoo\openVidu;

require_once ('./openViduClass.php');

const OPENVIDU_API_URL = 'https://openvidu.example.com';
const OPENVIDU_API_SECRET = 'YOURSECRETKEY';
const OPENVIDU_API_PORT = 443;
const OPENVIDU_API_USER = 'OPENVIDUAPP';

//Define session name:
$mySessionId = "sess-001"; 
//If you prefer a random name:
//$mySessionId =  "sess-" . generateRandomString( 12 );

// Instantiate OpenVidu REST API main object:
$openvidu = new OpenViduPhpClient(OPENVIDU_API_URL, OPENVIDU_API_SECRET, OPENVIDU_API_PORT, OPENVIDU_API_USER);


//Create session and if success: Start connecton:
if ( $openvidu->createSession( $mySessionId ) != 1) {
    $data = $openvidu->createConnection( $mySessionId, 'nospecialinfo' );

    header('Content-Type: application/json');
    echo json_encode( $data );
} else {
    $json = '{ 
        "errno" : "101", 
        "error" : "openvidu-php-client: Session not created." 
    }';

    header('Content-Type: application/json');
    echo( $json );
}

// Helper functions:
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function myLog($logMsg)
{
    $log  = $_SERVER['REMOTE_ADDR'].' - '.
            date("d.m.Y G:i:s").' - '.
            $_SERVER['HTTP_HOST'].'/'. basename($_SERVER["SCRIPT_FILENAME"]) . ' - '.
            $logMsg . PHP_EOL;

    file_put_contents('./log-'.date("Y-m-d").'.txt', $log, FILE_APPEND);
}

?>
