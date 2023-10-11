# GTM SST WordPress Plugin Documentation

## Overview

**GTM SST** is a WordPress plugin designed to send `page_view` events to a specified server-side Google Tag Manager (sGTM) container. The plugin offers a user interface in the WordPress admin area where users can configure the plugin's settings, such as the GTM server address, user consent cookie name, GTM server preview mode, and user_id cookie.

## Installation

1. Upload the `gtm-sst` folder to the `/wp-content/plugins/` directory of your WordPress installation.
2. Activate the plugin through the 'Plugins' menu in WordPress.

## Configuration

After activating the plugin, navigate to the **GTM SST** settings page in the WordPress admin area. Here, you can configure the following settings:

- **GTM Server Address**: The address of your sGTM container. This is where the plugin will send the `page_view` events.
- **User Consent Cookie Name**: The name of the cookie that stores the user's consent value. This value will be sent with the event to ensure data privacy compliance.
- **X-Gtm-Server-Preview**: If you want to send events in preview mode to GTM (useful for debugging), provide the preview value from GTM here.
- **User ID Cookie Name**: If your website sets a specific cookie for user identification, provide the name of that cookie here. The plugin will read the value from this cookie and send it as the "user_id" with the event.

## How It Works

The plugin operates by:

1. Listening for when the DOM content is fully loaded on the frontend.
2. Once loaded, it sends an AJAX request to the backend of WordPress, triggering the `send_gtm_custom_event` function.
3. The function retrieves various information like the page title, URL, referrer, user consent, user ID, client ID, user agent, and IP.
4. This data is then sent as a `page_view` event to the specified sGTM container along with the following data:
    - client_id
    - page_title (dt)
    - page_location (dl)
    - page_referrer (dr)
    - user's IP
    - User-Agent
    - user's consent

## Privacy Considerations

The plugin has been designed with user privacy in mind. It will only send data that is necessary for analytics purposes and ensures compliance with data privacy regulations by sending the user consent value with each event. Ensure that you have the appropriate user consent before sending any personal data to third-party tools.

## Troubleshooting

If events are not being sent to your sGTM container, ensure that:

- The GTM Server Address is correctly set in the plugin settings.
- The user consent cookie name is correctly set, and the cookie is present on the user's browser.
- The sGTM container is correctly set up to receive and process the `page_view` events.

For further assistance, please contact: [analytics@mariuszbrucki.pl](mailto:analytics@mariuszbrucki.pl)

## License

This plugin is licensed under the terms of the [GNU Affero General Public License v3.0](https://www.gnu.org/licenses/agpl-3.0.html).
