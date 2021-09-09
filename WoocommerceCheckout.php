<?php

require 'WoocommerceEndPoint.php';
class WoocommerceCheckout extends WoocommerceEndPoint
{

    public function add_custom_shipping() {
        // this is just before the end of the table
        print '
            <tr>
            <td>Shipping Method</td>
            <td>AIM Canada Post</td>
            </tr>
            <tr>
            <td>Shipping Cost</td>
            <td>$45.6</td>
            </tr>
        ';
    }

}