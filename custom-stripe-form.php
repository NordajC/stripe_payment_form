<?php
/**
 * Plugin Name: Custom Stripe Course Payment Form
 * Plugin URI:  https://yourwebsite.com
 * Description: A simple plugin to handle Stripe payments for courses/lessons. [custom_stripe_course_form]. Requires WP mail SMTP plugin and setup for email there.
 * Version: 1.2
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
$stripe_secret_key = getenv('STRIPE_SECRET_KEY') ?: $_ENV['STRIPE_SECRET_KEY'] ?? '';
$stripe_publishable_key = getenv('STRIPE_PUBLISHABLE_KEY') ?: $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '';

// Prevent Stripe from running if the key is missing
if (empty($stripe_secret_key)) {
    error_log("Stripe Secret Key is missing! Cannot process payments.");
    return;
}

error_log("Plugin is active and running.");

// Set Stripe API key for backend (Secret Key)
\Stripe\Stripe::setApiKey($stripe_secret_key);

function csf_enqueue_scripts() {
    // ✅ Ensure Stripe is loaded first
    wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', [], null, true);
    
    // ✅ Ensure your script loads AFTER Stripe.js
    wp_enqueue_script('csf-script', plugin_dir_url(__FILE__) . 'src/script.js', ['jquery', 'stripe-js'], null, true);
    
    // ✅ Ensure styles load
    wp_enqueue_style('csf-style', plugin_dir_url(__FILE__) . 'src/style.css');

    // ✅ Get Stripe Publishable Key
    $stripe_publishable_key = $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '';

    // ✅ Debugging: Log if key is missing
    error_log("Stripe Publishable Key: " . ($stripe_publishable_key ?: 'MISSING'));

    // ✅ Pass data to JavaScript
    wp_localize_script('csf-script', 'csf_vars', [
        'stripePublicKey' => $stripe_publishable_key,
        'nonce' => wp_create_nonce('csf_checkout_nonce'),
        'ajaxurl' => admin_url('admin-ajax.php')
    ]);
}
add_action('wp_enqueue_scripts', 'csf_enqueue_scripts', 999); // ✅ Ensure it loads LAST

add_action('wp_ajax_nopriv_test_ajax', function() {
    wp_send_json_success(['message' => 'AJAX is working']);
});
add_action('wp_ajax_test_ajax', function() {
    wp_send_json_success(['message' => 'AJAX is working']);
});

// Add this right after your wp_localize_script call
error_log("Localizing script with: " . json_encode([
    'stripePublicKey' => $stripe_publishable_key ? 'YES' : 'NO',
    'nonce' => $nonce ? 'YES' : 'NO'
]));

// Shortcode to display the form
function csf_payment_form() {
    ob_start();
    ?>
    <form id="payment-form">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" required>

        <label for="course">Choose Course</label>
        <select id="course" name="course" required>
            <option value="Spanish Course">Spanish Course | 10 weeks</option>
            <option value="French Course">French Course | 10 weeks</option>
            <option value="Portuguese Course">Portuguese Course | 10 weeks</option>
            <option value="Arabic Course">Arabic Course | 10 weeks</option>
            <option value="Mandarin Course">Mandarin Course | 10 weeks</option>
            <option value="Russian Course">Russian Course | 10 weeks</option>
        </select>

        <label for="courseDates">Choose Course Dates</label>
        <select id="courseDates" name="courseDates" required>
            <option value="22nd April to 27th June">22nd April to 27th June (10 weeks)</option>
            <option value="" disabled>TBD</option>
        </select>

        <label for="proficiency">What is your language proficiency level?</label>
        <select id="proficiency" name="proficiency" required>
            <option value="Beginner">Beginner</option>
            <option value="Lower Intermediate">Lower Intermediate</option>
            <option value="Intermediate">Intermediate</option>
            <option value="Upper Intermediate">Upper Intermediate</option>
            <option value="Advanced">Advanced</option>
        </select>

        <h3>Home Address</h3>
        <label for="streetAddress">Street Address</label>
        <input type="text" id="streetAddress" name="streetAddress" autocomplete="streetAddress" required>

        <label for="postalCode">ZIP or Postal Code (Optional)</label>
        <input type="text" id="postalCode" name="postalCode" autocomplete="postalCode">

        <label for="city">City</label>
        <input type="text" id="city" name="city" autocomplete="address-level2" required>

        <label for="country">Country</label>
        <select id="country" name="country" autocomplete="country" enterkeyhint="done" required>
                <option></option>
                <option value="AF">Afghanistan</option>
                <option value="AX">Åland Islands</option>
                <option value="AL">Albania</option>
                <option value="DZ">Algeria</option>
                <option value="AS">American Samoa</option>
                <option value="AD">Andorra</option>
                <option value="AO">Angola</option>
                <option value="AI">Anguilla</option>
                <option value="AQ">Antarctica</option>
                <option value="AG">Antigua &amp; Barbuda</option>
                <option value="AR">Argentina</option>
                <option value="AM">Armenia</option>
                <option value="AW">Aruba</option>
                <option value="AC">Ascension Island</option>
                <option value="AU">Australia</option>
                <option value="AT">Austria</option>
                <option value="AZ">Azerbaijan</option>
                <option value="BS">Bahamas</option>
                <option value="BH">Bahrain</option>
                <option value="BD">Bangladesh</option>
                <option value="BB">Barbados</option>
                <option value="BY">Belarus</option>
                <option value="BE">Belgium</option>
                <option value="BZ">Belize</option>
                <option value="BJ">Benin</option>
                <option value="BM">Bermuda</option>
                <option value="BT">Bhutan</option>
                <option value="BO">Bolivia</option>
                <option value="BA">Bosnia &amp; Herzegovina</option>
                <option value="BW">Botswana</option>
                <option value="BV">Bouvet Island</option>
                <option value="BR">Brazil</option>
                <option value="IO">British Indian Ocean Territory</option>
                <option value="VG">British Virgin Islands</option>
                <option value="BN">Brunei</option>
                <option value="BG">Bulgaria</option>
                <option value="BF">Burkina Faso</option>
                <option value="BI">Burundi</option>
                <option value="KH">Cambodia</option>
                <option value="CM">Cameroon</option>
                <option value="CA">Canada</option>
                <option value="CV">Cape Verde</option>
                <option value="BQ">Caribbean Netherlands</option>
                <option value="KY">Cayman Islands</option>
                <option value="CF">Central African Republic</option>
                <option value="TD">Chad</option>
                <option value="CL">Chile</option>
                <option value="CN">China</option>
                <option value="CX">Christmas Island</option>
                <option value="CC">Cocos (Keeling) Islands</option>
                <option value="CO">Colombia</option>
                <option value="KM">Comoros</option>
                <option value="CG">Congo - Brazzaville</option>
                <option value="CD">Congo - Kinshasa</option>
                <option value="CK">Cook Islands</option>
                <option value="CR">Costa Rica</option>
                <option value="CI">Côte d’Ivoire</option>
                <option value="HR">Croatia</option>
                <option value="CW">Curaçao</option>
                <option value="CY">Cyprus</option>
                <option value="CZ">Czechia</option>
                <option value="DK">Denmark</option>
                <option value="DJ">Djibouti</option>
                <option value="DM">Dominica</option>
                <option value="DO">Dominican Republic</option>
                <option value="EC">Ecuador</option>
                <option value="EG">Egypt</option>
                <option value="SV">El Salvador</option>
                <option value="GQ">Equatorial Guinea</option>
                <option value="ER">Eritrea</option>
                <option value="EE">Estonia</option>
                <option value="SZ">Eswatini</option>
                <option value="ET">Ethiopia</option>
                <option value="FK">Falkland Islands (Islas Malvinas)</option>
                <option value="FO">Faroe Islands</option>
                <option value="FJ">Fiji</option>
                <option value="FI">Finland</option>
                <option value="FR">France</option>
                <option value="GF">French Guiana</option>
                <option value="PF">French Polynesia</option>
                <option value="TF">French Southern Territories</option>
                <option value="GA">Gabon</option>
                <option value="GM">Gambia</option>
                <option value="GE">Georgia</option>
                <option value="DE">Germany</option>
                <option value="GH">Ghana</option>
                <option value="GI">Gibraltar</option>
                <option value="GR">Greece</option>
                <option value="GL">Greenland</option>
                <option value="GD">Grenada</option>
                <option value="GP">Guadeloupe</option>
                <option value="GU">Guam</option>
                <option value="GT">Guatemala</option>
                <option value="GG">Guernsey</option>
                <option value="GN">Guinea</option>
                <option value="GW">Guinea-Bissau</option>
                <option value="GY">Guyana</option>
                <option value="HT">Haiti</option>
                <option value="HM">Heard &amp; McDonald Islands</option>
                <option value="HN">Honduras</option>
                <option value="HK">Hong Kong</option>
                <option value="HU">Hungary</option>
                <option value="IS">Iceland</option>
                <option value="IN">India</option>
                <option value="ID">Indonesia</option>
                <option value="IR">Iran</option>
                <option value="IQ">Iraq</option>
                <option value="IE">Ireland</option>
                <option value="IM">Isle of Man</option>
                <option value="IL">Israel</option>
                <option value="IT">Italy</option>
                <option value="JM">Jamaica</option>
                <option value="JP">Japan</option>
                <option value="JE">Jersey</option>
                <option value="JO">Jordan</option>
                <option value="KZ">Kazakhstan</option>
                <option value="KE">Kenya</option>
                <option value="KI">Kiribati</option>
                <option value="XK">Kosovo</option>
                <option value="KW">Kuwait</option>
                <option value="KG">Kyrgyzstan</option>
                <option value="LA">Laos</option>
                <option value="LV">Latvia</option>
                <option value="LB">Lebanon</option>
                <option value="LS">Lesotho</option>
                <option value="LR">Liberia</option>
                <option value="LY">Libya</option>
                <option value="LI">Liechtenstein</option>
                <option value="LT">Lithuania</option>
                <option value="LU">Luxembourg</option>
                <option value="MO">Macao</option>
                <option value="MG">Madagascar</option>
                <option value="MW">Malawi</option>
                <option value="MY">Malaysia</option>
                <option value="MV">Maldives</option>
                <option value="ML">Mali</option>
                <option value="MT">Malta</option>
                <option value="MH">Marshall Islands</option>
                <option value="MQ">Martinique</option>
                <option value="MR">Mauritania</option>
                <option value="MU">Mauritius</option>
                <option value="YT">Mayotte</option>
                <option value="MX">Mexico</option>
                <option value="FM">Micronesia</option>
                <option value="MD">Moldova</option>
                <option value="MC">Monaco</option>
                <option value="MN">Mongolia</option>
                <option value="ME">Montenegro</option>
                <option value="MS">Montserrat</option>
                <option value="MA">Morocco</option>
                <option value="MZ">Mozambique</option>
                <option value="MM">Myanmar (Burma)</option>
                <option value="NA">Namibia</option>
                <option value="NR">Nauru</option>
                <option value="NP">Nepal</option>
                <option value="NL">Netherlands</option>
                <option value="NC">New Caledonia</option>
                <option value="NZ">New Zealand</option>
                <option value="NI">Nicaragua</option>
                <option value="NE">Niger</option>
                <option value="NG">Nigeria</option>
                <option value="NU">Niue</option>
                <option value="NF">Norfolk Island</option>
                <option value="KP">North Korea</option>
                <option value="MK">North Macedonia</option>
                <option value="MP">Northern Mariana Islands</option>
                <option value="NO">Norway</option>
                <option value="OM">Oman</option>
                <option value="PK">Pakistan</option>
                <option value="PW">Palau</option>
                <option value="PS">Palestine</option>
                <option value="PA">Panama</option>
                <option value="PG">Papua New Guinea</option>
                <option value="PY">Paraguay</option>
                <option value="PE">Peru</option>
                <option value="PH">Philippines</option>
                <option value="PN">Pitcairn Islands</option>
                <option value="PL">Poland</option>
                <option value="PT">Portugal</option>
                <option value="PR">Puerto Rico</option>
                <option value="QA">Qatar</option>
                <option value="RE">Réunion</option>
                <option value="RO">Romania</option>
                <option value="RU">Russia</option>
                <option value="RW">Rwanda</option>
                <option value="WS">Samoa</option>
                <option value="SM">San Marino</option>
                <option value="ST">São Tomé &amp; Príncipe</option>
                <option value="SA">Saudi Arabia</option>
                <option value="SN">Senegal</option>
                <option value="RS">Serbia</option>
                <option value="SC">Seychelles</option>
                <option value="SL">Sierra Leone</option>
                <option value="SG">Singapore</option>
                <option value="SX">Sint Maarten</option>
                <option value="SK">Slovakia</option>
                <option value="SI">Slovenia</option>
                <option value="SB">Solomon Islands</option>
                <option value="SO">Somalia</option>
                <option value="ZA">South Africa</option>
                <option value="GS">South Georgia &amp; South Sandwich Islands</option>
                <option value="KR">South Korea</option>
                <option value="SS">South Sudan</option>
                <option value="ES">Spain</option>
                <option value="LK">Sri Lanka</option>
                <option value="BL">St Barthélemy</option>
                <option value="SH">St Helena</option>
                <option value="KN">St Kitts &amp; Nevis</option>
                <option value="LC">St Lucia</option>
                <option value="MF">St Martin</option>
                <option value="PM">St Pierre &amp; Miquelon</option>
                <option value="VC">St Vincent &amp; Grenadines</option>
                <option value="SR">Suriname</option>
                <option value="SJ">Svalbard &amp; Jan Mayen</option>
                <option value="SE">Sweden</option>
                <option value="CH">Switzerland</option>
                <option value="TW">Taiwan</option>
                <option value="TJ">Tajikistan</option>
                <option value="TZ">Tanzania</option>
                <option value="TH">Thailand</option>
                <option value="TL">Timor-Leste</option>
                <option value="TG">Togo</option>
                <option value="TK">Tokelau</option>
                <option value="TO">Tonga</option>
                <option value="TT">Trinidad &amp; Tobago</option>
                <option value="TA">Tristan da Cunha</option>
                <option value="TN">Tunisia</option>
                <option value="TR">Turkey</option>
                <option value="TM">Turkmenistan</option>
                <option value="TC">Turks &amp; Caicos Islands</option>
                <option value="TV">Tuvalu</option>
                <option value="UG">Uganda</option>
                <option value="UA">Ukraine</option>
                <option value="AE">United Arab Emirates</option>
                <option value="GB">United Kingdom</option>
                <option value="US">United States</option>
                <option value="UY">Uruguay</option>
                <option value="UM">US Outlying Islands</option>
                <option value="VI">US Virgin Islands</option>
                <option value="UZ">Uzbekistan</option>
                <option value="VU">Vanuatu</option>
                <option value="VA">Vatican City</option>
                <option value="VE">Venezuela</option>
                <option value="VN">Vietnam</option>
                <option value="WF">Wallis &amp; Futuna</option>
                <option value="EH">Western Sahara</option>
                <option value="YE">Yemen</option>
                <option value="ZM">Zambia</option>
                <option value="ZW">Zimbabwe</option>
            </select> 

        

        <h3>Emergency Contact</h3>
        <label for="emergencyName">Emergency Name</label>
        <input type="text" id="emergencyName" name="emergencyMame" required>

        <label for="relationship">Relationship</label>
        <input type="text" id="relationship" name="relationship" required>

        <label for="emergencyPhone">Emergency Tel</label>
        <input type="tel" id="emergencyPhone" name="emergencyPhone" required>

        <label for="eng">Can she/he speak English?</label>
        <select id="eng" name="eng" required>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select>

        <h3>Privacy and Terms</h3>
        <!-- <label for="privacy">
            I understand that Golders Green College will securely store my personal information and I allow them to send me important information.
        </label><input type="checkbox" name="privacy" id="privacy" required>

        <label for="terms">
            <input type="checkbox" name="terms" id="terms" required>I acknowledge that I have read and understood the College Terms and Conditions as provided, and I agree to all of the terms.
        </label> -->

        <div class="checkbox-container">
            <input class="checkbox" type="checkbox" name="privacy" id="privacy" required>
            <label for="privacy">
                I understand that Golders Green College will securely store my personal information and I allow them to send me important information.
            </label>
        </div>

        <div class="checkbox-container">
            <input class="checkbox" type="checkbox" name="terms" id="terms" required>
            <label for="terms">
                I acknowledge that I have read and understood the College Terms and Conditions as provided, and I agree to all of the terms.
            </label>
        </div>

        <button id="checkout-button" type="button">Pay for Course</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_stripe_course_form', 'csf_payment_form');

function csf_create_checkout_session() {
    require_once __DIR__ . '/vendor/autoload.php';

    global $stripe_secret_key;

    $input = file_get_contents('php://input');
    error_log('Received data: ' . $input);

    if (empty($stripe_secret_key)) {
        error_log("Stripe Secret Key is missing! Cannot process payment.");
        wp_send_json_error(["message" => "Stripe payment system error."], 500);
        wp_die();
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['course']) || empty($data['email']) || empty($data['name']) || empty($data['phone'])) {
        error_log("Error: Missing required fields");
        wp_send_json_error(["message" => "Missing required parameters"], 400);
        wp_die();
    }

    $course_names = [
        "Spanish Course" => "Spanish Course",
        "French Course" => "French Course",
        "Portuguese Course" => "Portuguese Course",
        "Arabic Course" => "Arabic Course",
        "Mandarin Course" => "Mandarin Course",
        "Russian Course" => "Russian Course"
    ];

    if (!isset($course_names[$data['course']])) {
        
        wp_send_json_error(["message" => "Invalid course selected"], 400);
        wp_die();
    }

    $course_name = $course_names[$data['course']];
    $course_price = 32000;

    try {
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
            'customer_email' => sanitize_email($data['email']),
            'metadata' => [
                'name' => sanitize_text_field($data['name']),
                'phone' => sanitize_text_field($data['phone']),
                'course_name' => $course_name
            ],
            'success_url' => site_url('/course-payment-success/'),
            'cancel_url' => site_url('/course-payment-cancel/'),
        ]);


        if (!isset($session->id) || empty($session->id)) {
            throw new Exception("Stripe session ID is missing in response.");
        }

        $user_data = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'course' => $course_name,
            'courseDates' => $data['courseDates'],
            'proficiency' => $data['proficiency'],
            'streetAddress' => $data['streetAddress'],
            'postalCode' => $data['postalCode'],
            'city' => $data['city'],
            'country' => $data['country'],
            'emergencyName' => $data['emergencyName'],
            'relationship' => $data['relationship'],
            'emergencyPhone' => $data['emergencyPhone'],
            'eng' => $data['eng'],
            'privacy' => isset($data['privacy']) ? 'Yes' : 'No',
            'terms' => isset($data['terms']) ? 'Yes' : 'No'
        ];

        // send Confirmation Email to User
        send_user_confirmation_email($user_data);

        // send Notification Email to Admin
        send_admin_notification($user_data);

        wp_send_json_success(["id" => $session->id]);

    } catch (Exception $e) {
        error_log("Stripe Error: " . $e->getMessage());
        wp_send_json_error(["message" => "Stripe session creation failed", "error" => $e->getMessage()], 500);
    }

    wp_die();
}


add_action('wp_ajax_csf_create_checkout_session', 'csf_create_checkout_session');
add_action('wp_ajax_nopriv_csf_create_checkout_session', 'csf_create_checkout_session');

function send_user_confirmation_email($user_data) {
    $subject = "Booking Confirmation for " . $user_data['course'];

    // Prepare the email message
    $message = "Dear " . $user_data['name'] . ",\n\n";
    $message .= "Thank you for booking the " . $user_data['course'] . " course with us.\n\n";
    $message .= "Here are your details:\n\n";
    $message .= "Full Name: " . $user_data['name'] . "\n";
    $message .= "Email: " . $user_data['email'] . "\n";
    $message .= "Phone: " . $user_data['phone'] . "\n";
    $message .= "Course: " . $user_data['course'] . "\n";
    $message .= "Course Dates: " . $user_data['courseDates'] . "\n";
    $message .= "Expected Level: " . $user_data['proficiency'] . "\n\n";


    // Home Address
    $message .= "Home Address:\n";
    $message .= "Street Address: " . $user_data['streetAddress'] . "\n";
    $message .= "Postal Code: " . $user_data['postalCode'] . "\n";
    $message .= "City: " . $user_data['city'] . "\n";
    $message .= "Country: " . $user_data['country'] . "\n\n";

    // Emergency Contact
    $message .= "Emergency Contact:\n";
    $message .= "Emergency Name: " . $user_data['emergencyName'] . "\n";
    $message .= "Relationship: " . $user_data['relationship'] . "\n";
    $message .= "Emergency Tel: " . $user_data['emergencyPhone'] . "\n";
    $message .= "Can they speak English?: " . $user_data['eng'] . "\n\n";

    // Privacy & Terms Agreement
    $message .= "Privacy & Terms Agreement:\n";
    $message .= "Privacy Statement Accepted: " . ($user_data['privacy'] ? "Yes" : "No") . "\n";
    $message .= "Terms & Conditions Accepted: " . ($user_data['terms'] ? "Yes" : "No") . "\n\n";

    // Confirmation message
    $message .= "Your booking is confirmed. We will get in touch with you shortly. If you have any questions, please contact info@ggcolleges.com or call +44 20 7870 8728.\n\n";
    $message .= "Best regards,\nGG Colleges";

    // Set the "From" header for the email
    $headers = "From: GG Colleges <no-reply@ggcolleges.com>\r\n";

    if (wp_mail($user_data['email'], $subject, $message, $headers)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Email successfully sent to: " . $user_data['email']);
        }
    } else {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Failed to send email to: " . $user_data['email']);
        }
    }
    
}


function send_admin_notification($user_data) {
    $admin_email = "it@globalvisasupport.com"; // Get WordPress admin email (ensure it's set in WordPress settings)
    $subject = "New Course Booking: " . $user_data['course'];

    $message = "A new course booking has been made. Here are the details:\n\n";
    $message .= "Full Name: " . $user_data['name'] . "\n";
    $message .= "Email: " . $user_data['email'] . "\n";
    $message .= "Phone: " . $user_data['phone'] . "\n";
    $message .= "Course: " . $user_data['course'] . "\n";
    $message .= "Course Dates: " . $user_data['courseDates'] . "\n";
    $message .= "Expected Level: " . $user_data['proficiency'] . "\n\n";


    // Home Address
    $message .= "Home Address:\n";
    $message .= "Street Address: " . $user_data['streetAddress'] . "\n";
    $message .= "Postal Code: " . $user_data['postalCode'] . "\n";
    $message .= "City: " . $user_data['city'] . "\n";
    $message .= "Country: " . $user_data['country'] . "\n\n";
    

    // Emergency Contact
    $message .= "Emergency Contact:\n";
    $message .= "Emergency Name: " . $user_data['emergencyName'] . "\n";
    $message .= "Relationship: " . $user_data['relationship'] . "\n";
    $message .= "Emergency Tel: " . $user_data['emergencyPhone'] . "\n";
    $message .= "Can they speak English?: " . $user_data['eng'] . "\n\n";

    // Privacy & Terms Agreement
    $message .= "Privacy & Terms Agreement:\n";
    $message .= "Privacy Statement Accepted: " . ($user_data['privacy'] ? "Yes" : "No") . "\n";
    $message .= "Terms & Conditions Accepted: " . ($user_data['terms'] ? "Yes" : "No") . "\n\n";

    // Send notification to the admin
    $headers = "From: GG Colleges <no-reply@ggcolleges.com>\r\n";

    if (wp_mail($admin_email, $subject, $message, $headers)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Email successfully sent to: " . $user_data['email']);
        }
    } else {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Failed to send email to: " . $user_data['email']);
        }
    }
    
}
