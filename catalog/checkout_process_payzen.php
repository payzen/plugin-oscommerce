<?php
/**
 * Copyright © Lyra Network.
 * This file is part of PayZen plugin for osCommerce. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra-network.com/)
 * @copyright Lyra Network
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL v2)
 */

/**
 * This file is an access point to the payment gateway plugin to validate an order.
 */
if (key_exists('vads_hash', $_POST) && isset($_POST['vads_hash']) && key_exists('vads_order_id', $_POST) && isset($_POST['vads_order_id'])
    && key_exists('vads_ext_info_session_id', $_POST) && isset($_POST['vads_ext_info_session_id'])) {
    // Restore session if this is an IPN call.

    $osCsid = $_POST['vads_ext_info_session_id'];
    $_POST['osCsid'] = $osCsid;
    $_GET['osCsid'] = $osCsid;

    // For cookie based sessions...
    $_COOKIE['osCsid'] = $osCsid;
    $_COOKIE['cookie_test'] = 'please_accept_for_session';

    require_once('checkout_process.php');
} else {
    require_once('includes/application_top.php');

    global $payzen_response, $language, $messageStack;

    $paymentMethod = $_REQUEST['vads_ext_info_payment_method'];

    switch ($paymentMethod) {
        case 'payzen':
            require_once(DIR_FS_CATALOG . 'includes/modules/payment/payzen.php');
            $paymentObject = new payzen();
            break;

        case 'payzen_multi':
            require_once(DIR_FS_CATALOG . 'includes/modules/payment/payzen_multi.php');
            $paymentObject = new payzen_multi();
            break;

        default:
            require_once(DIR_FS_CATALOG . "includes/languages/$language/modules/payment/payzen.php");
            $messageStack->add_session('header', MODULE_PAYMENT_PAYZEN_TECHNICAL_ERROR, 'error');

            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true));
            break;
    }

    require_once(DIR_FS_CATALOG . 'includes/classes/payzen_response.php');
    $payzen_response = new PayzenResponse(
        array_map('stripslashes', $_REQUEST),
        constant($paymentObject->prefix . 'CTX_MODE'),
        @constant($paymentObject->prefix . 'KEY_TEST'),
        constant($paymentObject->prefix . 'KEY_PROD'),
        constant($paymentObject->prefix . 'SIGN_ALGO')
    );

    $from_server = ($payzen_response->get('hash') != null);

    // Check authenticity.
    if (! $payzen_response->isAuthentified()) {
        $messageStack->add_session('header', MODULE_PAYMENT_PAYZEN_TECHNICAL_ERROR, 'error');

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true));
    }

    if ($paymentObject->_is_order_paid()) {
        global $payzen_plugin_features;

        // Messages to display on payment result page.
        if ($payzen_plugin_features['prodfaq'] && (constant($paymentObject->prefix . 'CTX_MODE') == 'TEST')) {
            $messageStack->add_session('header', MODULE_PAYMENT_PAYZEN_GOING_INTO_PROD_INFO, 'success');
        }

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL', true));
    } else {
        $return_mode = '_' . ($from_server ? 'POST' : constant($paymentObject->prefix . 'RETURN_MODE'));

        if ($return_mode == '_POST') {
            $action = tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', true);
            $fields = '';

            foreach ($$return_mode as $key => $value) {
                $fields .= '<input type="hidden" name="' . $key . '" value="' . htmlentities($value, ENT_QUOTES, 'UTF-8') . '" />' . "\n";
            }

            echo <<<EOT
                <html>
                    <body>
                        <form action="$action" method="POST" name="checkout_process_payzen_form">
                            $fields
                        </form>

                        <script type="text/javascript">
                            window.onload = function() {
                                document.checkout_process_payzen_form.submit();
                            };
                        </script>
                    </body>
                </html>
EOT;
        } else {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PROCESS, http_build_query($$return_mode), 'SSL', true));
        }
    }
}
