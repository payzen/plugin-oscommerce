<?php
/**
 * PayZen V2-Payment Module version 1.3.0 for osCommerce 2.3.x. Support contact : support@payzen.eu.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @category  Payment
 * @package   Payzen
 * @author    Lyra Network (http://www.lyra-network.com/)
 * @copyright 2014-2018 Lyra Network and contributors
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html  GNU General Public License (GPL v2)
 */

// administration interface - amount restriction error message
define('MODULE_PAYMENT_PAYZEN_NULL_MIN_AMOUNT_ERROR', "The field « Minimum amount » is mandatory.");
define('MODULE_PAYMENT_PAYZEN_NULL_MAX_AMOUNT_ERROR', "The field « Maximum amount » is mandatory.");
define('MODULE_PAYMENT_PAYZEN_INCONSISTENT_AMOUNT_ERROR', "« Minimum amount » must be inferior to « Maximum amount ».");
define('MODULE_PAYMENT_PAYZEN_CHOOZEO_OPTIONS_ERROR', "Min and max amounts must fall within the interval defined by fields « Minimum amount » and « Maximum amount ».");

// administration interface - choozeo payment options
define('MODULE_PAYMENT_PAYZEN_OPTIONS_TITLE', "Payment options");
define('MODULE_PAYMENT_PAYZEN_OPTIONS_DESC', "Configure Choozeo payment options");
define('MODULE_PAYMENT_PAYZEN_OPTIONS_LABEL', "Label");
define('MODULE_PAYMENT_PAYZEN_OPTIONS_MIN_AMOUNT', "Min amount");
define('MODULE_PAYMENT_PAYZEN_OPTIONS_MAX_AMOUNT', "Max amount");

// choozeo payment catalog messages
define('MODULE_PAYMENT_PAYZEN_CHOOZEO_TITLE', "PayZen - Payment with Choozeo");
