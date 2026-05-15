document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registerForm');
    const errorDisplay = document.getElementById('error-message');

    form.addEventListener('submit', (e) => {
        // Clear previous errors
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