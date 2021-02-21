<?php
/**
 * OpenViduPhpClient class for connection to openVidu REST-API version 2.16.x
 *
 * openVidu is a OpenVidu is a platform to facilitate the addition of video calls 
 * in your web or mobile application. More on: www.openvidu.io
 *
 * OpenViduPhpClient class only implements Session, Connection and Signal handling.
 * Contribute to implement Recording, Media Node and Others!
 *
 * openVidu REST-API documentation:
 * https://docs.openvidu.io/en/2.16.0/reference-docs/REST-API/
 *
 * Inspired by legolabs/openvidu-php-rest-api (Github)
 *
 * @author  bludash - hello@blufoo.com
 * @version 0.2
 * @license MIT
 */

namespace blufoo\openVidu;

/** OpenVidu REST API Client main class
 *
 **/
class OpenViduPhpClient
{
	private $server;
	private $port;
	private $username;
	private $secret;
	private $url;

	/** Constructor
	 * @param string $server
	 *        	URL to openVidu server (REST-API)
	 * @param string $secret
	 *        	OpenVidu secret
	 * @param int $port
	 *        	openvidu service port (default = 4443)
	 * @param string $username
	 *        	OpenVidu service username (default = OPENVIDUAPP) */
	function __construct(string $server, string $secret, int $port = 443, string $username = 'OPENVIDUAPP')
	{
        $this->server = $server;
		$this->port = $port;
		$this->username = $username;
		$this->secret = $secret;

		$this->url = "{$this->server}:{$port}";
	}

	/** Send request to OpenVidu server
	 *
	 * @param object    $req          request object as documenten in openVidu's REST-API doc:
     *      $req->api_method          GET / POST
     *      $req->api_url             Url of API endpoint (excluding IP and Port)
     *      $req->http_headers[]      Headers as defined in openVidu REST-API docs (optional)
     *      $req->parameters[]        POST parameters (optional)
     * @throws \Exception
	 * @return object|NULL decoded JSON request response from OpenVidu server */
	private function send_request( $req ): ?object
    {
        // Build final API request URL:
        $complete_api_url = "{$this->url}/openvidu/api/{$req->api_url}";

        // cURL request composition:
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //noch ändern!
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if (count($req->http_headers) > 0)
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $req->http_headers);
        }

        switch ($req->api_method)
        {
            case "POST":
                curl_setopt($ch, CURLOPT_POST, 1);

                if (count($req->parameters) > 0)
                {
                    $str_parameters = json_encode($req->parameters);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $str_parameters);
                }
                break;

            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
        }

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->secret}");

        curl_setopt($ch, CURLOPT_URL, $complete_api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if (($result = curl_exec($ch)) === false) //Request error:
        {
            $errno = curl_errno( $ch );
            $errmsg = curl_error( $ch ) ;
            curl_close( $ch );

            $json = '{ "errno" : "$errno", "error" : "$errmsg" }';
            return json_decode( $json );
        }
        //Request OK, return decoded JSON response or nothing (when session already exists):
        else
        { 
            curl_close( $ch );

            return json_decode( $result );
        }
    }

	/** Create a new openVidu session (room)
	 *
	 * @param   string  $sessionId      Name of the session to create (optional, otherwise creating random name)
	 * @return  object  JSON/1/0        Returns session object/ 1=exception / 0=ok. */
    public function createSession( $sessionId ): ?bool
    {
        $req->api_method = 'POST';
        $req->api_url = "sessions";
        $req->http_headers[] = "Content-Type: application/json";
        $req->parameters["customSessionId"] = $sessionId; 
        // Unused parameters:
        //$req->parameters["mediaMode"] = 'ROUTED';
        //$req->parameters["recordingMode"] = 'MANUAL';
        //$req->parameters["defaultOutputMode"] = 'COMPOSED';
        //$req->parameters["defaultRecordingLayout"] = 'BEST_FIT';
        //$req->parameters["defaultCustomLayout"] = 'CUSTOM_LAYOUT';

        try {
            $data = $this->send_request( $req ); //Returns session object / nothing (when session already there) / errno.
            
            try {
                if ($data->errno != 0) { return $data->errno; };  
            } catch (Exception $e) {}

            try {
                if ($data = NULL) { return 0; };  
            } catch (Exception $e) {}

            return $data;

        } catch (Exception $e) {
            return 1;
        }
    }

	/** Create a new openVidu connection (client connection)
	 *
	 * @param   string  $sessionId      Name of the session to use
	 * @param   string  $dataString     Data to be passed to server (optional)
	 * @throws  \Exception
	 * @return  object|NULL             Decoded JSON request response from OpenVidu server 
     *          	                    (connection object including token) */
    public function createConnection( $sessionId, $dataString ): ?object
    {
        //Create a new connection and get a token:
        $req->api_method = 'POST';
        $req->api_url = "sessions/$sessionId/connection";
        $req->http_headers[] = "Content-Type: application/json";
        //
        $req->parameters["data"] = $dataString;
        $req->parameters["role"] = "PUBLISHER";

        try {
            $data = $this->send_request( $req );
            return $data;

        } catch (Exception $e) {
            return NULL;
        }
    }
}

?>
