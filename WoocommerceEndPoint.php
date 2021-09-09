<?php


require 'XMLTOJSONCONVERTER.php';
class WoocommerceEndPoint
{
    use XMLTOJSONCONVERTER;

    public $anchorUrl = 'https://soap.anchordistributors.com/anchorwebservice.asmx';
    public $anchorUsername = 786424;
    public $anchorPassword = 'loveworld';


    public $loveworldUrl = 'https://loveworldbooks.org/newweb/wp-json/wc/v3/orders';
    public $loveworldUsername = 'ck_6b4041800f46a7e5866fc9ec25e069e7e5d7885f';
    public $loveworldPassword = 'cs_92aee06b50d52f3cef3065c40704881a66c29f91';

    /*
     * Fetch Shipping methods Handler
     */
    public function register_fetch_loveworld_orders_api()
    {
        register_rest_route('anchor-api/v1', 'fetch-internal-orders', array(
            'methods' => 'GET',
            'callback' => array($this, 'submit_order_to_anchor')
        ));
    }

    public function handle_loveworld_orders_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        $result  = $this->fetch_loveworld_orders('GET', 'https://loveworldbooks.org/newweb/wp-json/wc/v3/orders');

        if ($result){
            return new WP_REST_Response($result, 200);
        }
    }

    function fetch_loveworld_orders($method, $url, $data = false)
    {
        $wp_request_headers = array(
            'Authorization' => 'Basic ' . base64_encode( "$this->loveworldUsername:$this->loveworldPassword" )
        );
        $fetch_order_response = wp_remote_request(
            $url,
            array(
                'method'    => $method,
                'headers'   => $wp_request_headers
            )
        );
        return json_decode(wp_remote_retrieve_body($fetch_order_response));
    }

    /*
     * Fetch Shipping methods Handler
     */
    public function register_shipping_methods_api()
    {
        register_rest_route('anchor-api/v1', 'fetch-shipping-methods', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_shipping_methods_request')
        ));
    }

    public function handle_shipping_methods_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        $result  = $this->fetch_shipping_methods_from_anchor();

        if ($result){
            return new WP_REST_Response($result, 200);
        }
    }

    public function fetch_shipping_methods_from_anchor()
    {
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure
        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                             <soap:Body>
                                <GetShipMethods xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice" />
                             </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);
        curl_close($ch);

        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

         return $this->XMLtoJSON($response2);

    }


    /*
     * Fetch countries Handler
     */
    public function register_fetch_countries_api()
    {
        register_rest_route('anchor-api/v1', 'fetch-countries', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_fetch_countries_request')
        ));
    }

    public function handle_fetch_countries_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        $result  = $this->fetch_countries_from_anchor();

        if ($result){
            return new WP_REST_Response($result, 200);
        }
    }

    public function fetch_countries_from_anchor()
    {
        //Fetch data from anchor soap API
        //Data, connection, auth
//        $dataFromTheForm = $_POST['fieldName']; // request data from the form
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure

        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                              <soap:Body>
                                <GetCountries xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice" />
                              </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

//        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);

        return $this->XMLtoJSON($response2);

    }

    /*
     * Submit Order t Anchor Websevices
     */
    public function submit_order_to_anchor_api()
    {
        register_rest_route('anchor-api/v1', 'submit-order', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_submit_order_request')
        ));
    }

    public function handle_submit_order_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        $result  = $this->submit_order_to_anchor();

        if ($result){
            return new WP_REST_Response($result, 200);
        }
    }

    public function submit_order_to_anchor($order_id)
    {
//        $order_id = 914;

        $orderUrl = "https://loveworldbooks.org/newweb/wp-json/wc/v3/orders/$order_id";

         $order  = $this->fetch_loveworld_orders('GET', $orderUrl);

        if ($order){

            // Generate Shipping method
            $shipping_method = 8;
            foreach ($order->meta_data as $data){
                if ($data->key == 'billing_shipping_method'){
                    $shipping_method = $data->value;
                }
            }

            // Generate Ship to Account
            $shipAccount = $this->submit_ship_to_customer_account_from_order_to_anchor($order, $shipping_method);

             //Loop through each item in the list
            $order_result = false;
            foreach ($order->line_items as $item){
                // Generate xml post structure for each item
                $order_result = $this->generate_order_item_xml_to_anchor($item, $shipping_method, $order->shipping_total, $shipAccount);
            }

            return $order_result;
        }else{
            echo "Not found";
        }

    }

    Public function generate_order_item_xml_to_anchor($item, $shipping_method, $shipping_cost, $shipAccount){


        //Sending Data to anchor soap API
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password


        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                               <soap:Header>
                                    <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                      <Username>'.$this->anchorUsername.'</Username>
                                      <Password>'.$this->anchorPassword.'</Password>
                                    </SecurityHeader>
                              </soap:Header>
                              <soap:Body>
                                <SubmitOrderWithImprint xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                      <SOI>
                                        <Invoice_Seq_Id>0</Invoice_Seq_Id>
                                        <Bill_to_Seq_Id>'.$this->anchorUsername.'</Bill_to_Seq_Id>
                                        <Ship_to_Seq_Id>'.$shipAccount->SubmitShipToAccountWithErrorResponse->SubmitShipToAccountWithErrorResult.'</Ship_to_Seq_Id>
                                        <PO_Number>TEST IMPR</PO_Number>
                                        <Net>'.$item->price.'</Net>  
                                        <Flag_Rush_Order>N</Flag_Rush_Order>      
                                        <Date_Ship_By>09-DEC-2018</Date_Ship_By>
                                        <Shipping_Charge>'.$shipping_cost.'</Shipping_Charge>      
                                        <SO_Detail>
                                          <SalesOrderDetailImprint>
                                            <Product_Seq_Id>'.$item->sku.'</Product_Seq_Id>
                                            <Order_Quantity>'.$item->quantity.'</Order_Quantity>
                                            <Ship_Quantity>'.$item->quantity.'</Ship_Quantity>
                                            <Unit_Price>'.$item->price.'</Unit_Price>
                                            <Discount>0</Discount>
                                            <Extension>0</Extension>
                                            <Customer_ID>'.$this->anchorUsername.'</Customer_ID>
                                            <imprint_font_style_id>1</imprint_font_style_id>
                                            <imprint_text_line1>Line1 TEST</imprint_text_line1>
                                            <imprint_text_line2>Line2 test</imprint_text_line2>
                                            <indexing_color_id>1</indexing_color_id>
                                          </SalesOrderDetailImprint>
                                           <SalesOrderDetailImprint>
                                            <Product_Seq_Id>'.$item->sku.'</Product_Seq_Id>
                                            <Order_Quantity>'.$item->quantity.'</Order_Quantity>
                                            <Ship_Quantity>'.$item->quantity.'</Ship_Quantity>
                                            <Unit_Price>'.$item->price.'</Unit_Price>
                                            <Discount>0</Discount>
                                            <Extension>0</Extension>
                                            <Customer_ID>'.$this->anchorUsername.'</Customer_ID>
                                            <imprint_font_style_id>1</imprint_font_style_id>
                                            <imprint_text_line1>Line1 TEST</imprint_text_line1>
                                            <imprint_text_line2>Line2 test</imprint_text_line2>
                                            <indexing_color_id>1</indexing_color_id>
                                          </SalesOrderDetailImprint>
                                        </SO_Detail>
                                        <Flag_All_Complete>N</Flag_All_Complete>
                                        <Ship_method_Seq_Id>'.$shipping_method.'</Ship_method_Seq_Id>
                                        <Store_Name>Loveworld Books</Store_Name>
                                        <Store_Message>Test Successful</Store_Message>
                                        <Store_Street>8623 Hemlock Hill Drive</Store_Street>
                                        <Store_City>Houston</Store_City>
                                        <Store_State>Texas</Store_State>
                                        <Store_ZIP>77083</Store_ZIP>
                                        <Store_Country>United States</Store_Country>
                                        <Intl_Tax_Number>0</Intl_Tax_Number>
                                        <Intl_Tax_Description>Test Description</Intl_Tax_Description>
                                        <Intl_Tax_Amount>'.$item->total_tax.'</Intl_Tax_Amount>
                                        <Special_Instruction>'.$item->name.'</Special_Instruction>
                                        <Date_Shipped>25-DEC-2018</Date_Shipped>
                                    </SOI>
                                      <sErrorCode>0</sErrorCode>
                                </SubmitOrderWithImprint>
                              </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

//        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML

        $parser = simplexml_load_string($response2);

        return $this->XMLtoJSON($response2);



    }



    /*
     * GetShipMethodsInternational (generates <Ship_to_Seq_Id>int</Ship_to_Seq_Id>)
     */
    public function register_get_ship_methods_international_api()
    {
        register_rest_route('anchor-api/v1', 'fetch-ship-methods-int', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_get_ship_methods_international_request')
        ));
    }

    public function handle_get_ship_methods_international_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        $result  = $this->fetch_ship_methods_international_from_anchor();

        if ($result){
            return new WP_REST_Response($result, 200);
        }
    }

    public function fetch_ship_methods_international_from_anchor()
    {
        //Fetch data from anchor soap API
        //Data, connection, auth
//        $dataFromTheForm = $_POST['fieldName']; // request data from the form
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure

        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                              <soap:Body>
                                 <GetShipMethodsInternational xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice" />
                              </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

//        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);

        return $this->XMLtoJSON($response2);

    }


    /*
     * Get processed Orders
     * Return ProcessedOrder Structure, given an Account Number(bill to), Date Start and Date end.
     * Returns Array of Processed Invoice(s) and tracking number(s) for specific date range.
     */
    public function register_get_processed_orders()
    {
        register_rest_route('anchor-api/v1', 'fetch-processed-orders', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_get_processed_orders_request')
        ));
    }

    public function handle_get_processed_orders_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        if (empty($params['date_from']) || empty($params['date_to'])){
            return new WP_REST_Response(array(['status' => 'error', 'message' => 'missing date_from or date_to params']), 400);
        }
           $result  = $this->fetch_processed_orders_from_anchor($params);
          return new WP_REST_Response($result, 200);

    }

    public function fetch_processed_orders_from_anchor($params)
    {
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure
        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                              <soap:Body>
                               <GetProcessedOrders xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Account_Number>'.$this->anchorUsername.'</Account_Number>
                                  <Date_From>'.$params['date_from'].'</Date_From>
                                  <Date_To>'.$params['date_to'].'</Date_To>
                              </GetProcessedOrders>  
                              </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);
        return $this->XMLtoJSON($response2);

    }

    /*
     *  Get Zapped Invoices
     */
    public function register_get_zapped_invoices_api()
    {
        register_rest_route('anchor-api/v1', 'fetch-zapped-invoices', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_get_zapped_invoices_request')
        ));
    }

    public function handle_get_zapped_invoices_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        if (empty($params['date_from']) || empty($params['date_to'])){
            return new WP_REST_Response(array(['status' => 'error', 'message' => 'missing date_from or date_to params']), 400);
        }
        $result  = $this->fetch_zapped_invoices_from_anchor($params);
        return new WP_REST_Response($result, 200);

    }

    public function fetch_zapped_invoices_from_anchor($params)
    {
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure
        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                              <soap:Body>
                              <GetZappedInvoices xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Account_Number>'.$this->anchorUsername.'</Account_Number>
                                  <Date_From>'.$params['date_from'].'</Date_From>
                                  <Date_To>'.$params['date_to'].'</Date_To>
                              </GetZappedInvoices>
                              </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);
        return $this->XMLtoJSON($response2);

    }


    /*
     * Get SHipping Charge
     * Return ShippingCharges Structure, given an Account Number(bill to), Date Start and Date end.
     * Returns Array of Processed Invoice(s) and Shipping Charge(s) for specific date range
     */
    public function register_get_shipping_charges_api()
    {
        register_rest_route('anchor-api/v1', 'fetch-shipping-charges', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_get_shipping_charges_request')
        ));
    }

    public function handle_get_shipping_charges_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        if (empty($params['date_from']) || empty($params['date_to'])){
            return new WP_REST_Response(array(['status' => 'error', 'message' => 'missing date_from or date_to params']), 400);
        }
        $result  = $this->fetch_shipping_charges_from_anchor($params);
        return new WP_REST_Response($result, 200);

    }

    public function fetch_shipping_charges_from_anchor($params)
    {
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure
        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                              <soap:Body>
                              <GetShippingCharges xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                 <Account_Number>'.$this->anchorUsername.'</Account_Number>
                                  <Date_From>'.$params['date_from'].'</Date_From>
                                  <Date_To>'.$params['date_to'].'</Date_To>
                              </GetShippingCharges>
                              </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);
        return $this->XMLtoJSON($response2);

    }

    /*
     * Get Shipping Rate
     *  Return a Decimal value, given Weight, Ship Method ID, and Zip Code.
     *  Use This to get Shipping Rates in the USA only
     */
    public function register_get_shipping_rate_api()
    {
        register_rest_route('anchor-api/v1', 'fetch-shipping-rate', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_get_shipping_rate_request')
        ));
    }

    public function handle_get_shipping_rate_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        if (empty($params['weight']) || empty($params['ship_method_id']) || empty($params['zip_code'])){
            return new WP_REST_Response(array(['status' => 'error', 'message' => 'missing weight or ship_method_id or zip_code params']), 400);
        }
        $result  = $this->fetch_shipping_rate_from_anchor($params);
        return new WP_REST_Response($result, 200);

    }

    public function fetch_shipping_rate_from_anchor($params)
    {
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure
        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                              <soap:Body>
                               <GetShippingRate xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Weight>'.$params['weight'].'</Weight>
                                  <Ship_Method_ID>'.$params['ship_method_id'].'</Ship_Method_ID>
                                  <ZipCode>'.$params['zip_code'].'</ZipCode>
                               </GetShippingRate>
                              </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);
        return $this->XMLtoJSON($response2);

    }


    /*
     * get Various Flat Rates
     * Return Various FLAT shipping Rates, given total number of items,
     * weight(use 1 if N/A), zipcode(use blank space if N/A), and country code ID
     */
    public function register_get_various_flat_rate_api()
    {
        register_rest_route('anchor-api/v1', 'fetch-various-flat-rate', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_get_various_flat_rate_request')
        ));
    }

    public function handle_get_various_flat_rate_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        if (empty($params['weight']) ||
            empty($params['total_items']) ||
            empty($params['country_seq_id'])){
            return new WP_REST_Response(
                array(['status' => 'error', 'message' => 'missing weight or total_items or country_seq_id, zip_code(optional)']), 400);
        }
        if (empty($params['zip_code'])){
            $params['zip_code'] = '';
        }
        $result  = $this->fetch_various_flat_rate_from_anchor($params);
        return new WP_REST_Response($result, 200);

    }

    public function fetch_various_flat_rate_from_anchor($params)
    {
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure
        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                              <soap:Body>
                              <GetVariousFlatRates xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <dWeight>'.$params['weight'].'</dWeight>
                                  <ZipCode>'.$params['zip_code'].'</ZipCode>
                                  <Country_Seq_ID>'.$params['country_seq_id'].'</Country_Seq_ID>
                                  <TotalItems>'.$params['total_items'].'</TotalItems>
                                  <sErrorCode></sErrorCode>
                              </GetVariousFlatRates>
                              </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);
        return $this->XMLtoJSON($response2);

    }

    /*
     * Get Various Ship Rate
     * Return Various Ship Rates, given weight, zipcode, and country code
     */
    public function register_get_various_ship_rate_api()
    {
        register_rest_route('anchor-api/v1', 'fetch-various-ship-rate', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_get_various_ship_rate_request')
        ));
    }

    public function handle_get_various_ship_rate_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        if (empty($params['weight']) ||
            empty($params['country_seq_id'])){
            return new WP_REST_Response(
                array(['status' => 'error', 'message' => 'missing weight or total_items or country_seq_id, zip_code(optional)']), 400);
        }
        if (empty($params['zip_code'])){
            $params['zip_code'] = '';
        }
        $result  = $this->fetch_various_ship_rate_from_anchor($params);
        return new WP_REST_Response($result, 200);

    }

    public function fetch_various_ship_rate_from_anchor($params)
    {
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure
        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                              <soap:Body>
                                  <GetVariousShipRates xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                      <dWeight>'.$params['weight'].'</dWeight>
                                      <ZipCode>'.$params['zip_code'].'</ZipCode>
                                      <Country_Seq_ID>'.$params['country_seq_id'].'</Country_Seq_ID>
                                      <sErrorCode></sErrorCode>
                                  </GetVariousShipRates>
                              </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);
        return $this->XMLtoJSON($response2);

    }

    /*
     * Submit Ship To Account
     * Return Integer, given a ShiptoCustomer Structure as input.
     * Return the Customer Ship To Account number.
     * leave customer_type_seq_id to 1, it will be assigned according to your Billing Default. Ship_method_seq_id also will follow the default, which usually is Fedex or UPS ground.
     * country_seq_id default is USA, its 282. Use GetCountries function to find out the correct
     */
    public function register_submit_ship_to_customer_account_api()
    {
        register_rest_route('anchor-api/v1', 'submit-ship-to-customer', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_submit_ship_to_customer_account_request')
        ));
    }

    public function handle_submit_ship_to_customer_account_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        if (empty($params['name']) ||
            empty($params['street1']) ||
            empty($params['street2']) ||
            empty($params['city']) ||
            empty($params['state']) ||
            empty($params['country']) ||
            empty($params['phone']) ||
            empty($params['zip_code']) ||
            empty($params['email']) ||
            empty($params['contact']) ||
            empty($params['country_seq_id'])){
            return new WP_REST_Response(
                array(['status' => 'error', 'message' => $params]), 400);
        }
        if (empty($params['fax'])){
            $params['fax'] = '';
        }
        $result  = $this->submit_ship_to_customer_account_to_anchor($params);
        return new WP_REST_Response($result, 200);

    }

    public function submit_ship_to_customer_account_to_anchor($params)
    {
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure
        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                              <soap:Body>
                                 <SubmitShipToAccountWithError xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                      <ShipToCustomer>
                                        <Bill_To_Seq_Id>'.$this->anchorUsername.'</Bill_To_Seq_Id>
                                        <Ship_To_Seq_Id>0</Ship_To_Seq_Id>
                                        <Name>'.$params['name'].'</Name>
                                        <Street1>'.$params['street1'].'</Street1>
                                        <Street2>'.$params['street2'].'</Street2>
                                        <City>'.$params['city'].'</City>
                                        <State>'.$params['state'].'</State>
                                        <ZipCode>'.$params['zip_code'].'</ZipCode>
                                        <Country>'.$params['country'].'</Country>
                                        <Telephone>'.$params['phone'].'</Telephone>
                                        <Fax>'.$params['fax'].'</Fax>
                                        <Email>'.$params['email'].'</Email>
                                        <Contact>'.$params['name'].'</Contact>
                                        <Customer_Type_Seq_Id>1</Customer_Type_Seq_Id>
                                        <Ship_method_Seq_Id>'.$params['ship_method_seq_id'].'</Ship_method_Seq_Id>
                                        <Country_Seq_Id>'.$params['country_seq_id'].'</Country_Seq_Id>
                                      </ShipToCustomer>
                                      <sErrorCode></sErrorCode>
                                 </SubmitShipToAccountWithError>
                              </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);
//        return $response2;
        $result = $this->XMLtoJSON($response2);
        return $result;

    }

    public function submit_ship_to_customer_account_from_order_to_anchor($order, $shipping_method)
    {
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure
        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                              <soap:Body>
                                 <SubmitShipToAccountWithError xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                      <ShipToCustomer>
                                        <Bill_To_Seq_Id>'.$this->anchorUsername.'</Bill_To_Seq_Id>
                                        <Ship_To_Seq_Id>0</Ship_To_Seq_Id>
                                        <Name>'.$order->shipping->first_name.'  '.$order->shipping->last_name.'</Name>
                                        <Street1>'.$order->address_1.'</Street1>
                                        <Street2>'.$order->address_2.'</Street2>
                                        <City>'.$order->city.'</City>
                                        <State>'.$order->shipping->state.'</State>
                                        <ZipCode>'.$order->shipping->postcode.'</ZipCode>
                                        <Country>'.$order->shipping->country.'</Country>
                                        <Telephone>'.$order->billing->phone.'</Telephone>
                                        <Fax>'.$order->billing->phone.'</Fax>
                                        <Email>'.$order->billing->email.'</Email>
                                        <Contact>'.$order->shipping->first_name.'  '.$order->shipping->last_name.'</Contact>
                                        <Customer_Type_Seq_Id>1</Customer_Type_Seq_Id>
                                        <Ship_method_Seq_Id>'.$shipping_method.'</Ship_method_Seq_Id>
                                        <Country_Seq_Id>282</Country_Seq_Id>
                                      </ShipToCustomer>
                                      <sErrorCode></sErrorCode>
                                 </SubmitShipToAccountWithError>
                              </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);
//        return $response2;
        $result = $this->XMLtoJSON($response2);
        return $result;

    }



    /*
     * Get Shipping Rate two
     * Return a Shipping_Rate Structure, given Weight, Ship Method ID, Zip Code, and Country Code.
        Used to get Shipping Rates in USA & International
     */
    public function register_get_shipping_rate_2_api()
    {
        register_rest_route('anchor-api/v1', 'fetch-shipping-rate-2', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_get_shipping_rate_2_request')
        ));
    }

    public function handle_get_shipping_rate_2_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        if (empty($params['weight']) || empty($params['ship_method_id']) || empty($params['zip_code']) || empty($params['country_seq_id'] )){
            return new WP_REST_Response(array(['status' => 'error', 'message' => 'missing weight or ship_method_id or zip_code, or country_seq_id params']), 400);
        }
        $result  = $this->fetch_shipping_rate_2_from_anchor($params);
        return new WP_REST_Response($result, 200);
    }

    public function fetch_shipping_rate_2_from_anchor($params)
    {
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure
        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                              <soap:Body>
                                  <GetShippingRate2 xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                      <Weight>'.$params['weight'].'</Weight>
                                      <Ship_Method_ID>'.$params['ship_method_id'].'</Ship_Method_ID>
                                      <ZipCode>'.$params['zip_code'].'</ZipCode>
                                      <Country_Seq_ID>'.$params['country_seq_id'].'</Country_Seq_ID>
                                 </GetShippingRate2>
                              </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);
        return $this->XMLtoJSON($response2);

    }

    /*
     * Get Shipping Rate three
     * Return a Shipping_Rate Structure, given Weight (in Decimal), Ship Method ID, Zip Code, and Country Code.
     *  Used to get Shipping Rates in USA & International
     */
    public function register_get_shipping_rate_3_api()
    {
        register_rest_route('anchor-api/v1', 'fetch-shipping-rate-3', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_get_shipping_rate_3_request')
        ));
    }

    public function handle_get_shipping_rate_3_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        if (empty($params['weight']) || empty($params['ship_method_id']) || empty($params['zip_code']) || empty($params['country_seq_id'] )){
            return new WP_REST_Response(array(['status' => 'error', 'message' => 'missing weight or ship_method_id or zip_code, or country_seq_id params']), 400);
        }
        $result  = $this->fetch_shipping_rate_3_from_anchor($params);
        return new WP_REST_Response($result, 200);
    }

    public function fetch_shipping_rate_3_from_anchor($params)
    {
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure
        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                              <soap:Body>
                                  <GetShippingRate3 xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                      <Weight>'.$params['weight'].'</Weight>
                                      <Ship_Method_ID>'.$params['ship_method_id'].'</Ship_Method_ID>
                                      <ZipCode>'.$params['zip_code'].'</ZipCode>
                                      <Country_Seq_ID>'.$params['country_seq_id'].'</Country_Seq_ID>
                                 </GetShippingRate3>
                              </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);
        return $this->XMLtoJSON($response2);

    }



    /*
     * Get Order Status (takes invoice_seq_id as parameter)
     * Return SalesOrderStatus Structure, given an Invoice Seq Id input.
     * Useful to see the Status of Sales Order, Tracking number(s) is included for Shipped Invoice.
     */
    public function register_get_order_status()
    {
        register_rest_route('anchor-api/v1', 'fetch-order-status', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_get_order_status_request')
        ));
    }

    public function handle_get_order_status_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        if (empty($params['invoice_Seq_id'])){
            return new WP_REST_Response(array(['status' => 'error', 'message' => 'missing invoice_Seq_id params']), 400);
        }

        $result  = $this->fetch_order_status_from_anchor($params);
        return new WP_REST_Response($result, 200);

    }

    public function fetch_order_status_from_anchor($params)
    {
        //Fetch data from anchor soap API
        //Data, connection, auth
//        $dataFromTheForm = $_POST['fieldName']; // request data from the form
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure

        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                              <soap:Body>
                               <GetOrderStatus xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Invoice_Seq_Id>'.$params['invoice_Seq_id'].'</Invoice_Seq_Id>
                                </GetOrderStatus>
                              </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

//        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);
        return $this->XMLtoJSON($response2);

    }


    /*
     * Get Shipped Invoice Detail
     * Return SalesOrder Structure, given an Invoice Seq ID.
     * Returns Order information and items.
     * Useful to check if any items is unfortunately not shipped due to out of stock.
     */
    public function register_get_shipped_invoice_details_api()
    {
        register_rest_route('anchor-api/v1', 'fetch-shipped-invoice-details', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_get_shipped_invoice_details_request')
        ));
    }

    public function handle_get_shipped_invoice_details_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        if (empty($params['invoice_Seq_id'])){
            return new WP_REST_Response(array(['status' => 'error', 'message' => 'missing invoice_Seq_id params']), 400);
        }

        $result  = $this->fetch_shipped_invoice_details_from_anchor($params);
        return new WP_REST_Response($result, 200);

    }

    public function fetch_shipped_invoice_details_from_anchor($params)
    {
        //Fetch data from anchor soap API
        //Data, connection, auth
//        $dataFromTheForm = $_POST['fieldName']; // request data from the form
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure

        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                              <soap:Body>
                                <GetShippedInvoiceDetails xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                      <Invoice_Seq_Id>'.$params['invoice_Seq_id'].'</Invoice_Seq_Id>
                                 </GetShippedInvoiceDetails>
                              </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

//        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);
        return $this->XMLtoJSON($response2);

    }


    /*
     * Get Order Detail (takes Invoice_Seq_Id as parameter)
     * Return SalesOrder Structure, given an Invoice Seq ID.
     * Returns Order information and items.
     * Useful to check if any items is unfortunately will not shipped due to out of stock or backordered.
     */
    public function register_get_order_details_api()
    {
        register_rest_route('anchor-api/v1', 'fetch-order-details', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_get_order_details_request')
        ));
    }

    public function handle_get_order_details_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        if (empty($params['invoice_Seq_id'])){
            return new WP_REST_Response(array(['status' => 'error', 'message' => 'missing invoice_Seq_id params']), 400);
        }

        $result  = $this->fetch_order_details_from_anchor($params);
        return new WP_REST_Response($result, 200);

    }

    public function fetch_order_details_from_anchor($params)
    {
        //Fetch data from anchor soap API
        //Data, connection, auth
//        $dataFromTheForm = $_POST['fieldName']; // request data from the form
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure

        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                              <soap:Body>
                                  <GetOrderDetails xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                      <Invoice_Seq_Id>'.$params['invoice_Seq_id'].'</Invoice_Seq_Id>
                                  </GetOrderDetails>
                              </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

//        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);
        return $this->XMLtoJSON($response2);

    }

    /*
     * Get Order Tracking
     * Return SalesOrderTracking Structure, given an Invoice Seq Id input.
     * Returns all package information for a shipped order: tracking number, weight, freight, and shipping method.
     */
    public function register_get_order_tracking_api()
    {
        register_rest_route('anchor-api/v1', 'fetch-order-tracking', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_get_order_tracking_request')
        ));
    }

    public function handle_get_order_tracking_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        if (empty($params['invoice_Seq_id']) || empty($params['account_number'])){
            return new WP_REST_Response(array(['status' => 'error', 'message' => 'missing invoice_Seq_id or account_number params']), 400);
        }

        $result  = $this->fetch_order_tracking_from_anchor($params);
        return new WP_REST_Response($result, 200);

    }

    public function fetch_order_tracking_from_anchor($params)
    {
        //Fetch data from anchor soap API
        //Data, connection, auth
//        $dataFromTheForm = $_POST['fieldName']; // request data from the form
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure

        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                              <soap:Body>
                                  <GetOrderTracking xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                      <Account_Number>'.$params['account_number'].'</Account_Number>
                                      <Invoice_Seq_Id>'.$params['invoice_Seq_id'].'</Invoice_Seq_Id>
                                  </GetOrderTracking>
                              </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

//        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);
        return $this->XMLtoJSON($response2);

    }

    /*
     * Get processed Invoices
     * Return Integer of Invoice Seq Id, given an Account Number(bill to), Date Start and Date end.
     * Returns Array of Shipped Invoice(s) for specific date_shipped range
     */
    public function register_get_processed_invoices()
    {
        register_rest_route('anchor-api/v1', 'fetch-processed-invoices', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_get_processed_invoices_request')
        ));
    }

    public function handle_get_processed_invoices_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        $result  = $this->fetch_processed_invoices_from_anchor();


        return new WP_REST_Response($result, 200);

    }

    public function fetch_processed_invoices_from_anchor()
    {
        //Fetch data from anchor soap API
        //Data, connection, auth
//        $dataFromTheForm = $_POST['fieldName']; // request data from the form
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure

        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                              <soap:Body>
                                <GetProcessedInvoices xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Account_Number>'.$this->anchorUsername.'</Account_Number>
                                  <Date_From>2010-05-24T18:13:00</Date_From>
                                  <Date_To>2021-05-24T18:13:00</Date_To>
                               </GetProcessedInvoices> 
                              </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

//        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);
        return $this->XMLtoJSON($response2);

    }

    /*
     *  Return Boolean, given an Invoice Seq ID, Customer Seq ID, Item Seq ID as input.
     *  Return TRUE if successful.
     */
    public function register_delete_order_api()
    {
        register_rest_route('anchor-api/v1', 'delete-order', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_delete_order_request')
        ));
    }

    public function handle_delete_order_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        if (empty($params['invoice_seq_id']) || empty($params['customer_id']) || empty($params['item_id'])){
            return new WP_REST_Response(array([
                'status' => 'error',
                'message' => 'Missing parameters invoice_seq_id or customer_id or item_id'
            ]), 400);
        }
        $result  = $this->delete_order_from_anchor($params);

        if ($result){
            return new WP_REST_Response($result, 200);
        }
    }

    public function delete_order_from_anchor($params)
    {
        //Fetch data from anchor soap API
        //Data, connection, auth
//        $dataFromTheForm = $_POST['fieldName']; // request data from the form
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure

        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                                <soap:Body>
                                    <DeleteOrder xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                      <InvoiceSeqId>'.$params['invoice_seq_id'].'</InvoiceSeqId>
                                      <CustomerID>'.$params['customer_id'].'</CustomerID>
                                      <ItemID>'.$params['item_id'].'</ItemID>
                                    </DeleteOrder>
                                  </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

//        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);
       return $this->XMLtoJSON($response2);

    }

    /*
     * Vendor sales By Date
     *  Daily sales report for vendor accounts. Date in format YYYY-MM-DD.
     */
    public function register_vendor_sales_by_date_api()
    {
        register_rest_route('anchor-api/v1', 'vendor-sales-by-date', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_vendor_sales_by_date_request')
        ));
    }

    public function handle_vendor_sales_by_date_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        if (empty($params['date'])){
            return new WP_REST_Response(array(['status' => 'error', 'message' => 'missing date (YYYY-MM-DD) params']), 400);
        }

        $result  = $this->fetch_vendor_sales_by_date_from_anchor($params);

        if ($result){
            return new WP_REST_Response($result, 200);
        }
    }

    public function fetch_vendor_sales_by_date_from_anchor($params)
    {
        //Fetch data from anchor soap API
        //Data, connection, auth
//        $dataFromTheForm = $_POST['fieldName']; // request data from the form
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure

        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                                <soap:Body>
                                    <VendorSalesByDate xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                      <Account_Number>'.$this->anchorUsername.'</Account_Number>
                                      <DataDate>'.$params['date'].'</DataDate>
                                    </VendorSalesByDate>
                                  </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

//        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);
        return $this->XMLtoJSON($response2);

    }

    /*
     * VendorInventoryAdjustmentsByDate
     * Daily inventory adjustment report for vendor accounts. Date in format YYYY-MM-DD.
     */
    public function register_vendor_inventory_adjustments_by_date_api()
    {
        register_rest_route('anchor-api/v1', 'inventory-adjustment-by-date', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_vendor_inventory_adjustments_by_date_request')
        ));
    }

    public function handle_vendor_inventory_adjustments_by_date_request($data)
    {
        $headers = $data->get_headers();
        $params  = $data->get_params();

        if (empty($params['date'])){
            return new WP_REST_Response(array(['status' => 'error', 'message' => 'missing date (YYYY-MM-DD) params']), 400);
        }

        $result  = $this->fetch_vendor_inventory_adjustments_by_date_from_anchor($params);
        return new WP_REST_Response($result, 200);


    }

    public function fetch_vendor_inventory_adjustments_by_date_from_anchor($params)
    {
        //Fetch data from anchor soap API
        //Data, connection, auth
//        $dataFromTheForm = $_POST['fieldName']; // request data from the form
        $soapUrl = $this->anchorUrl; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure

        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                              <soap:Header>
                                <SecurityHeader xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <Username>'.$this->anchorUsername.'</Username>
                                  <Password>'.$this->anchorPassword.'</Password>
                                </SecurityHeader>
                              </soap:Header>
                                <soap:Body>
                                    <VendorInventoryAdjustmentsByDate xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                         <Account_Number>'.$this->anchorUsername.'</Account_Number>
                                         <DataDate>'.$params['date'].'</DataDate>
                                    </VendorInventoryAdjustmentsByDate>
                                  </soap:Body>
                            </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
//            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        curl_close($ch);

//        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);
        return $this->XMLtoJSON($response2);

    }

}