$(document).ready(function(){

    // $("#shipping_method_name").hide();
    // $("#shipping_cost").hide();

    function getSelectedText(elementId) {
        var elt = document.getElementById(elementId);
        if (elt.selectedIndex == -1)
            return null;
        return elt.options[elt.selectedIndex].text;
    }

    function getSelectValue() {
        var selectedValue = document.getElementById("shipping_method").value;
        var text = getSelectedText('shipping_method');

        $('#shipping_method_name').text(text);
        return selectedValue;
    }

    function fetchShippingCost() {
        // 1) Fetch shipping method sequence id
        getSelectValue();
        var shipping_method_seq_id =  getSelectValue();

        // 2) Fetch ZipCode

        // 3) Fetch Weight

        // 4) Fetch country sequence id


        $.ajax({
            url: "url",
            url: 'example.com/something',
            method: 'GET',
            data: { 'sample':'test' }, // => I want this data in success function.
            success: functiondata, textStatus, jqXHR) {
                //Do Something

            },
            error: function(xhr) {
                alert("Something went wrong");
            }
        });

        // $('#shipping_cost').text(selectedValue);
    }











    fetchShippingCost();
    $("#shipping_method").on("change keyup paste", function(){
        fetchShippingCost();
    });

});


