// AJAX
function alterQuantity(cartId, quantityValue, itemPrice) {
    const qty = parseInt(quantityValue);

    if (isNaN(qty) || qty <= 0) {
        alert("Quantity entries must be structural positive numbers above 0.");
        return;
    }

    fetch('/api/cart/update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cart_id: cartId, quantity: qty })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const subtotalField = document.getElementById('subtotal-' + cartId);
            const calculatedSubtotal = qty * itemPrice;
            subtotalField.innerText = '$' + calculatedSubtotal.toFixed(2);

            document.getElementById('cart-grand-total').innerText = '$' + data.newGrandTotal.toFixed(2);
        } else {
            alert(data.message);
            location.reload(); // if stock limit hits
        }
    })
    .catch(err => console.error("AJAX Error updating item:", err));
}

// AJAX
function removeItem(cartId) {
    if (!confirm("Are you sure you want to remove this item from your cart?")) return;

    fetch('/api/cart/remove', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cart_id: cartId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const tableRow = document.getElementById('cart-row-' + cartId);
            tableRow.remove();

            // Adjust grand totals
            const grandTotalContainer = document.getElementById('cart-grand-total');
            if(grandTotalContainer) {
                grandTotalContainer.innerText = '$' + data.newGrandTotal.toFixed(2);
            }

            if (data.newCartCount === 0) {
                location.reload(); // Refresh 
            }
        }
    })
    .catch(err => console.error("AJAX Error removing item:", err));
}