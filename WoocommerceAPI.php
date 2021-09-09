<?php

require 'WoocommerceEndPoint.php';

class WoocommerceAPI extends WoocommerceEndPoint
{
    public $url = 'https://loveworldbooks.org/newweb/wp-json/wc/v3/orders';
    public $username = 'ck_6b4041800f46a7e5866fc9ec25e069e7e5d7885f';
    public $password = 'cs_92aee06b50d52f3cef3065c40704881a66c29f91';


    public $liveLoveworldUrl = 'https://loveworldbooks.org/newweb/wp-json/wc/v3/';
    public $liveLoveworldUsername = 'ck_a8c4321562c43cbc28fbc1c997f91ee703cf4c05';
    public $liveLoveworldPassword = 'cs_21ebe19064e7731d567e3d177d9d093f5d10c652';



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


    public function add_custom_shipping() {
        // this is just before the end of the table
        print '
            <tr>
            <td>Shipping Method</td>
            <td id="shipping_method_name">AIM Canada Post</td>
            </tr>
             <td>Shipping Country</td>
            <td id="shipping_country_name">USA</td>
            </tr>
            <tr>
            <td>Shipping Cost</td>
            <td id="shipping_cost">$45.6</td>
            </tr>
        ';
    }


    /*
     * Shipping method field plus validation
     */
    public function fetch_anchor_shipping_method_field($checkout)
    {
        woocommerce_form_field('custom_shipping_method', array(
            'type' => 'select',
            'class' => array('form-control'),
            'id'    => 'shipping_method',
            'label' => __('Shipping Method'),
            'required' => true,
            'options' => array(
                '314' => __('AIM Canada Post'),
                '121' => __('FedEx International Economy (DDU)'),
                '120' => __('FedEx International Priority (DDU)'),
                '606' => __('Int\'l Post (7-15 Days)'),
                '350' => __('POS Int\'l First Class Mail'),
                '351' => __('POS Int\'l Priority Mail'),
                '354' => __('UPS Standard to Canada (DDP)'),
            ),
        ),

            $checkout->get_value('custom_shipping_method'));
    }

    //Validate input field
    public function anchor_shipping_method_field_validation()
    {
        // Show an error message if the field is not set.
        if (!$_POST['custom_shipping_method']) wc_add_notice(__('Please select a shipping method!') , 'error');

    }

    //To be displayed in the edit order area in the admin section
    public function anchor_shipping_method_field_update_order_meta($order_id)
    {
        if (!empty($_POST['custom_shipping_method'])) {
            update_post_meta($order_id, 'Shipping Method',sanitize_text_field($_POST['custom_shipping_method']));
        }
    }

    //To be displayed in the admin order information area
    public function anchor_shipping_method_field_display_admin_order_meta($order){
        echo ''.__('custom_shipping_method').': ' . get_post_meta( $order->get_id(), 'custom_shipping_method', true ) . '';
    }



    /*
     * Shipping country field plus validation
     */
    function fetch_anchor_countries_field($checkout)
    {
        $url = 'https://loveworldbooks.org/newweb/wp-json/anchor-api/v1/fetch-countries';
        $method = 'GET';
        $data = false;

        $wp_request_headers = array(
            'Authorization' => 'Basic ' . base64_encode( "$this->liveLoveworldUsername:$this->liveLoveworldPassword")
        );
        $fetch_order_response = wp_remote_request(
            $url,
            array(
                'method'    => $method,
                'headers'   => $wp_request_headers
            )
        );

        $response =  json_decode(wp_remote_retrieve_body($fetch_order_response));
        $countries =  $response->GetCountriesResponse->GetCountriesResult->ListValue2;

        $newCountries = array();
        foreach ($countries as $country){
            $newCountries[$country->SEQ_ID] =  $country->Description_1;
        }

        echo '<h5>'.__('Shipping Info').'</h5>';
        woocommerce_form_field('custom_shipping_country', array(
            'type' => 'select',
            'class' => array('form-control'),
            'id'    => 'custom_shipping_country',
            'label' => __('Shipping Country'),
            'required' => true,
            'options' => $newCountries,
        ),
        $checkout->get_value('custom_shipping_country'));
    }

    //Validate input field
    public function anchor_countries_field_validation()
    {
        // Show an error message if the field is not set.
        if (!$_POST['custom_shipping_country']) wc_add_notice(__('Please select a shipping country!') , 'error');

    }

    //To be displayed in the edit order area in the admin section
    public function anchor_countries_field_update_order_meta($order_id)
    {
        if (!empty($_POST['custom_shipping_country'])) {
            update_post_meta($order_id, 'Shipping Country',sanitize_text_field($_POST['custom_shipping_country']));
        }
    }

    //To be displayed in the admin order information area
    public function anchor_countries_field_display_admin_order_meta($order){
        echo ''.__('custom_shipping_country').': ' . get_post_meta( $order->get_id(), 'custom_shipping_country', true ) . '';
    }

}