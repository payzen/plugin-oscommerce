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
 * Main class implementing multiple payment module for osCommerce.
 */
require_once(DIR_FS_CATALOG . 'includes/modules/payment/payzen.php');

global $payzen_plugin_features;

if ($payzen_plugin_features['multi']) {
    global $language;

    // Load module language file.
    require_once(DIR_FS_CATALOG . "includes/languages/$language/modules/payment/payzen_multi.php");

    class payzen_multi extends payzen
    {
        var $prefix = 'MODULE_PAYMENT_PAYZEN_MULTI_';

        /**
         * Class constructor.
         */
        function payzen_multi()
        {
            global $payzen_plugin_features;

            parent::payzen();

            // Initialize code.
            $this->code = 'payzen_multi';

            // Initialize title.
            $this->title = MODULE_PAYMENT_PAYZEN_MULTI_TITLE;

            if ($payzen_plugin_features['restrictmulti']) {
                $this->description = '<p style="background-color: #FFFFE0; border: 1px solid #E6DB55; font-size: 13px;  margin: 0 0 20px; padding: 10px;">' .
                    MODULE_PAYMENT_PAYZEN_MULTI_WARNING . '</p>' . $this->description;
            }
        }

        /**
         * Payment zone and amount restriction checks.
         */
        function update_status()
        {
            parent::update_status();

            if (! $this->enabled) {
                return;
            }

            // Check multi payment options.
            $options = $this->get_available_options();
            if (empty($options)) {
                $this->enabled = false;
            }
        }

        function get_available_options()
        {
            global $order;

            $amount = $order->info['total'];

            $options = MODULE_PAYMENT_PAYZEN_MULTI_OPTIONS ?
                json_decode(MODULE_PAYMENT_PAYZEN_MULTI_OPTIONS, true) : array();

            $availOptions = array();
            if (is_array($options) && ! empty($options)) {
                foreach ($options as $code => $option) {
                    if (empty($option)) {
                        continue;
                    }

                    if ((! $option['min_amount'] || $amount >= $option['min_amount'])
                        && (! $option['max_amount'] || $amount <= $option['max_amount'])) {
                        // Option will be available.
                        $availOptions[$code] = $option;
                    }
                }
            }

            return $availOptions;
        }

        /**
         * Parameters for what the payment option will look like in the list.
         * @return array
         */
        function selection()
        {
            $selection = array(
                'id' => $this->code,
                'module' => $this->title
            );

            $first = true;
            foreach ($this->get_available_options() as $code => $option) {
                $checked = '';
                if ($first) {
                    $checked = ' checked="checked"';
                    $first = false;
                }

                $selection['fields'][] = array(
                    'title' => '',
                    'field' => '<input type="radio" id="payzen_option_' . $code . '" name="payzen_option" value="' . $code . '" onclick="$(\'input[name=payment][value=payzen_multi]\').click();" style="vertical-align: middle; margin-top: 0;"' . $checked . '>' .
                               '<label for="payzen_option_' . $code . '">' . $option['label'] . '</label>'
                );
            }

            return $selection;
        }

        /**
         * Prepare the form that will be sent to the payment gateway.
         * @return string
         */
        function process_button()
        {
            $data = $this->_build_request();

            // Set multi payment options.
            $options = $this->get_available_options();
            $option = $options[tep_output_string($_POST['payzen_option'])];

            $first = (isset($option['first']) && $option['first']) ?
                (int) (string) (($option['first'] / 100) * $data['amount']) /* Amount is in cents. */ : null;

            // Override cb contract.
            $data['contracts'] = $option['contract'] ? 'CB=' . $option['contract'] : null;

            require_once(DIR_FS_CATALOG . 'includes/classes/payzen_request.php');
            $request = new PayzenRequest(CHARSET);

            $request->setFromArray($data);

            // To recover order session.
            $request->addExtInfo('session_id', session_id());

            // To recover order payment method.
            $request->addExtInfo('payment_method', $this->code);

            $request->setMultiPayment(null /* Use already set amount. */, $first, $option['count'], $option['period']);

            return $request->getRequestHtmlFields();
        }

        /**
         * Module install (register admin-managed parameters in database).
         */
        function install()
        {
            parent::install();

            // Multi-payment parameters.
            $this->_install_query('OPTIONS', '', 35, 'payzen_cfg_draw_table_multi_options(', 'payzen_get_multi_options');
        }

        /**
         * Returns the names of module's parameters.
         * @return array[int]string
         */
        function keys()
        {
            global $payzen_plugin_features;

            $keys = array();

            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_STATUS';
            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_SORT_ORDER';
            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_ZONE';

            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_SITE_ID';

            if (! $payzen_plugin_features['qualif']) {
                $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_KEY_TEST';
            }

            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_KEY_PROD';
            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_CTX_MODE';
            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_SIGN_ALGO';
            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_PLATFORM_URL';

            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_LANGUAGE';
            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_AVAILABLE_LANGUAGES';
            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_CAPTURE_DELAY';
            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_VALIDATION_MODE';
            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_PAYMENT_CARDS';
            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_3DS_MIN_AMOUNT';

            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_MIN_AMOUNT';
            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_MAX_AMOUNT';

            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_OPTIONS';

            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_REDIRECT_ENABLED';
            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_REDIRECT_SUCCESS_TIMEOUT';
            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_REDIRECT_SUCCESS_MESSAGE';
            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_REDIRECT_ERROR_TIMEOUT';
            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_REDIRECT_ERROR_MESSAGE';
            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_RETURN_MODE';
            $keys[] = 'MODULE_PAYMENT_PAYZEN_MULTI_ORDER_STATUS';

            return $keys;
        }
    }
}
