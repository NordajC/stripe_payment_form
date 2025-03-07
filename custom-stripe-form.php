<?php
/**
 * Plugin Name: Custom Stripe Course Payment Form
 * Plugin URI:  https://yourwebsite.com
 * Description: A simple plugin to handle Stripe payments for courses/lessons. [custom_stripe_course_form]. Requires WP mail SMTP plugin and setup for email there.
 * Version: 1.0
 * Author: Jordan Chong
 * Author URI: https://yourwebsite.com
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load .env file if it exists
if (file_exists(__DIR__ . '/.env')) {
    require_once __DIR__ . '/vendor/autoload.php'; // Composer's autoloader
    Dotenv\Dotenv::createImmutable(__DIR__)->load(); // Load the .env file
}

// Get the keys from the .env file
$stripe_secret_key = $_ENV['STRIPE_SECRET_KEY'] ?? '';
$stripe_publishable_key = $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '';

// Set Stripe API key for backend (Secret Key)
\Stripe\Stripe::setApiKey($stripe_secret_key);

// Enqueue Stripe and custom scripts
function csf_enqueue_scripts() {
    wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', [], null, true);
    wp_enqueue_script('csf-script', plugin_dir_url(__FILE__) . 'src/script.js', ['jquery'], null, true);
    wp_enqueue_style('csf-style', plugin_dir_url(__FILE__) . 'src/style.css');

    // Get Stripe Publishable Key
    $stripe_publishable_key = $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '';

    // Debugging: Log the key to wp-content/debug.log
    error_log("Stripe Publishable Key: " . ($stripe_publishable_key ? 'Loaded' : 'MISSING'));

    // Pass Stripe Publishable Key to JavaScript
    wp_localize_script('csf-script', 'csf_vars', [
        'stripePublicKey' => $stripe_publishable_key
    ]);
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

// Send a confirmation email to the user
function send_user_confirmation_email($user_email, $course_name) {
    $subject = "Booking Confirmation for " . $course_name;
    $message = "Dear Customer,\n\n";
    $message .= "Thank you for booking the " . $course_name . " course with us.\n\n";
    $message .= "Your booking is confirmed. We will get in touch with you shortly.\n\n";
    $message .= "Kind regards,\nYour Course Team";

    wp_mail($user_email, $subject, $message);
}

// Send a notification email to the admin
function send_admin_notification($user_email, $course_name, $user_name) {
    $admin_email = 'youremail@example.com';  // Replace with your email address
    $subject = "New Course Booking: " . $course_name;
    $message = "A new booking has been made for the " . $course_name . " course.\n\n";
    $message .= "Customer Name: " . $user_name . "\n";
    $message .= "Customer Email: " . $user_email . "\n";
    $message .= "Course: " . $course_name . "\n";
    $message .= "Please process the booking accordingly.";

    wp_mail($admin_email, $subject, $message);
}

// Handle Stripe Checkout Session
function csf_create_checkout_session() {
    require_once __DIR__ . '/vendor/autoload.php';

    $data = json_decode(file_get_contents("php://input"), true);
    if (empty($data['course']) || empty($data['email']) || empty($data['name'])) {
        error_log("Error: Missing required fields");
        wp_send_json_error(["message" => "Missing required parameters"], 400);
        wp_die();
    }

    $course_names = [
        "Spanish Course" => "Spanish Course",
        "French Course" => "French Course",
        "Portuguese Course" => "Portuguese Course",
        "Arabic Course" => "Arabic Course",
        "Mandarin Course" => "Mandarin Course"
    ];

    $selected_course = $data['course'];
    $course_name = $course_names[$selected_course] ?? "Custom Course";
    $course_price = 32000;

    try {
        // Create the Stripe session
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
            'customer_email' => $data['email'],
            'metadata' => [
                'name' => $data['name'],
                'course_name' => $course_name
            ],
            'success_url' => site_url('/course-payment-success/'),
            'cancel_url' => site_url('/course-payment-cancel/'),
        ]);
    
        // Log session ID for debugging
        error_log("Stripe session created successfully: " . $session->id);
    
        // Send confirmation email to the user
        send_user_confirmation_email($data['email'], $course_name);
    
        // Send notification email to the admin
        send_admin_notification($data['email'], $course_name, $data['name']);
    
        // Return session ID to the front end
        wp_send_json_success(["id" => $session->id]);
    } catch (Exception $e) {
        error_log("Stripe Error: " . $e->getMessage());
        wp_send_json_error(["message" => "Stripe session creation failed", "error" => $e->getMessage()], 500);
    }
    
    wp_die();
}
add_action('wp_ajax_csf_create_checkout_session', 'csf_create_checkout_session');
add_action('wp_ajax_nopriv_csf_create_checkout_session', 'csf_create_checkout_session');
