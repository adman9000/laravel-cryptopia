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
     * Constructor for CryptopiaAPI
     *
     */
    function __construct()
    {
        //Initialise with the API key stored in config
        $this->key = config('cryptopia.auth.key');
        $this->secret = config('cryptopia.auth.secret');
        $this->url = config('cryptopia.urls.api');

        //Initialise curl
        $this->curl = curl_init();
        curl_setopt_array($this->curl, array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_USERAGENT => 'Cryptopia PHP API Agent',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FRESH_CONNECT => TRUE
          )
        );
        
    }

    /**
     * Destructor function
     **/
    function __destruct()
    {
        curl_close($this->curl);
    }
    
    
    /**
     * setAPI()
     * @param $key - API key
     * @param $secret - API secret
     * We can change the API key to access different accounts
     **/
    function setAPI($key, $secret) {

       $this->key = $key;
       $this->secret = $secret;
    }


    /**
     ---------- PUBLIC FUNCTIONS ----------
    * getTicker
    * getCurrencies
    * getAssetPairs (for backwards compatibility)
    * getMarkets (calls getAssetPairs)
    *
    *
    *
    * 
     **/

     /**
     * getTicker()
     *
     * @param $currency - optional currency to retrieve price data for, leave blank for all
     * @return asset pair ticker info
     */
    public function getTicker($currency=false)
    {
        $t = $this->request("GetMarkets".($currency ? "/$currency" : ""));
        return $t['Data'];
    }


    /**
     * getCurrencies()
     * @return array of currencies available on this exchange
     **/
    public function getCurrencies()
    {
        $t = $this->request("GetCurrencies");
        return $t['Data'];
    }

     /**
     * getAssetPairs()
     * @return array of trading pairs available on this exchange
     **/
    public function getAssetPairs()
    {
        $t = $this->request("GetTradePairs");
        return $t['Data'];
    }

    /**
     * getMarkets()
     * @return array of trading pairs available on this exchange
     **/
    public function getMarkets()
    {
        return $this->getAssetPairs();
    }



    /**
     ---------- PRIVATE ACCOUNT FUNCTIONS ----------
    * getBalances
    * getRecentTrades
    * getOpenOrders
    * getAllOrders (false)
    * trade
    * marketSell
    * marketBuy
    * limitSell
    * limitBuy
     **/

     /**
     * getBalances()
     * @return array of currency balances for this account
     **/
    public function getBalances() {

        $b = $this->privateRequest("GetBalance");
        return $b['Data'];

    }

    /**
     * getRecentTrades()
     * Get trades for a specific account and symbol
     *
     * @param string $symbol Currency pair
     * @param int $limit     Limit of trades. Default. 100
     * @return mixed
     * @throws \Exception
     **/
    public function getRecentTrades($symbol = false, $limit = 100) {

        $data = array();
        if($symbol) $data['Market'] = $symbol;
        if($limit) $data['Count'] = $limit;

        $b = $this->privateRequest("GetTradeHistory", $data, "POST");
        return $b['Data'];

    }

    /**
     * getOpenOrders()
     * Get open orders for a specific account and symbol
     *
     * @param string $symbol Currency pair
     * @param int $limit     Limit of orders. Default. 100
     * @return mixed
     * @throws \Exception
     **/
    public function getOpenOrders($symbol = false, $limit = 100)
    {

        $data = array();
        if($symbol) $data['Market'] = $symbol;
        if($limit) $data['Count'] = $limit;

        $b = $this->queryPrivate('GetOpenOrders', $data, "POST");
        return $b['Data'];

    }

    /**
     * getAllOrders()
     * Not available in API
     *
     * @param string $symbol Currency pair
     * @param int $limit     Limit of orders. Default. 100
     * @return false
     **/
    public function getAllOrders($symbol = false, $limit = false) {
        return false;
    }


    /** trade()
     * @param $market - asset pair to trade
     * @param $amount - amount of trade asset
     * @param $type - BUY or SELL
     * @param $rate - limit price
     * @return
    **/
    public function trade($market, $amount, $type, $rate=false) {



        $data = [
            'Market' => $market,
            'Type' => $type,
            'Amount' => $amount,
            'Rate' => $rate
        ];

        $b = $this->privateRequest("SubmitTrade", $data, "POST");
    
        return $b;

    }

    /** marketSell()
     * @param $symbol - asset pair to trade
     * @param $amount - amount of trade asset
    */
    public function marketSell($symbol, $amount) {

        return false;

    }
    /** marketBuy()
     * @param $symbol - asset pair to trade
     * @param $amount - amount of trade asset
    */
    public function marketBuy($symbol, $amount) {

        return false;
        
    }

    /** limitSell()
     * @param $symbol - asset pair to trade
     * @param $amount - amount of trade asset
    */
    public function limitSell($market, $amount, $rate) {

        return $this->trade($market, $amount, "SELL", $rate);

    }

    /** marketSell()
     * @param $symbol - asset pair to trade
     * @param $amount - amount of trade asset
    */
    public function limitBuy($market, $amount, $rate) {

        return $this->trade($market, $amount, "BUY", $rate);
        
    }



      /**
     ---------- REQUESTS ----------
     **/

    /** request()
    * @param $url - append to the API url to create full request url
    * @param $params - additional parameters to send
    * @param $method - GET or POST
    * @return array from json decoded string
    * Handles the requests for publically accessible data
    **/
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
            curl_setopt($this->curl,CURLOPT_POST, true);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postdata);
        } else {
            curl_setopt($this->curl,CURLOPT_POST, false);
        }

        //Get result
        $result = curl_exec($this->curl);
        if($result===false)
            throw new \Exception('CURL error: ' . curl_error($this->curl));

         // decode results
        $result = json_decode($result, true);
        if(!is_array($result))
            throw new \Exception('JSON decode error');

        return $result;

    }


     /** privateRequest()
    * @param $url - append to the API url to create full request url
    * @param $params - additional parameters to send
    * @param $method - GET or POST
    * @return array from json decoded string
    * Handles the private requests for account data
    **/
    private function privateRequest($url, $params = [], $method = "GET") {

        $url = $this->url . $url;

        //Doesnt work with an empty params array...
        if(sizeof($params)==0) $params['a'] = 'b';

        //Authorisation & request code taken from PHP example on cryptopia API guide
        $nonce = explode(' ', microtime())[1];
        $post_data                  = json_encode( $params );
        $m                          = md5( $post_data, true );
        $requestContentBase64String = base64_encode( $m );
        $signature                  = $this->key . "POST" . strtolower( urlencode( $url ) ) . $nonce . $requestContentBase64String;
        $hmacsignature              = base64_encode( hash_hmac("sha256", $signature, base64_decode( $this->secret ), true ) );
        $header_value               = "amx " . $this->key . ":" . $hmacsignature . ":" . $nonce;
        $headers                    = array("Content-Type: application/json; charset=utf-8", "Authorization: $header_value");

        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode( $params ) );
        curl_setopt($this->curl, CURLOPT_URL, $url );
        $result = curl_exec($this->curl);


        if($result===false)
            throw new \Exception('CURL error: ' . curl_error($this->curl));

         // decode results
        $result = json_decode($result, true);
        if(!is_array($result))
            throw new \Exception('JSON decode error');

        return $result;

    }

}
