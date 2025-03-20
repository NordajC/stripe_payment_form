// document.addEventListener("DOMContentLoaded", function () {
//     // Debug: Check the csf_vars object to see if the data is passed correctly
//     console.log(csf_vars); // This will print the entire object containing stripePublicKey and nonce

//     if (typeof csf_vars !== "undefined" && csf_vars.stripePublicKey) {
//         console.log("Stripe Public Key: " + csf_vars.stripePublicKey);
//     } else {
//         console.error("Stripe public key is not defined.");
//     }

//     // Proceed with your existing functionality
//     const stripe = Stripe(csf_vars.stripePublicKey);
    
//     const checkoutButton = document.getElementById("checkout-button");

    

//     checkoutButton.addEventListener("click", async function (event) {
//         event.preventDefault();

//         const csrfNonce = csf_vars.nonce; 

//         // Get form values
//         const name = document.getElementById("name").value.trim();
//         const email = document.getElementById("email").value.trim();
//         const phone = document.getElementById("phone").value.trim();
//         const selectedCourse = document.getElementById("course").value;
        
//         // Additional fields
//         const requestBody = {
//             name,
//             email,
//             phone,
//             course: selectedCourse,
//             courseDates: document.getElementById("courseDates").value.trim(),
//             proficiency: document.getElementById("proficiency").value.trim(),
//             streetAddress: document.getElementById("streetAddress").value.trim(),
//             postalCode: document.getElementById("postalCode").value.trim(),
//             city: document.getElementById("city").value.trim(),
//             country: document.getElementById("country").value,
//             emergencyName: document.getElementById("emergencyName").value.trim(),
//             relationship: document.getElementById("relationship").value.trim(),
//             emergencyPhone: document.getElementById("emergencyPhone").value.trim(),
//             eng: document.getElementById("eng").value,
//             privacy: document.querySelector("input[name='privacy']").checked,
//             terms: document.querySelector("input[name='terms']").checked,
//         };


//         // Validate required fields
//         if (!name || !email || !phone || !selectedCourse || !requestBody.country || !requestBody.privacy || !requestBody.terms) {
//             alert("Please fill in all required fields.");
//             return;
//         }

//         // Disable button to prevent multiple clicks
//         checkoutButton.disabled = true;
//         checkoutButton.textContent = "Processing...";

//         try {
//             // Send request to create checkout session
//             const response = await fetch("/wp-admin/admin-ajax.php?action=csf_create_checkout_session", {
//                 method: "POST",
//                 headers: { 
//                     "Content-Type": "application/json",
//                     "X-WP-Nonce": csf_vars.nonce // CSRF Protection
//                 },
//                 body: JSON.stringify(requestBody),
//             });

//             // Handle non-JSON responses
//             if (!response.ok) {
//                 throw new Error(`HTTP error! Status: ${response.status}`);
//             }

//             const session = await response.json();
            

//             // 
//             if (session && session.success && session.data && typeof session.data.id === "string" && session.data.id.length > 0) {
                
//                 await stripe.redirectToCheckout({ sessionId: session.data.id });
//             } else {
                
//                 alert("Payment processing failed. Please try again.");
//             }
            
            
//         } catch (error) {
            
//             alert("An error occurred while processing your payment. Please try again.");
//         } finally {
//             // Re-enable button
//             checkoutButton.disabled = false;
//             checkoutButton.textContent = "Pay for Course";
//         }
//     });
// });

// document.addEventListener("DOMContentLoaded", function () {
//     console.log("DOM fully loaded, initializing payment form...");

//     const checkoutForm = document.getElementById("payment-form");
//     const checkoutButton = document.getElementById("checkout-button");

//     if (!checkoutForm) {
//         console.error("Payment form not found!");
//         return;
//     }

//     if (!checkoutButton) {
//         console.error("Checkout button not found!");
//         return;
//     }

//     if (typeof csf_vars === "undefined" || !csf_vars.stripePublicKey) {
//         console.error("Stripe Public Key is missing or undefined!");
//         return;
//     }

//     // Make sure Stripe is loaded
//     if (typeof Stripe === "undefined") {
//         console.error("Stripe.js not loaded!");
//         return;
//     }

//     const stripe = Stripe(csf_vars.stripePublicKey);

//     // 🔹 REMOVE any event listeners added by form plugins
//     checkoutForm.outerHTML = checkoutForm.outerHTML; // This resets any existing event listeners


//     // ✅ Prevent default form submission
//     checkoutForm.addEventListener("submit", function(event) {
//         console.log("Preventing default form submission...");
//         event.preventDefault();
//         event.stopPropagation();
//     });

//     // ✅ Handle checkout button click
//     checkoutButton.addEventListener("click", function(event) {
//         event.preventDefault();
//         console.log("Checkout button clicked, processing payment...");

//         // Collect form data
//         const formData = new FormData(checkoutForm);
//         const requestBody = Object.fromEntries(formData.entries());

//         console.log("Collected form data:", requestBody);

//         // Validate required fields before sending request
//         if (!requestBody.name || !requestBody.email || !requestBody.phone || !requestBody.course) {
//             alert("Please fill in all required fields.");
//             console.error("Form validation failed: Missing required fields");
//             return;
//         }

//         // Disable button to prevent multiple clicks
//         checkoutButton.disabled = true;
//         checkoutButton.textContent = "Processing...";

//         // ✅ Make AJAX request
//         fetch(csf_vars.ajaxurl + "?action=csf_create_checkout_session", {
//             method: "POST",
//             headers: {
//                 "Content-Type": "application/json",
//                 "X-WP-Nonce": csf_vars.nonce
//             },
//             body: JSON.stringify(requestBody)
//         })
//         .then(response => {
//             console.log("Response status:", response.status);
//             return response.text().then(text => {
//                 try {
//                     return JSON.parse(text);
//                 } catch (e) {
//                     console.error("Failed to parse response as JSON:", text);
//                     throw new Error("Invalid server response");
//                 }
//             });
//         })
//         .then(data => {
//             console.log("Response data:", data);
//             if (data.success && data.data && data.data.id) {
//                 console.log("Redirecting to Stripe Checkout...");
//                 return stripe.redirectToCheckout({ sessionId: data.data.id });
//             } else {
//                 throw new Error("Invalid session data from server.");
//             }
//         })
//         .catch(error => {
//             console.error("Payment processing error:", error);
//             alert("Payment processing failed: " + error.message);
//         })
//         .finally(() => {
//             // Re-enable button after request
//             checkoutButton.disabled = false;
//             checkoutButton.textContent = "Pay for Course";
//         });
//     });
// });


// document.addEventListener("DOMContentLoaded", function () {
//     // Debugging: Check the csf_vars object
//     console.log(csf_vars); // This will print the entire object containing stripePublicKey and nonce

//     // Proceed only if the Stripe public key is available
//     if (typeof csf_vars !== "undefined" && csf_vars.stripePublicKey) {
//         console.log("Stripe Public Key: " + csf_vars.stripePublicKey);
//     } else {
//         console.error("Stripe public key is not defined.");
//         return; // Exit if Stripe public key is not found
//     }

//     // Proceed with the payment setup
//     const stripe = Stripe(csf_vars.stripePublicKey);
//     const checkoutForm = document.getElementById("payment-form");
//     const checkoutButton = document.getElementById("checkout-button");

//     // Debug: Check if form and button exist
//     console.log(checkoutForm, checkoutButton);
//     if (!checkoutForm || !checkoutButton) {
//         console.error("Form or checkout button not found!");
//         return;
//     }

//     // Prevent default form submission by pressing Enter or clicking submit
//     checkoutForm.addEventListener("submit", function (event) {
//         event.preventDefault(); // Prevent form submission
//         event.stopPropagation(); // Stop event propagation
//         console.log("Form submission intercepted!"); // Log to confirm interception
//         handlePaymentProcessing(); // Trigger payment processing
//     });

//     // Handle the checkout button click (if not using the submit button)
//     checkoutButton.addEventListener("click", function (event) {
//         event.preventDefault(); // Prevent button default action
//         console.log("Checkout button clicked, processing payment...");
//         handlePaymentProcessing(); // Trigger payment processing
//     });

//     // Function to handle the payment processing logic
//     async function handlePaymentProcessing() {
//         console.log("Processing payment...");

//         const csrfNonce = csf_vars.nonce;

//         // Collect form values
//         const name = document.getElementById("name").value.trim();
//         const email = document.getElementById("email").value.trim();
//         const phone = document.getElementById("phone").value.trim();
//         const selectedCourse = document.getElementById("course").value;
        
//         // Collect additional fields
//         const requestBody = {
//             name,
//             email,
//             phone,
//             course: selectedCourse,
//             courseDates: document.getElementById("courseDates").value.trim(),
//             proficiency: document.getElementById("proficiency").value.trim(),
//             streetAddress: document.getElementById("streetAddress").value.trim(),
//             postalCode: document.getElementById("postalCode").value.trim(),
//             city: document.getElementById("city").value.trim(),
//             country: document.getElementById("country").value,
//             emergencyName: document.getElementById("emergencyName").value.trim(),
//             relationship: document.getElementById("relationship").value.trim(),
//             emergencyPhone: document.getElementById("emergencyPhone").value.trim(),
//             eng: document.getElementById("eng").value,
//             privacy: document.querySelector("input[name='privacy']").checked,
//             terms: document.querySelector("input[name='terms']").checked,
//         };

//         // Validate required fields before making the request
//         if (!name || !email || !phone || !selectedCourse || !requestBody.country || !requestBody.privacy || !requestBody.terms) {
//             alert("Please fill in all required fields.");
//             return;
//         }

//         // Disable button to prevent multiple clicks
//         checkoutButton.disabled = true;
//         checkoutButton.textContent = "Processing...";

//         try {
//             // Send AJAX request to create the Stripe checkout session
//             const response = await fetch(csf_vars.ajaxurl + "?action=csf_create_checkout_session", {
//                 method: "POST",
//                 headers: {
//                     "Content-Type": "application/json",
//                     "X-WP-Nonce": csrfNonce // CSRF Protection
//                 },
//                 body: JSON.stringify(requestBody),
//             });

//             // Handle the response
//             if (!response.ok) {
//                 throw new Error(`HTTP error! Status: ${response.status}`);
//             }

//             const session = await response.json();

//             // If session data is valid, redirect to Stripe checkout
//             if (session && session.success && session.data && session.data.id) {
//                 console.log("Redirecting to Stripe Checkout...");
//                 await stripe.redirectToCheckout({ sessionId: session.data.id });
//             } else {
//                 alert("Payment processing failed. Please try again.");
//             }
//         } catch (error) {
//             console.error("Payment processing error:", error);
//             alert("An error occurred while processing your payment. Please try again.");
//         } finally {
//             // Re-enable button after request
//             checkoutButton.disabled = false;
//             checkoutButton.textContent = "Pay for Course";
//         }
//     }
    
// });

// document.addEventListener("DOMContentLoaded", function () {
//     // Debugging: Log csf_vars to check data
//     console.log("csf_vars:", csf_vars);

//     if (!csf_vars || !csf_vars.stripePublicKey) {
//         console.error("Stripe Public Key is missing or undefined!");
//         alert("Payment system error. Please try again later.");
//         return;
//     }

//     const stripe = Stripe(csf_vars.stripePublicKey);

//     function handlePaymentProcessing(event) {
//         event.preventDefault(); // Prevent form submission
//         console.log("Processing payment...");

//         const checkoutButton = event.target;
//         const form = checkoutButton.closest("#payment-form");
//         if (!form) {
//             console.error("Payment form not found!");
//             return;
//         }

//         const csrfNonce = csf_vars.nonce;

//         // Collect form values
//         const requestBody = {
//             name: form.querySelector("#name").value.trim(),
//             email: form.querySelector("#email").value.trim(),
//             phone: form.querySelector("#phone").value.trim(),
//             course: form.querySelector("#course").value,
//             courseDates: form.querySelector("#courseDates").value.trim(),
//             proficiency: form.querySelector("#proficiency").value.trim(),
//             streetAddress: form.querySelector("#streetAddress").value.trim(),
//             postalCode: form.querySelector("#postalCode").value.trim(),
//             city: form.querySelector("#city").value.trim(),
//             country: form.querySelector("#country").value,
//             emergencyName: form.querySelector("#emergencyName").value.trim(),
//             relationship: form.querySelector("#relationship").value.trim(),
//             emergencyPhone: form.querySelector("#emergencyPhone").value.trim(),
//             eng: form.querySelector("#eng").value,
//             privacy: form.querySelector("input[name='privacy']").checked,
//             terms: form.querySelector("input[name='terms']").checked,
//         };

//         // Validate required fields
//         if (!requestBody.name || !requestBody.email || !requestBody.phone || !requestBody.course || !requestBody.country || !requestBody.privacy || !requestBody.terms) {
//             alert("Please fill in all required fields.");
//             return;
//         }

//         // Disable button to prevent multiple clicks
//         checkoutButton.disabled = true;
//         checkoutButton.textContent = "Processing...";

//         fetch(csf_vars.ajaxurl + "?action=csf_create_checkout_session", {
//             method: "POST",
//             headers: {
//                 "Content-Type": "application/json",
//                 "X-WP-Nonce": csrfNonce // CSRF Protection
//             },
//             body: JSON.stringify(requestBody),
//         })
//         .then(response => {
//             if (!response.ok) {
//                 throw new Error(`HTTP error! Status: ${response.status}`);
//             }
//             return response.json();
//         })
//         .then(session => {
//             if (session && session.success && session.data && session.data.id) {
//                 console.log("Redirecting to Stripe Checkout...");
//                 return stripe.redirectToCheckout({ sessionId: session.data.id });
//             } else {
//                 throw new Error("Stripe session ID is missing or invalid");
//             }
//         })
//         .catch(error => {
//             console.error("Payment processing error:", error);
//             alert("An error occurred while processing your payment. Please try again.");
//         })
//         .finally(() => {
//             checkoutButton.disabled = false;
//             checkoutButton.textContent = "Pay for Course";
//         });
//     }

//     // ✅ Use event delegation to handle button clicks dynamically
//     document.body.addEventListener("click", function (event) {
//         if (event.target.id === "checkout-button") {
//             handlePaymentProcessing(event);
//         }
//     });
// });

jQuery(document).ready(function($) {
    // Debugging: Log csf_vars to check data
    console.log("csf_vars:", csf_vars);

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
        .catch(error => {
            console.error("Payment processing error:", error);
            alert("An error occurred while processing your payment. Please try again.");
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
