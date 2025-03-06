<?php
/**
 * Plugin Name: Custom Stripe Course Payment Form
 * Plugin URI:  https://yourwebsite.com
 * Description: A simple plugin to handle Stripe payments for courses/lessons.
 * Version: 1.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue Stripe and custom scripts
function csf_enqueue_scripts() {
    wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', [], null, true);
    wp_enqueue_script('csf-script', plugin_dir_url(__FILE__) . 'script.js', ['jquery'], null, true);
}
add_action('wp_enqueue_scripts', 'csf_enqueue_scripts');

// Shortcode to display the form
function csf_payment_form() {
    ob_start();
    ?>
    <form id="payment-form">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="course">Choose Course</label>
        <select id="course" name="course">
            <option value="Spanish Course">Spanish Course</option>
            <option value="French Course">French Course</option>
            <option value="Portuguese Course">Portuguese Course</option>
            <option value="Arabic Course">Arabic Course</option>
            <option value="Mandarin Course">Mandarin Course</option>
        </select>

        <button id="checkout-button">Pay for Course</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_stripe_course_form', 'csf_payment_form');

// Handle AJAX request for Stripe Checkout Session
function csf_create_checkout_session() {
    require_once __DIR__ . '/vendor/autoload.php'; // Stripe PHP SDK

    \Stripe\Stripe::setApiKey("sk_test_51PdTpjLDYpN9ZccjQdlmbuzuEIQLO9Iu03yUuTAG3h6DnKEDJXg5VYlrAoOdVNkpsU1oCIot6UqI1LD3MyFhrhXO007O8fF68X"); // Replace with your Stripe secret key

    $data = json_decode(file_get_contents("php://input"), true);

    $course_names = [
        "Spanish Course" => "Spanish Course",
        "French Course" => "French Course",
        "Portuguese Course" => "Portuguese Course",
        "Arabic Course" => "Arabic Course",
        "Mandarin Course" => "Mandarin Course"
    ];

    $selected_course = $data['course'];
    $course_name = isset($course_names[$selected_course]) ? $course_names[$selected_course] : "Custom Course";

    $course_price = 32000; // Price in cents ($150)

    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'gbp',
                'product_data' => ['name' => $course_name],
                'unit_amount' => $course_price,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => site_url('/course-payment-success/'),
        'cancel_url' => site_url('/course-payment-cancel/'),
    ]);

    echo json_encode(['id' => $session->id]);
    wp_die();
}

add_action('wp_ajax_csf_create_checkout_session', 'csf_create_checkout_session');
add_action('wp_ajax_nopriv_csf_create_checkout_session', 'csf_create_checkout_session');
