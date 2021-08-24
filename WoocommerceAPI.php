<?php

require 'WoocommerceEndPoint.php';

class WoocommerceAPI extends WoocommerceEndPoint
{
    public $url = 'https://penciledge.net/apwb/wp-json/wc/v3/orders';
    public $username = 'ck_6edcb0747280fd98c97577f5fa9db827268f23a4';
    public $password = 'cs_84803adbb2652e8c447b27f879f1f9079ac366c0';


    function CallAPI($method, $url, $data = false)
    {
        $curl = curl_init();

        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            case "GET":
                curl_setopt($curl, CURLOPT_HTTPGET, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "$this->username:$this->password");

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }


}