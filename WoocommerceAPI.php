<?php

require 'WoocommerceEndPoint.php';

class WoocommerceAPI extends WoocommerceEndPoint
{
    public $url = ' https://loveworldbooks.org/newweb/wp-json/wc/v3/orders';
    public $username = 'ck_6b4041800f46a7e5866fc9ec25e069e7e5d7885f';
    public $password = 'cs_92aee06b50d52f3cef3065c40704881a66c29f91';


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