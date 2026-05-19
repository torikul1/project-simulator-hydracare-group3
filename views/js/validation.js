function validateCheckoutForm(event) {
    const shippingField = document.getElementById('shipping_address');
    const addressValue = shippingField.value.trim();
    
    const selectedPayment = document.querySelector('input[name="payment_method"]:checked');

    if (addressValue === "") {
        alert("Please provide a valid shipping destination address layout string.");
        event.preventDefault();
        return false;
    }

    if (!selectedPayment) {
        alert("Please pick an available payment processing module to finalize purchases.");
        event.preventDefault(); 
        return false;
    }

    return true;
}