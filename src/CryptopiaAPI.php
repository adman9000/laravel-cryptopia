<?php 
namespace adman9000\cryptopia;

class CryptopiaAPI
{
    protected $key;     // API key
    protected $secret;  // API secret
    protected $url;     // API base URL
    protected $version; // API version
    protected $curl;    // curl handle

    /**
     * Constructor for BinanceAPI
     *
     */
    function __construct()
    {
        $this->key = config('cryptopia.auth.key');
        $this->secret = config('cryptopia.auth.secret');
        $this->url = config('cryptopia.urls.api');
        $this->curl = curl_init();
        curl_setopt_array($this->curl, array(
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'Cryptopia PHP API Agent',
           // CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true)
        );
        
    }

    function __destruct()
    {
        curl_close($this->curl);
    }
    
    
    function setAPI($key, $secret) {

       $this->key = $key;
       $this->secret = $secret;
    }

     /**
     * Get ticker
     *
     * @return asset pair ticker info
     */
    public function getTicker($currency=false)
    {
        $t = $this->request("GetMarkets".($currency ? "/$currency" : ""));
        return $t['Data'];
    }

    public function getBalances() {

        $b = $this->privateRequest("GetBalance");
        return $b['Data'];

    }

    private function request($url, $params = [], $method = "GET") {
        $opt = [
            "http" => [
                "method" => $method,
                "header" => "User-Agent: Mozilla/4.0 (compatible; PHP Cryptopia API)\r\n"
            ]
        ];

        

         // build the POST data string
        $postdata = $params;


        // Set URL & Header
        curl_setopt($this->curl, CURLOPT_URL, $this->url . $url);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array());

        //Add post vars
        if($method == "POST") {
            curl_setopt($ch,CURLOPT_POST, count($params));
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postdata);
        }

        //Get result
        $result = curl_exec($this->curl);
        if($result===false)
            throw new \Exception('CURL error: ' . curl_error($this->curl));

         // decode results
        $result = json_decode($result, true);
        if(!is_array($result))
            throw new BinanceAPIException('JSON decode error');

        return $result;

    }

    private function privateRequest($url, $params = [], $method = "GET") {

        $url = $this->url . $url;

        //Doesnt work with an empty params array...
        if(sizeof($params)==0) $params['a'] = 'b';

        static $ch = null;
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Cryptopia.co.nz API PHP client; '.php_uname('s').'; PHP/'.phpversion().')');

         $nonce = explode(' ', microtime())[1];
        $post_data                  = json_encode( $params );
        $m                          = md5( $post_data, true );
        $requestContentBase64String = base64_encode( $m );
        $signature                  = $this->key . "POST" . strtolower( urlencode( $url ) ) . $nonce . $requestContentBase64String;
        $hmacsignature              = base64_encode( hash_hmac("sha256", $signature, base64_decode( $this->secret ), true ) );
        $header_value               = "amx " . $this->key . ":" . $hmacsignature . ":" . $nonce;
        $headers                    = array("Content-Type: application/json; charset=utf-8", "Authorization: $header_value");


        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $params ) );
          
          curl_setopt($ch, CURLOPT_URL, $url );
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
          curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $result = curl_exec($ch);


        if($result===false)
            throw new \Exception('CURL error: ' . curl_error($ch));

         // decode results
        $result = json_decode($result, true);
        if(!is_array($result))
            throw new \Exception('JSON decode error');

        return $result;

    }

}