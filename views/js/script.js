document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registerForm');
    const errorDisplay = document.getElementById('error-message');

    form.addEventListener('submit', (e) => {
        errorDisplay.innerText = "";
        errorDisplay.style.color = "#e74c3c";

        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const phone = document.getElementById('phone').value.trim();
        const address = document.getElementById('address').value.trim();

        // 1. Name Validation
        if (name.length < 3) {
            e.preventDefault();
            errorDisplay.innerText = "Name must be at least 3 characters.";
            return;
        }

        // 2. Email Validation (Simple Regex)
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            errorDisplay.innerText = "Please enter a valid email address.";
            return;
        }

        // 3. Password Validation (Min 8 chars)
        if (password.length < 8) {
            e.preventDefault();
            errorDisplay.innerText = "Password must be at least 8 characters long.";
            return;
        }

        // 4. Phone Validation (Numeric check)
        if (phone.length < 10 || isNaN(phone)) {
            e.preventDefault();
            errorDisplay.innerText = "Please enter a valid phone number.";
            return;
        }

        // 5. Address Validation
        if (address === "") {
            e.preventDefault();
            errorDisplay.innerText = "Address field cannot be empty.";
            return;
        }
    });
});




// views/js/script.js

document.addEventListener("DOMContentLoaded", () => {
    const searchBox = document.getElementById('searchBox');
    const catalogContainer = document.getElementById('catalogContainer');

    // Only run this logic if we are actually on the catalog page (to prevent errors on other pages)
    if (searchBox && catalogContainer) {
        
        // Handles asynchronous payload fetch actions
        function loadCatalog(query = '') {
            // Note the path change: since index.php runs this, the path goes back to ../controllers/
            fetch(`../controllers/search_controller.php?query=${encodeURIComponent(query)}`)
                .then(res => res.text())
                .then(htmlOutput => {
                    catalogContainer.innerHTML = htmlOutput;
                })
                .catch(err => console.error('Error fetching catalog matrix updates:', err));
        }


        loadCatalog();

        searchBox.addEventListener('input', (event) => {
            loadCatalog(event.target.value);
        });
    }
});





// views/js/script.js

document.addEventListener("DOMContentLoaded", () => {
    const searchBox = document.getElementById('searchBox');
    const catalogContainer = document.getElementById('catalogContainer');
    const cartItemsContainer = document.getElementById('cartItemsContainer');
    const cartOrderTotal = document.getElementById('cartOrderTotal');
    const checkoutBtn = document.getElementById('checkoutBtn');


    if (searchBox && catalogContainer) {
        function loadCatalog(query = '') {
            fetch(`../controllers/search_controller.php?query=${encodeURIComponent(query)}`)
                .then(res => res.text())
                .then(htmlOutput => {
                    catalogContainer.innerHTML = htmlOutput;
                })
                .catch(err => console.error('Error fetching catalog data:', err));
        }

        // Load inventory rows on dashboard startup
        loadCatalog();

        // Listen for live key input filtering
        searchBox.addEventListener('input', (event) => {
            loadCatalog(event.target.value);
        });
    }

    
    function renderSidebarUI(cart) {
        if (!cartItemsContainer) return;

        const cartKeys = Object.keys(cart);
        
        if (cartKeys.length === 0) {
            cartItemsContainer.innerHTML = `<p style="color: #94a3b8; font-size: 0.9rem; text-align: center; margin: 20px 0;">Your queue is currently empty.</p>`;
            cartOrderTotal.innerText = '৳0.00';
            checkoutBtn.disabled = true;
            return;
        }

        let htmlRows = '';
        let runningTotal = 0;

        cartKeys.forEach(id => {
            const item = cart[id];
            const itemTotal = item.price * item.quantity;
            runningTotal += itemTotal;

            htmlRows += `
                <div class="cart-item-node">
                    <div class="cart-item-info">
                        <h4>${item.name}</h4>
                        <p>${item.quantity} x ৳${item.price.toFixed(2)}</p>
                    </div>
                    <button class="cart-item-remove-btn" onclick="removeFromCart(${item.id})">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            `;
        });

        cartItemsContainer.innerHTML = htmlRows;
        cartOrderTotal.innerText = `৳${runningTotal.toFixed(2)}`;
        checkoutBtn.disabled = false;
    }

    // Fetch initial cart state when page loads to keep view in sync
    function syncCartData() {
        fetch('../controllers/cart_controller.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'fetch' })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderSidebarUI(data.cart);
            }
        });
    }

    if (cartItemsContainer) {
        syncCartData();
    }

    
    // 3. GLOBAL EXPOSURES FOR ONCLICK HANDLERS

    window.addToCart = function(medicineId) {
        fetch('../controllers/cart_controller.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add', medicine_id: medicineId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderSidebarUI(data.cart);
            } else {
                alert(data.message); // Alert stock or profile tracking bottlenecks safely
            }
        })
        .catch(err => console.error('Error adding to shopping queue:', err));
    };

    
    window.removeFromCart = function(medicineId) {
        // We will implement item-dropping backend router logic next!
        alert("Removing items feature integration coming up next!");
    };
});