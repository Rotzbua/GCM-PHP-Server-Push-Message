<?php
/**
 * Class to send push notifications using Google Cloud Messaging for Android
 *
 * Example usage
 * -----------------------
 * $an = new GCMPushMessage($apiKey);
 * $an->setDevices($devices);
 * $response = $an->send($data);
 * -----------------------
 * 
 * $apiKey Your GCM api key
 * $devices An array or string of registered device tokens
 * $data The mesasge you want to push out
 * 
 * @author Matt Grundy
 * 
 * Adapted from the code available at:
 * http://stackoverflow.com/questions/11242743/gcm-with-php-google-cloud-messaging
 */
class GCMPushMessage {
	
	private $url = 'https://android.googleapis.com/gcm/send';
	private $serverApiKey = '';
	private $devices = array();
	private $time_to_live = 0;
	
	/**
	 * Constructor
	 * @param $apiKeyIn the server API key
	*/
	public function __construct($apiKeyIn){
		$this->serverApiKey = $apiKeyIn;
	}
	/**
	 * Set the devices to send to
	 * @param $deviceIds array of device tokens to send to
	*/
	public function setDevices($deviceIds){
		if(is_array($deviceIds)){
			$this->devices = $deviceIds;
		} else {
			$this->devices = array($deviceIds);
		}
	}
	/**
	 * Set time to live
	 * @param $time time in seconds
	 */
	public function setTimeToLive($time){
		if(is_int($time) && 0 < $time){
			$this->time_to_live = $time;
		}
	}
	/**
	 * Send the message to the device
	 * @param $data Array of data to send
	*/
	public function send($data = false){
		
		if($data == false){
			$this->error('No data set');
		}
		
		if(!is_array($this->devices) || count($this->devices) == 0){
			$this->error('No devices set');
		}
		
		if(strlen($this->serverApiKey) < 8){
			$this->error('Server API Key not set');
		}
		
		$fields = array(
			'registration_ids'  => $this->devices,
			'data'              => $data,
		);
		
		if($this->time_to_live != 0){
			$fields['time_to_live'] = $this->time_to_live;
			$fields['delay_while_idle'] = TRUE;
		}
		
		$headers = array( 
			'Authorization: key=' . $this->serverApiKey,
			'Content-Type: application/json'
		);
		
		// Open connection
		$ch = curl_init();
		
		// Set the url, number of POST vars, POST data
		curl_setopt( $ch, CURLOPT_URL, $this->url );
		
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		
		curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields ) );
		
		// Avoids problem with https certificate
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		
		// Execute post
		$result = curl_exec($ch);
		
		// Close connection
		curl_close($ch);
		
		return $result;
	}
	/**
	 * Output of error
	 * @parm $msg error message
	 */
	protected function error($msg){
		echo 'Android send notification failed with error:';
		echo "\t" . $msg;
		exit(1);
	}
}
