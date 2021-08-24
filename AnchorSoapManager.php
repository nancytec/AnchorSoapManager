<?php
/*
Plugin Name: Anchor Web service Api
Description: Plugin responsible for sending and receiving API request from Anchor web services.
Version: 1.0
Author: PencilEdge
License: A "Slug" license name e.g. GPL2
Text Domain: anchor-api
*/


if ( !defined('ABSPATH')) {
    echo 'What are you trying to do?';
    exit;
}

require 'WoocommerceAPI.php';

class AnchorSoapManager extends WoocommerceAPI {


    public $anchorUrl = 'https://soap.anchordistributors.com/anchorwebservice.asmx';
    public $anchorUsername = 786424;
    public $anchorPassword = 'loveworld';

    public $result;

    public function __construct()
    {
        add_action('init', array($this, 'fetch_processed_xml_orders_from_anchor'));

        //Add shortcode(Shows the form)
        add_shortcode('test-area', array($this, 'load_short_code'));

        //Woocomerce sompleted order hook
        add_action('woocommerce_thankyou',array(&$this, 'submit_order_to_anchor'),10,2);

        add_action('woocommerce_delete_order', array($this, 'delete_order_from_anchor'));


        /*
         * Rest APIS for fetching Anchor Web services stuffs
         */
        add_action('rest_api_init', array($this, 'register_shipping_methods_api'));
        add_action('rest_api_init', array($this, 'register_fetch_countries_api'));
        add_action('rest_api_init', array($this, 'register_delete_order_api'));
        add_action('rest_api_init', array($this, 'register_get_order_details_api'));
        add_action('rest_api_init', array($this, 'register_get_ship_methods_international_api'));
        add_action('rest_api_init', array($this, 'register_get_processed_orders'));
        add_action('rest_api_init', array($this, 'register_get_processed_invoices'));
        add_action('rest_api_init', array($this, 'register_get_order_status'));
        add_action('rest_api_init', array($this, 'register_get_order_tracking_api'));
        add_action('rest_api_init', array($this, 'register_get_shipped_invoice_details_api'));
        add_action('rest_api_init', array($this, 'register_get_shipping_charges_api'));
        add_action('rest_api_init', array($this, 'register_get_shipping_rate_api'));
        add_action('rest_api_init', array($this, 'register_get_shipping_rate_2_api'));
        add_action('rest_api_init', array($this, 'register_get_shipping_rate_3_api'));
        add_action('rest_api_init', array($this, 'register_get_zapped_invoices_api'));
        add_action('rest_api_init', array($this, 'register_vendor_sales_by_date_api'));
        add_action('rest_api_init', array($this, 'register_vendor_inventory_adjustments_by_date_api'));
        add_action('rest_api_init', array($this, 'submit_order_to_anchor_api'));



    }


    public function fetch_processed_xml_orders_from_anchor()
    {
        // $dataFromTheForm = $_POST['fieldName']; // request data from the form
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
                                  <Date_From>2019-05-24T18:13:00</Date_From>
                                  <Date_To>2021-01-24T18:13:00</Date_To>
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

//        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // converting to XML
        $parser = simplexml_load_string($response2);

        $this->result =  $response2;

        $result = $this->hhb_tohtml($response);
        $this->result = $this->XMLtoJSONENCODE($response2);

    }

    public function submit_order_xml_to_anchor()
    {
//        if ( ! $order_id )
//            return;
//
//        // Get an instance of the WC_Order object
//        $order = wc_get_order( $order_id );
//
//        // Get the order key
//        $order_key = $order->get_order_key();
//
//        // Get the order number
//        $order_key = $order->get_order_number();
//
//
//        // Loop through order items
//        foreach ( $order->get_items() as $item_id => $item ) {
//
//            // Get the product object
//            $product = $item->get_product();
//
//            // Get the product Id
//            $product_id = $product->get_id();
//
//            // Get the product name
//            $product_id = $item->get_name();
//        }



        //Sending Data to anchor soap API
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
                                  <SubmitOrder xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                  <SO>
                                        <Invoice_Seq_Id>3456</Invoice_Seq_Id>
                                        <Bill_to_Seq_Id>'.$this->anchorUsername.'</Bill_to_Seq_Id>
                                        <Ship_to_Seq_Id>'.$this->anchorUsername.'</Ship_to_Seq_Id>
                                        <PO_Number>65765</PO_Number>
                                        <Net>10.6</Net>
                                        <Flag_Rush_Order>Random</Flag_Rush_Order>
                                        <Date_Ship_By>Random</Date_Ship_By>
                                        <Shipping_Charge>3.6</Shipping_Charge>
                                        <SO_Detail>
                                          <SalesOrderDetail>
                                            <Product_Seq_Id>56743</Product_Seq_Id>
                                            <Order_Quantity>4</Order_Quantity>
                                            <Ship_Quantity>4</Ship_Quantity>
                                            <Unit_Price>8.5</Unit_Price>
                                            <Discount>0.00</Discount>
                                            <Extension>0.6</Extension>
                                            <Customer_ID>3345</Customer_ID>
                                          </SalesOrderDetail>
                                          <SalesOrderDetail>
                                            <Product_Seq_Id>56743</Product_Seq_Id>
                                            <Order_Quantity>4</Order_Quantity>
                                            <Ship_Quantity>4</Ship_Quantity>
                                            <Unit_Price>8.5</Unit_Price>
                                            <Discount>0.00</Discount>
                                            <Extension>0.6</Extension>
                                            <Customer_ID>3345</Customer_ID>
                                          </SalesOrderDetail>
                                        </SO_Detail>
                                        <Flag_All_Complete>No</Flag_All_Complete>
                                        <Ship_method_Seq_Id>23</Ship_method_Seq_Id>
                                        <Store_Name>Loveworld</Store_Name>
                                        <Store_Message>Christian Books</Store_Message>
                                      <Store_Street>*8623 Hemlock Hill Drive*</Store_Street>
                                        <Store_City>Houston</Store_City>
                                        <Store_State>Texas</Store_State>
                                        <Store_ZIP>77083</Store_ZIP>
                                        <Store_Country>USA</Store_Country>
                                        <Intl_Tax_Number>9.0</Intl_Tax_Number>
                                        <Intl_Tax_Description>Anything babe</Intl_Tax_Description>
                                        <Intl_Tax_Amount>0.6</Intl_Tax_Amount>
                                        <Special_Instruction>Nothing o</Special_Instruction>
                                        <Date_Shipped>12-4-2022</Date_Shipped>
                                      </SO>
                                </SubmitOrder>
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

        $result = $this->hhb_tohtml($response);
        $this->result = $result;

        // user $parser to get your data out of XML response and to display it.

        // Flag the action as done (to avoid repetitions on reload for example)
//        $order->update_meta_data( '_thankyou_action_done', true );
//        $order->save();
    }

    public function submit_ship_to_account()
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
                                <SubmitShipToAccount xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                      <ShipToCustomer>
                                        <Bill_To_Seq_Id>'.$this->anchorUsername.'</Bill_To_Seq_Id>
                                        <Ship_To_Seq_Id xsi:nil="true"></Ship_To_Seq_Id>
                                        <Name>Loveworld Books</Name>
                                        <Street1>8623 Hemlock Hill Drive</Street1>
                                        <Street2>8623 Hemlock Hill Drive</Street2>
                                        <City>Houston</City>
                                        <State>Texas</State>
                                        <ZipCode>77083</ZipCode>
                                        <Country>USA</Country>
                                        <Telephone>+1(800) 620-8522</Telephone>
                                        <Fax>35</Fax>
                                        <Email>sales@loveworldbooks.org</Email>
                                        <Contact>8623 Hemlock Hill Drive</Contact>
                                        <Customer_Type_Seq_Id>'.$this->anchorUsername.'</Customer_Type_Seq_Id>
                                        <Ship_method_Seq_Id>'.$this->anchorUsername.'</Ship_method_Seq_Id>
                                        <Country_Seq_Id>282</Country_Seq_Id>
                                      </ShipToCustomer>
                                </SubmitShipToAccount>
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

        $result = $this->hhb_tohtml($response);
        $this->result = $result;
    }

    public function post_order_to_anchor()
    {
        //Data, connection, auth
        $dataFromTheForm = $_POST['fieldName']; // request data from the form
        $soapUrl = "https://connecting.website.com/soap.asmx?op=DoSomething"; // asmx URL of WSDL
        $soapUser = $this->anchorUsername;  //  username
        $soapPassword = $this->anchorPassword; // password

        // xml post structure

        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                     <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                       <soap:Body>
                         <GetItemPrice xmlns="http://connecting.website.com/WSDL_Service"> // xmlns value to be set to your WSDL URL
                           <PRICE>'.$dataFromTheForm.'</PRICE> 
                         </GetItemPrice >
                       </soap:Body>
                     </soap:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
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

        // convertingc to XML
        $parser = simplexml_load_string($response2);
        // user $parser to get your data out of XML response and to display it.
    }

    public function fetch_successful_orders()
    {
       $this->result = $this->CallAPI('GET', $this->url);
    }



    public function load_short_code()
    { ?>
        <div class="simple-contact-form">
            <p><?php echo $this->result; ?></p>

        </div>
    <?php }
}

new AnchorSoapManager;