<?php

namespace AvangPhpApi;



class Base {

    /**
     *
     * @var string
     */
    private $_host;

    /**
     *
     * @var string
     */
    private $_key;

    public function __construct($host, $key) {
        $this->_host = $host;
        $this->_key = $key;
    }

    public function createRequest($controller, $action, $parameters) {
        $url = sprintf('%s/api/v1/%s/%s', $this->_host, $controller, $action);
        $headers = [
            'x-server-api-key:'.$this->_key,
            'content-type:'.'application/json',
        ];
        $body = json_encode($parameters);
     
		
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,$body);  //Post Fields
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
  $response = curl_exec($ch);		
 $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);			

	curl_close($ch);	
		
     
        if ($httpcode === 200) {
            $result =$response;
            $json = json_decode($result);
            if ($json->status == 'success') {
                return $json->data;
            } else {
               return false;
            }
        }
    }

}
