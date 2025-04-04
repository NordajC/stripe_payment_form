jQuery(document).ready(function($) {

    if (!csf_vars || !csf_vars.stripePublicKey) {
        console.error("Stripe Public Key is missing or undefined!");
        alert("Payment system error. Please try again later.");
        return;
    }

    const stripe = Stripe(csf_vars.stripePublicKey);

    function handlePaymentProcessing(event) {
        event.preventDefault(); // Prevent form submission
        console.log("Processing payment...");

        const checkoutButton = $(event.target); // jQuery equivalent of event.target
        const form = checkoutButton.closest("#payment-form");
        if (!form.length) {
            console.error("Payment form not found!");
            return;
        }

        // csf_vars
        const csrfNonce = csf_vars.nonce; // accessing nonce from csf_vars

        // Collect form values
        const requestBody = {
            name: form.find("#name").val().trim(),
            email: form.find("#email").val().trim(),
            phone: form.find("#phone").val().trim(),
            course: form.find("#course").val(),
            courseDates: form.find("#courseDates").val().trim(),
            proficiency: form.find("#proficiency").val().trim(),
            streetAddress: form.find("#streetAddress").val().trim(),
            postalCode: form.find("#postalCode").val().trim(),
            city: form.find("#city").val().trim(),
            country: form.find("#country").val(),
            emergencyName: form.find("#emergencyName").val().trim(),
            relationship: form.find("#relationship").val().trim(),
            emergencyPhone: form.find("#emergencyPhone").val().trim(),
            eng: form.find("#eng").val(),
            privacy: form.find("input[name='privacy']").prop("checked"),
            terms: form.find("input[name='terms']").prop("checked"),
        };

        // Validate required fields
        if (!requestBody.name || !requestBody.email || !requestBody.phone || !requestBody.course || !requestBody.country || !requestBody.privacy || !requestBody.terms) {
            alert("Please fill in all required fields.");
            return;
        }

        // Disable button to prevent multiple clicks
        checkoutButton.prop("disabled", true).text("Processing...");

        fetch(csf_vars.ajaxurl + "?action=csf_create_checkout_session", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": csrfNonce // CSRF Protection
            },
            body: JSON.stringify(requestBody),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(session => {
            if (session && session.success && session.data && session.data.id) {
                console.log("Redirecting to Stripe Checkout...");
                return stripe.redirectToCheckout({ sessionId: session.data.id });
            } else {
                throw new Error("Stripe session ID is missing or invalid");
            }
        })
        .then(result => {
            if (result.error) {
                console.error("Stripe Checkout error:", result.error.message);
                alert("An error occurred during checkout: " + result.error.message);
            }
        })
        .finally(() => {
            checkoutButton.prop("disabled", false).text("Pay for Course");
        });
    }

    // ✅ Use jQuery for event delegation to handle button clicks dynamically
    $(document).on("click", "#checkout-button", function(event) {
        handlePaymentProcessing(event);
    });
});
