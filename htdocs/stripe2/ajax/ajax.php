<?php
/* Copyright (C) 2021		Thibault FOUCART	<support@ptibogxiv.net>
 * Copyright (C) 2024		MDW							<mdeweerd@users.noreply.github.com>
 * Copyright (C) 2024       Frédéric France         <frederic.france@free.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/stripe2/ajax/ajax.php
 *	\brief      Ajax action for Stipe ie: Terminal. Used when doing payment with Stripe2 Terminal in TakePOS.
 *
 *  Calling with
 *  action=getConnexionToken return a token of Stripe2 terminal
 *  action=createPaymentIntent generates a payment intent
 *  action=capturePaymentIntent generates a payment
 */

if (!defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', '1');
}
if (!defined('NOREQUIREMENU')) {
	define('NOREQUIREMENU', '1');
}
if (!defined('NOREQUIREHTML')) {
	define('NOREQUIREHTML', '1');
}
if (!defined('NOREQUIREAJAX')) {
	define('NOREQUIREAJAX', '1');
}
if (!defined('NOBROWSERNOTIF')) {
	define('NOBROWSERNOTIF', '1');
}

// Load Dolibarr environment
require '../../main.inc.php'; // Load $user and permissions
require_once DOL_DOCUMENT_ROOT . '/includes/stripe2/stripe2-php/init.php';
require_once DOL_DOCUMENT_ROOT . '/stripe2/class/stripe2.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

$action = GETPOST('action', 'aZ09');
$location = GETPOST('location', 'alphanohtml');
$stripe2acc = GETPOST('stripe2acc', 'alphanohtml');
$servicestatus = GETPOSTINT('servicestatus');
$amount = GETPOSTINT('amount');

if (!$user->hasRight('takepos', 'run')) {
	accessforbidden('Not allowed to use TakePOS');
}

$usestripe2terminals = getDolGlobalString('STRIPE2_LOCATION');
if (! $usestripe2terminals) {
	accessforbidden('Feature to use Stripe2 terminals not enabled');
}


/*
 * View
 */

top_httphead('application/json');

if ($action == 'getConnexionToken') {
	try {
		// Be sure to authenticate the endpoint for creating connection tokens.
		// Force to use the correct API key
		global $stripe2arrayofkeysbyenv;
		\Stripe\Stripe2::setApiKey($stripe2arrayofkeysbyenv[$servicestatus]['secret_key']);
		// The ConnectionToken's secret let's you connect to any stripe2 Terminal reader
		// and take payments with your stripe2 account.
		$array = array();
		if (isset($location) && !empty($location)) {
			$array['location'] = $location;
		}
		if (empty($stripe2acc)) {				// If the stripe2 connect account not set, we use common API usage
			$connectionToken = \Stripe\Terminal\ConnectionToken::create($array);
		} else {
			$connectionToken = \Stripe\Terminal\ConnectionToken::create($array, array("stripe_account" => $stripe2acc));
		}
		echo json_encode(array('secret' => $connectionToken->secret));
	} catch (Error $e) {
		http_response_code(500);
		echo json_encode(['error' => $e->getMessage()]);
	}
} elseif ($action == 'createPaymentIntent') {
	try {
		$json_str = file_get_contents('php://input');
		$json_obj = json_decode($json_str);

		// For Terminal payments, the 'payment_method_types' parameter must include
		// 'card_present' and the 'capture_method' must be set to 'manual'
		$object = new Facture($db);
		$object->fetch($json_obj->invoiceid);
		$object->fetch_thirdparty();

		$fulltag = 'INV=' . $object->id . '.CUS=' . $object->thirdparty->id;
		$tag = null;
		$fulltag = dol_string_unaccent($fulltag);

		$stripe2 = new Stripe2($db);
		$customer = $stripe2->customerStripe2($object->thirdparty, $stripe2acc, $servicestatus, 1);

		$intent = $stripe2->getPaymentIntent($json_obj->amount, $object->multicurrency_code, '', 'Stripe2 payment: ' . $fulltag . (is_object($object) ? ' ref=' . $object->ref : ''), $object, $customer, $stripe2acc, $servicestatus, 1, 'terminal', false, null, 0, 1);

		echo json_encode(array('client_secret' => $intent->client_secret));
	} catch (Error $e) {
		http_response_code(500);
		echo json_encode(['error' => $e->getMessage()]);
	}
} elseif ($action == 'capturePaymentIntent') {
	try {
		// retrieve JSON from POST body
		$json_str = file_get_contents('php://input');
		$json_obj = json_decode($json_str);
		if (empty($stripe2acc)) {				// If the stripe2 connect account not set, we use common API usage
			$intent = \Stripe\PaymentIntent::retrieve($json_obj->id);
		} else {
			$intent = \Stripe\PaymentIntent::retrieve($json_obj->id, array("stripe_account" => $stripe2acc));
		}
		$intent = $intent->capture();

		echo json_encode($intent);
	} catch (Error $e) {
		http_response_code(500);
		echo json_encode(['error' => $e->getMessage()]);
	}
}
