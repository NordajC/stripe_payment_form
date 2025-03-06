document.addEventListener("DOMContentLoaded", function () {
  const stripe = Stripe("pk_test_51PdTpjLDYpN9ZccjjKkS3NgQiTOOFIHQj9QRfYHOnMNzbrJvJz38Y2uSSBiRHqhBPHpjV7gEIw6CyuOxwMxLGhJq00XluvPZl5"); // Replace with your Stripe public key
  const checkoutButton = document.getElementById("checkout-button");

  checkoutButton.addEventListener("click", function (event) {
      event.preventDefault();

      const selectedCourse = document.getElementById("course").value;
      const name = document.getElementById("name").value;
      const email = document.getElementById("email").value;

      fetch("/wp-admin/admin-ajax.php?action=csf_create_checkout_session", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ name, email, course: selectedCourse }),
      })
      .then(response => response.json())
      .then(session => {
          return stripe.redirectToCheckout({ sessionId: session.id });
      })
      .catch(error => console.error("Error:", error));
  });
});
