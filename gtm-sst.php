<?php
/*
Plugin Name: GTM SST
Description: Sends page_view events to a specified sGTM container and allows for debugging.
Version: 1.0
Author: Mariusz Brucki
*/

// Function to add the settings page to the admin menu
function gtm_custom_event_menu() {
    add_options_page('GTM SST Settings', 'GTM SST', 'manage_options', 'gtm-sst', 'gtm_custom_event_options_page');
}
add_action('admin_menu', 'gtm_custom_event_menu');

// Display the settings page
function gtm_custom_event_options_page() {
    ?>
    <div class="wrap">
        <h2>GTM SST</h2>
        <form action="options.php" method="post">
            <?php settings_fields('gtm_custom_event_options'); ?>
            <?php do_settings_sections('gtm-sst'); ?>

            <input name="submit" class="button button-primary" type="submit" value="Save Changes">
        </form>
    </div>
    <?php
}

// Register the settings
function gtm_custom_event_admin_init() {
    register_setting('gtm_custom_event_options', 'gtm_custom_event_options', 'gtm_custom_event_options_validate');

    add_settings_section('gtm_custom_event_main', 'Quick Guide', 'gtm_custom_event_section_text', 'gtm-sst');

    add_settings_field('gtm_custom_event_server_address', 'GTM Server Address', 'gtm_custom_event_setting_server_address', 'gtm-sst', 'gtm_custom_event_main');
    
    add_settings_field('gtm_custom_event_consent_cookie_name', 'User Consent Cookie Name', 'gtm_custom_event_setting_consent_cookie_name', 'gtm-sst', 'gtm_custom_event_main');
    
    add_settings_field('gtm_custom_event_server_preview', 'X-Gtm-Server-Preview', 'gtm_custom_event_setting_string', 'gtm-sst', 'gtm_custom_event_main');
    
    add_settings_field('gtm_custom_event_cookie_name', 'User ID Cookie Name', 'gtm_custom_event_setting_cookie_name', 'gtm-sst', 'gtm_custom_event_main');
}
add_action('admin_init', 'gtm_custom_event_admin_init');

function gtm_custom_event_setting_server_address() {
    $options = get_option('gtm_custom_event_options', array());
    $value = isset($options['gtm_custom_event_server_address']) ? $options['gtm_custom_event_server_address'] : '';
    echo "<input id='gtm_custom_event_server_address' name='gtm_custom_event_options[gtm_custom_event_server_address]' size='60' type='text' value='{$value}' required />";
}

function gtm_custom_event_setting_consent_cookie_name() {
    $options = get_option('gtm_custom_event_options', array());
    $value = isset($options['gtm_custom_event_consent_cookie_name']) ? $options['gtm_custom_event_consent_cookie_name'] : '';
    echo "<input id='gtm_custom_event_consent_cookie_name' name='gtm_custom_event_options[gtm_custom_event_consent_cookie_name]' size='60' type='text' value='{$value}' required />";
}

function gtm_custom_event_setting_string() {
    $options = get_option('gtm_custom_event_options', array());
    $value = isset($options['gtm_custom_event_server_preview']) ? $options['gtm_custom_event_server_preview'] : '';
    echo "<input id='gtm_custom_event_server_preview' name='gtm_custom_event_options[gtm_custom_event_server_preview]' size='60' type='text' value='{$value}' placeholder='Leave empty if you do not want to debug' />";
}

function gtm_custom_event_setting_cookie_name() {
    $options = get_option('gtm_custom_event_options', array());
    $value = isset($options['gtm_custom_event_cookie_name']) ? $options['gtm_custom_event_cookie_name'] : '';
    echo "<input id='gtm_custom_event_cookie_name' name='gtm_custom_event_options[gtm_custom_event_cookie_name]' size='60' type='text' value='{$value}' placeholder='Leave empty to omit user_id' />";
}


// Validate user input
function gtm_custom_event_options_validate($input) {
    if (empty($input['gtm_custom_event_server_address'])) {
        add_settings_error('gtm_custom_event_server_address', 'gtm_custom_event_server_address_error', 'GTM Server Address cannot be empty.', 'error');
    }
    if (empty($input['gtm_custom_event_consent_cookie_name'])) {
        add_settings_error('gtm_custom_event_consent_cookie_name', 'gtm_custom_event_consent_cookie_name_error', 'User Consent Cookie Name cannot be empty.', 'error');
    }
    return $input;
}

function gtm_custom_event_section_text() {
    echo '<p>This plugin sends all the page_view events from the backend of Wordpress. Hit me if you have any questions: analytics@mariuszbrucki.pl</p>';
    echo '<p><strong>GTM Server Address:</strong> This is the address of your GTM server container. In order to find it go to sGTM -> click on ID -> Default Url. Make sure to provide the complete URL, including the  exact path e.g. https://gtm-pw9ffd-y2riu.uc.r.appspot.com/endpoint.</p>';
    echo '<p><strong>User Consent Cookie Name:</strong> Provide the name of the cookie that stores the user consent value. This value will be sent with the event to ensure data privacy compliance. It is being sent in the event data as user_consent. Please use this field in you server gtm container to filter out users that don not want to be tracked. This field is mandatory.</p>';
    echo '<p><strong>X-Gtm-Server-Preview:</strong> This is an optional field. If you want to send events in preview mode to GTM (useful for debugging), fill in this field with the appropriate preview value from GTM. If you leave it empty, events will be sent in regular mode. To find it go to sGTM -> click Preview -> 3 dots in the right top corner -> Send request manually -> copy "X-Gtm-Server-Preview HTTP header" </p>';
    echo '<p><strong>User ID Cookie Name:</strong> If your website sets a specific cookie for user identification, provide the name of that cookie here. The plugin will read the value from this cookie and send it as the "user_id" with the event. If left empty, the "user_id" parameter will not be included in the sent event. Make sure you have a right user consent before sending this information to 3rd party tools!</p>';
    echo '<p><strong>*CID (_ga) Cookie:</strong> The plugin will automatically look for the "_ga" cookie to retrieve the Client ID (CID). If the cookie is not found, a random CID will be generated. </p>';
    echo '<p><strong>*User-Agent</strong> The plugin will automatically pass the information about the device of a user. Make sure you have a right user consent before sending this information to 3rd party tools! </p>';
    echo '<p><strong>*IP</strong> The plugin will automatically pass the IP of a device. Make sure you have a right user consent before sending this information to 3rd party tools! </p>';
}

// Function to send the event from the backend
function send_gtm_custom_event($title = '', $url = '', $referrer = '') {
    $options = get_option('gtm_custom_event_options');

    if (empty($options['gtm_custom_event_server_address'])) {
        error_log('GTM Server Address not set. Event not sent.');
        return;
    }

    $cid = $_COOKIE["_ga"] ?? null;
    if (!$cid) {
        // If _ga cookie is not present, generate a random cid
        $cid = rand(1000000000, 9999999999) . '.' . rand(1000000000, 9999999999);
    } else {
        // Strip the "GA1.1." prefix if present
        $cid = str_replace("GA1.1.", "", $cid);
    }
    
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }
    
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $event_params = array(
        'name' => 'page_view',
        'params' => array(
            'client_id' => $cid,
            'x-ga-js_client_id' => $cid,
            'page_title' => $title,  // Use the passed title
            'page_location' => $url,  // Use the passed URL
            'page_referrer' => $referrer,  // Use the passed referrer
            'ip_override' => $ip_address,
            'user_agent' => $user_agent
        )
    );

    // Check for User ID Cookie and retrieve its value if set
    if (!empty($options['gtm_custom_event_cookie_name']) && isset($_COOKIE[$options['gtm_custom_event_cookie_name']])) {
        $event_params['params']['user_id'] = $_COOKIE[$options['gtm_custom_event_cookie_name']];
    }
    // Check for User Consent Cookie and retrieve its value if set
    if (isset($_COOKIE[$options['gtm_custom_event_consent_cookie_name']])) {
        $event_params['params']['user_consent'] = $_COOKIE[$options['gtm_custom_event_consent_cookie_name']];
    }

    $body = array('events' => array($event_params));

    // Determine if X-Gtm-Server-Preview header should be added
    $headers = array(
        'Content-Type' => 'application/json; charset=utf-8'
    );
    if (!empty($options['gtm_custom_event_server_preview'])) {
        $headers['X-Gtm-Server-Preview'] = $options['gtm_custom_event_server_preview'];
    }

    $response = wp_remote_post($options['gtm_custom_event_server_address'], array(
        'method' => 'POST',
        'headers' => $headers,
        'body' => json_encode($body),
        'timeout' => 15,
        'sslverify' => false
    ));

    if (is_wp_error($response)) {
        error_log('Error sending event to GTM: ' . $response->get_error_message());
    }
}

function no_cache_headers() {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}
add_action('init', 'no_cache_headers');

// Function to enqueue JavaScript that sends an AJAX request on DOMContentLoaded
function enqueue_dom_ready_script() {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "' . admin_url('admin-ajax.php') . '", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("action=send_gtm_event_on_dom_ready&title=" + encodeURIComponent(document.title) + "&url=" + encodeURIComponent(window.location.href) + "&referrer=" + encodeURIComponent(document.referrer));
        });
    </script>';
}

add_action('wp_footer', 'enqueue_dom_ready_script');

// Function to handle the AJAX request and trigger the GTM custom event
function send_gtm_event_on_dom_ready() {
    $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
    $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
    $referrer = isset($_POST['referrer']) ? esc_url_raw($_POST['referrer']) : '';

    send_gtm_custom_event($title, $url, $referrer);
    wp_die();  // Necessary for handling AJAX in WordPress
}

add_action('wp_ajax_send_gtm_event_on_dom_ready', 'send_gtm_event_on_dom_ready');
add_action('wp_ajax_nopriv_send_gtm_event_on_dom_ready', 'send_gtm_event_on_dom_ready');
?>
