document.addEventListener("DOMContentLoaded", function () {
    if (typeof csf_vars === "undefined" || !csf_vars.stripePublicKey) {
        console.error("Stripe Public Key is missing or undefined!");
        return;
    }

    const stripe = Stripe(csf_vars.stripePublicKey);

    const checkoutButton = document.getElementById("checkout-button");

    checkoutButton.addEventListener("click", function (event) {
        event.preventDefault();

        const selectedCourse = document.getElementById("course").value;
        const name = document.getElementById("name").value;
        const email = document.getElementById("email").value;

        // Send the request to create the checkout session
        fetch("/wp-admin/admin-ajax.php?action=csf_create_checkout_session", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ name, email, course: selectedCourse }),
        })
        .then(response => response.json()) // Parse the JSON response
        .then(session => {
            // Check if the session ID exists in the response
            if (session.success && session.data && session.data.id) {
                // Redirect to Stripe checkout
                return stripe.redirectToCheckout({ sessionId: session.data.id });
            } else {
                console.error("Stripe session ID is missing or the response format is incorrect:", session);
                alert("Error: Unable to create checkout session.");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error: Something went wrong while processing the payment.");
        });
    });
});
