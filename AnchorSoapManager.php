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

    public $anchor_report;

    public function __construct()
    {
        add_action('init', array($this, 'submit_order_to_anchor'));


        // Woocommerce Operation automation hooks
        $this->instantiate_woocommerce_operation_automation_hooks();

        // Rest APIS for fetching Anchor Web services stuffs
        $this->instantiate_rest_api_hooks();

        // Short-codes hooks
        $this->instantiate_short_codes_hooks();

        //Woocomerce completed order hook
        add_action('woocommerce_payment_complete', array($this, 'submit_order_to_anchor'));

    }

    /*
    * Rest APIS for fetching Anchor Web services stuffs
    */
    public function instantiate_rest_api_hooks()
    {
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
        add_action('rest_api_init', array($this, 'register_get_various_flat_rate_api'));
        add_action('rest_api_init', array($this, 'register_get_various_ship_rate_api'));
        add_action('rest_api_init', array($this, 'register_submit_ship_to_customer_account_api'));
        add_action('rest_api_init', array($this, 'register_fetch_loveworld_orders_api'));


    }

    /*
     * Short-codes hooks
     */
    public function instantiate_short_codes_hooks()
    {
        //Add shortcode(Shows the form)
        add_shortcode('anchor-report', array($this, 'load_anchor_report'));
    }

    /*
     * Woocommerce Operation automation hooks
     */
    public function instantiate_woocommerce_operation_automation_hooks()
    {
        add_action('woocommerce_delete_order', array($this, 'delete_order_from_anchor'));



    }

    public function create_ship_to_customer_account_on_anchor($params)
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
                                        <Name>'.$params->shipping->first_name.'</Name>
                                        <Street1>'.$params->shipping->address_1.'</Street1>
                                        <Street2>'.$params->shipping->address_2.'</Street2>
                                        <City>'.$params->shipping->city.'</City>
                                        <State>'.$params->shipping->state.'</State>
                                        <ZipCode>'.$params->shipping->post_code.'</ZipCode>
                                        <Country>'.$params->shipping->country.'</Country>
                                        <Telephone>'.$params->shipping->phone.'</Telephone>
                                        <Fax>'.$params->shipping->phone.'</Fax>
                                        <Email>'.$params->billing->email.'</Email>
                                        <Contact>'.$params['name'].'</Contact>
                                        <Customer_Type_Seq_Id>1</Customer_Type_Seq_Id>
                                        <Ship_method_Seq_Id>8</Ship_method_Seq_Id>
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
        return $this->XMLtoJSON($response2);

    }

    public function prepare_woocommerce_order_for_anchor($order_id)
    {

        $order  = wc_get_order($order_id);
        // Create a Ship To User Account
        $shipToUser = $this->create_ship_to_customer_account_on_anchor($order);

        //Check if there's an error during Ship To User Account creation
        if (!empty($shipToUser->SubmitShipToAccountWithErrorResponse->SubmitShipToAccountWithErrorResult->sErrorCode)){
            return $this->anchor_report = "Error Occurred with Shipping Information"; //Do Not proceed
        }

        //Create the order
        $anchorAnchor =  $this->submit_woocommerce_order_to_anchor($order, $shipToUser);
        //Check if the Order is submitted to Anchor Successfully
        if ($anchorAnchor->SubmitOrderWithImprintResponse->SubmitOrderWithImprintResult == 'false'){
            return $this->anchor_report = "Error Occurred while submitting the Order to Anchor"; //Do Not proceed
        }

        $invoiceSeqId        = $anchorAnchor->SubmitOrderWithImprintResponse->SOI->Invoice_Seq_Id;
        $shipToCustomerSeqId = $anchorAnchor->SubmitOrderWithImprintResponse->SOI->Ship_to_Seq_Id;;
        return $this->anchor_report = "Order Successfully Created! Your Invoice Seq Id is: $invoiceSeqId, Your ship to customer Seq Id is: $shipToCustomerSeqId"; //Do Not proceed
    }

    public function submit_woocommerce_order_to_anchor($params, $shipToUser)
    {
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
                                <SubmitOrderWithImprint xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                      <SOI>
                                        <Invoice_Seq_Id>0</Invoice_Seq_Id>
                                        <Bill_to_Seq_Id>'.$this->anchorUsername.'</Bill_to_Seq_Id>
                                        <Ship_to_Seq_Id>'.$this->anchorUsername->SubmitShipToAccountWithErrorResponse->SubmitShipToAccountWithErrorResult.'</Ship_to_Seq_Id>
                                        <PO_Number>TEST PPM</PO_Number>
                                        <Net>15</Net>  
                                        <Flag_Rush_Order>N</Flag_Rush_Order>      
                                        <Date_Ship_By>09-DEC-2018</Date_Ship_By>
                                        <Shipping_Charge>5</Shipping_Charge>      
                                        <SO_Detail>
                                          <SalesOrderDetailImprint>
                                            <Product_Seq_Id>638119</Product_Seq_Id>
                                            <Order_Quantity>1</Order_Quantity>
                                            <Ship_Quantity>1</Ship_Quantity>
                                            <Unit_Price>12</Unit_Price>
                                            <Discount>0</Discount>
                                            <Extension>0</Extension>
                                            <Customer_ID>'.$this->anchorUsername.'</Customer_ID>
                                            <imprint_font_style_id>1</imprint_font_style_id>
                                            <imprint_text_line1>Line1 TEST</imprint_text_line1>
                                            <imprint_text_line2>Line2 test</imprint_text_line2>
                                            <indexing_color_id>1</indexing_color_id>
                                          </SalesOrderDetailImprint>
                                           <SalesOrderDetailImprint>
                                            <Product_Seq_Id>638119</Product_Seq_Id>
                                            <Order_Quantity>1</Order_Quantity>
                                            <Ship_Quantity>1</Ship_Quantity>
                                            <Unit_Price>12</Unit_Price>
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
                                        <Ship_method_Seq_Id>8</Ship_method_Seq_Id>
                                        <Store_Name>Loveworld Books</Store_Name>
                                        <Store_Message>Test Successful</Store_Message>
                                        <Store_Street>8623 Hemlock Hill Drive</Store_Street>
                                        <Store_City>Houston</Store_City>
                                        <Store_State>Texas</Store_State>
                                        <Store_ZIP>77083</Store_ZIP>
                                        <Store_Country>United States</Store_Country>
                                        <Intl_Tax_Number>0</Intl_Tax_Number>
                                        <Intl_Tax_Description>Test Description</Intl_Tax_Description>
                                        <Intl_Tax_Amount>0</Intl_Tax_Amount>
                                        <Special_Instruction>something</Special_Instruction>
                                        <Date_Shipped>25-DEC-2021</Date_Shipped>
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

        $url = $this->anchorUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->anchorUsername.":".$this->anchorPassword); // username and password - declared at the top of the doc
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

        $jsonObject =  $this->XMLtoJSON($response2);

        echo $jsonObject->SubmitOrderWithImprintResponse->SOI->Invoice_Seq_Id. ' | ' . $jsonObject->SubmitOrderWithImprintResponse->SOI->Ship_to_Seq_Id;

    }




    public function test_submit_woocommerce_order_to_anchor()
    {
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
                                <SubmitOrderWithImprint xmlns="http://tempuri.org/AnchorWebservice/AnchorWebservice">
                                      <SOI>
                                        <Invoice_Seq_Id>0</Invoice_Seq_Id>
                                        <Bill_to_Seq_Id>'.$this->anchorUsername.'</Bill_to_Seq_Id>
                                        <Ship_to_Seq_Id>7142057</Ship_to_Seq_Id>
                                        <PO_Number>TEST IMPR</PO_Number>
                                        <Net>15</Net>  
                                        <Flag_Rush_Order>N</Flag_Rush_Order>      
                                        <Date_Ship_By>09-DEC-2018</Date_Ship_By>
                                        <Shipping_Charge>5</Shipping_Charge>      
                                        <SO_Detail>
                                          <SalesOrderDetailImprint>
                                            <Product_Seq_Id>638119</Product_Seq_Id>
                                            <Order_Quantity>1</Order_Quantity>
                                            <Ship_Quantity>1</Ship_Quantity>
                                            <Unit_Price>12</Unit_Price>
                                            <Discount>0</Discount>
                                            <Extension>0</Extension>
                                            <Customer_ID>'.$this->anchorUsername.'</Customer_ID>
                                            <imprint_font_style_id>1</imprint_font_style_id>
                                            <imprint_text_line1>Line1 TEST</imprint_text_line1>
                                            <imprint_text_line2>Line2 test</imprint_text_line2>
                                            <indexing_color_id>1</indexing_color_id>
                                          </SalesOrderDetailImprint>
                                           <SalesOrderDetailImprint>
                                            <Product_Seq_Id>638118</Product_Seq_Id>
                                            <Order_Quantity>1</Order_Quantity>
                                            <Ship_Quantity>1</Ship_Quantity>
                                            <Unit_Price>12</Unit_Price>
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
                                        <Ship_method_Seq_Id>8</Ship_method_Seq_Id>
                                        <Store_Name>Loveworld Books</Store_Name>
                                        <Store_Message>Test Successful</Store_Message>
                                        <Store_Street>8623 Hemlock Hill Drive</Store_Street>
                                        <Store_City>Houston</Store_City>
                                        <Store_State>Texas</Store_State>
                                        <Store_ZIP>77083</Store_ZIP>
                                        <Store_Country>United States</Store_Country>
                                        <Intl_Tax_Number>0</Intl_Tax_Number>
                                        <Intl_Tax_Description>Test Description</Intl_Tax_Description>
                                        <Intl_Tax_Amount>0</Intl_Tax_Amount>
                                        <Special_Instruction>something</Special_Instruction>
                                        <Date_Shipped>25-DEC-2021</Date_Shipped>
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

        $jsonObject =  $this->XMLtoJSON($response2);

        echo $jsonObject->SubmitOrderWithImprintResponse->SOI->Invoice_Seq_Id. ' | ' . $jsonObject->SubmitOrderWithImprintResponse->SOI->Ship_to_Seq_Id;
     ?>
        <script>
            alert(<?php echo $jsonObject; ?>)
        </script>
    <?php

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

    public function load_anchor_report()
    { ?>
        <div class="simple-contact-form">
            <p class="text text-danger"><?php echo $this->anchor_report; ?></p>
        </div>
    <?php }
}

new AnchorSoapManager;