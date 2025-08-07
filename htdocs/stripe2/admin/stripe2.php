<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/* Copyright (C) 2017		Alexandre Spangaro		<aspangaro@open-dsi.fr>
 * Copyright (C) 2017		Olivier Geffroy			<jeff@jeffinfo.com>
 * Copyright (C) 2017		Saasprov				<saasprov@gmail.com>
 * Copyright (C) 2018-2022  Thibault FOUCART		<support@ptibogxiv.net>
 * Copyright (C) 2018-2024  Frédéric France         <frederic.france@free.fr>
 * Copyright (C) 2024		MDW							<mdeweerd@users.noreply.github.com>
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
 * \file       htdocs/stripe2/admin/stripe2.php
 * \ingroup    stripe
 * \brief      Page to setup stripe2 module
 */

// Load Dolibarr environment
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/stripe2/lib/stripe2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT . '/stripe2/class/stripe2.class.php';

$servicename = 'stripe2';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Societe $mysoc
 * @var Translate $langs
 * @var User $user
 */

// Load translation files required by the page
$langs->loadLangs(array('admin', 'other', 'paypal', 'paybox', 'stripe2'));

if (empty($user->admin)) {
	accessforbidden();
}
if (empty($conf->stripe->enabled)) {
	accessforbidden();
}

$action = GETPOST('action', 'aZ09');


/*
 * Actions
 */
$error = 0;

$stripe2Accounts = array(
	'ridin' => 'Ridin’Family',
	'festi' => 'Festibike',
);



if ($action == 'setvalue' && $user->admin) {


	$db->begin();


	if (empty($conf->stripe2connect->enabled)) {
		$result = dolibarr_set_const($db, "STRIPE2_TEST_PUBLISHABLE_KEY", GETPOST('STRIPE2_TEST_PUBLISHABLE_KEY', 'alpha'), 'chaine', 0, '', $conf->entity);
		if (!($result > 0)) {
			$error++;
		}
		$result = dolibarr_set_const($db, "STRIPE2_TEST_SECRET_KEY", GETPOST('STRIPE2_TEST_SECRET_KEY', 'alpha'), 'chaine', 0, '', $conf->entity);
		if (!($result > 0)) {
			$error++;
		}
		$result = dolibarr_set_const($db, "STRIPE2_TEST_WEBHOOK_ID", GETPOST('STRIPE2_TEST_WEBHOOK_ID', 'alpha'), 'chaine', 0, '', $conf->entity);
		if (!($result > 0)) {
			$error++;
		}
		$result = dolibarr_set_const($db, "STRIPE2_TEST_WEBHOOK_KEY", GETPOST('STRIPE2_TEST_WEBHOOK_KEY', 'alpha'), 'chaine', 0, '', $conf->entity);
		if (!($result > 0)) {
			$error++;
		}
		$result = dolibarr_set_const($db, "STRIPE2_LIVE_PUBLISHABLE_KEY", GETPOST('STRIPE2_LIVE_PUBLISHABLE_KEY', 'alpha'), 'chaine', 0, '', $conf->entity);
		if (!($result > 0)) {
			$error++;
		}
		$result = dolibarr_set_const($db, "STRIPE2_LIVE_SECRET_KEY", GETPOST('STRIPE2_LIVE_SECRET_KEY', 'alpha'), 'chaine', 0, '', $conf->entity);
		if (!($result > 0)) {
			$error++;
		}
		$result = dolibarr_set_const($db, "STRIPE2_LIVE_WEBHOOK_ID", GETPOST('STRIPE2_LIVE_WEBHOOK_ID', 'alpha'), 'chaine', 0, '', $conf->entity);
		if (!($result > 0)) {
			$error++;
		}
		$result = dolibarr_set_const($db, "STRIPE2_LIVE_WEBHOOK_KEY", GETPOST('STRIPE2_LIVE_WEBHOOK_KEY', 'alpha'), 'chaine', 0, '', $conf->entity);
		if (!($result > 0)) {
			$error++;
		}
	}


	//MODIF JOSE
	$result = dolibarr_set_const($db, "STRIPE2_AUTO_ADD_FEES_TO_BANK", GETPOST('STRIPE2_AUTO_ADD_FEES_TO_BANK', 'int'), 'yesno', 0, '', $conf->entity);
	if (!($result > 0)) $error++;

	$date_start = dol_mktime(0, 0, 0, GETPOST('date_startmonth', 'int'), GETPOST('date_startday', 'int'), GETPOST('date_startyear', 'int'));
	$date_end = dol_mktime(23, 59, 59, GETPOST('date_endmonth', 'int'), GETPOST('date_endday', 'int'), GETPOST('date_endyear', 'int'));

	$result = dolibarr_set_const($db, "STRIPE2_FEES_DATE_START", $date_start, 'chaine', 0, '', $conf->entity);
	if (!($result > 0)) $error++;

	$result = dolibarr_set_const($db, "STRIPE2_FEES_DATE_END", $date_end, 'chaine', 0, '', $conf->entity);
	if (!($result > 0)) $error++;

	//END MODIF 


	$result = dolibarr_set_const($db, "ONLINE_PAYMENT_CREDITOR", GETPOST('ONLINE_PAYMENT_CREDITOR', 'alpha'), 'chaine', 0, '', $conf->entity);
	if (!($result > 0)) {
		$error++;
	}
	$result = dolibarr_set_const($db, "STRIPE2_BANK_ACCOUNT_FOR_PAYMENTS", GETPOSTINT('STRIPE2_BANK_ACCOUNT_FOR_PAYMENTS'), 'chaine', 0, '', $conf->entity);
	if (!($result > 0)) {
		$error++;
	}
	$result = dolibarr_set_const($db, "STRIPE2_USER_ACCOUNT_FOR_ACTIONS", GETPOSTINT('STRIPE2_USER_ACCOUNT_FOR_ACTIONS'), 'chaine', 0, '', $conf->entity);
	if (!($result > 0)) {
		$error++;
	}
	$result = dolibarr_set_const($db, "STRIPE2_BANK_ACCOUNT_FOR_BANKTRANSFERS", GETPOSTINT('STRIPE2_BANK_ACCOUNT_FOR_BANKTRANSFERS'), 'chaine', 0, '', $conf->entity);
	if (!($result > 0)) {
		$error++;
	}
	if (GETPOSTISSET('STRIPE2_LOCATION')) {
		$result = dolibarr_set_const($db, "STRIPE2_LOCATION", GETPOST('STRIPE2_LOCATION', 'alpha'), 'chaine', 0, '', $conf->entity);
		if (!$result > 0) {
			$error++;
		}
	}
	$result = dolibarr_set_const($db, "ONLINE_PAYMENT_CSS_URL", GETPOST('ONLINE_PAYMENT_CSS_URL', 'alpha'), 'chaine', 0, '', $conf->entity);
	if (!($result > 0)) {
		$error++;
	}
	$result = dolibarr_set_const($db, "ONLINE_PAYMENT_MESSAGE_FORM", GETPOST('ONLINE_PAYMENT_MESSAGE_FORM', 'restricthtml'), 'chaine', 0, '', $conf->entity);
	if (!($result > 0)) {
		$error++;
	}
	$result = dolibarr_set_const($db, "ONLINE_PAYMENT_MESSAGE_OK", GETPOST('ONLINE_PAYMENT_MESSAGE_OK', 'restricthtml'), 'chaine', 0, '', $conf->entity);
	if (!($result > 0)) {
		$error++;
	}
	$result = dolibarr_set_const($db, "ONLINE_PAYMENT_MESSAGE_KO", GETPOST('ONLINE_PAYMENT_MESSAGE_KO', 'restricthtml'), 'chaine', 0, '', $conf->entity);
	if (!($result > 0)) {
		$error++;
	}
	$result = dolibarr_set_const($db, "ONLINE_PAYMENT_SENDEMAIL", GETPOST('ONLINE_PAYMENT_SENDEMAIL'), 'chaine', 0, '', $conf->entity);
	if (!($result > 0)) {
		$error++;
	}
	// Stock decrement
	//$result = dolibarr_set_const($db, "ONLINE_PAYMENT_WAREHOUSE", (GETPOST('ONLINE_PAYMENT_WAREHOUSE', 'alpha') > 0 ? GETPOST('ONLINE_PAYMENT_WAREHOUSE', 'alpha') : ''), 'chaine', 0, '', $conf->entity);
	//if (! $result > 0)
	//	$error ++;

	// Payment token for URL
	$result = dolibarr_set_const($db, "PAYMENT_SECURITY_TOKEN", GETPOST('PAYMENT_SECURITY_TOKEN', 'alpha'), 'chaine', 0, '', $conf->entity);
	if (!($result > 0)) {
		$error++;
	}
	if (empty($conf->use_javascript_ajax)) {
		$result = dolibarr_set_const($db, "PAYMENT_SECURITY_TOKEN_UNIQUE", GETPOST('PAYMENT_SECURITY_TOKEN_UNIQUE', 'alpha'), 'chaine', 0, '', $conf->entity);
		if (!($result > 0)) {
			$error++;
		}
	}

	if (!$error) {
		$db->commit();
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	} else {
		$db->rollback();
		dol_print_error($db);
	}
}


if ($action == 'manual_reconcile_stripe2_payments' && $user->admin) {

	require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
	require_once DOL_DOCUMENT_ROOT . '/compta/paiement/class/paiement.class.php';
	require_once DOL_DOCUMENT_ROOT . '/compta/bank/class/account.class.php';

	$langs->load("bills");
	$stripe2 = new Stripe2($db);
	$added = 0;
	$options = [];

	$service = (getDolGlobalString('STRIPE2_LIVE') && !GETPOST('forcesandbox')) ? 'StripeLive' : 'StripeTest';
	$stripe2acc = $stripe2->getStripe2Account($service);
	if ($stripe2acc) $options['stripe_account'] = $stripe2acc;

	try {
		$txn_all = \Stripe\Charge::all(['limit' => 100], $options);
		$date_start2 = dol_mktime(0, 0, 0, GETPOST('date_start2month', 'int'), GETPOST('date_start2day', 'int'), GETPOST('date_start2year', 'int'));
		$date_end2 = dol_mktime(23, 59, 59, GETPOST('date_end2month', 'int'), GETPOST('date_end2day', 'int'), GETPOST('date_end2year', 'int'));


		foreach ($txn_all->data as $txn) {


			if (
				(empty($date_start2) || $txn->created >= $date_start2) &&
				(empty($date_end2) || $txn->created <= $date_end2)
			) {

				$ext_payment_id = $txn->payment_intent;


				//Duplicate?
				$sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "paiement WHERE ext_payment_id = '" . $db->escape($ext_payment_id) . "'";
				$resql = $db->query($sql);
				if ($resql && $db->num_rows($resql) > 0) continue;

				$object_id = 0;



				if (!empty($txn->metadata->dol_id)) {

					$object_id = $txn->metadata->dol_id;



					if ($txn->metadata->dol_type == "invoice" || $txn->metadata->dol_type == "facture") {
						$object = new Facture($db);
						if ($object->fetch($object_id) <= 0) continue;
					} elseif ($txn->metadata->dol_type == "order" || $txn->metadata->dol_type == "commande") {
						$object = new Commande($db);
						if ($object->fetch($object_id) <= 0) continue;
					}
				}



				if (!$object_id && !empty($txn->metadata->order_id)) {
					require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
					$refClient = '#' . $txn->metadata->order_id;
					$object_id = trouverIdFactureParRefClient($refClient);
				}

				// $thirdparty_id = 0;
				// if (!empty($txn->metadata->customer_email)) {
				// 	$thirdparty_id = trouverIdTiersParEmail($txn->metadata->customer_email);
				// }


				if (!$object_id) {
					continue;
				}
				$paiement = new Paiement($db);
				$paiement->datepaye = dol_now();
				$paiement->amounts = [$object->id => $txn->amount / 100];
				$paiement->paiementid = getDolGlobalInt('STRIPE2_PAYMENT_MODE_FOR_PAYMENTS');
				if (empty($paiement->paiementid)) {
					$paiement->paiementid = dol_getIdFromCode($db, 'CB', 'c_paiement', 'code', 'id', 1);
				}
				$paiement->ext_payment_id = $ext_payment_id;
				$paiement->ext_payment_site = 'Stripe2';
				$paiement->note_public = 'Paiement Stripe2 réconcilié manuellement';
				$paiement->num_payment = $ext_payment_id;

				$db->begin();
				if ($paiement->create($user) > 0) {
					$bankaccountid = getDolGlobalInt('STRIPE2_BANK_ACCOUNT_FOR_PAYMENTS');
					$label = '(CustomerInvoicePayment)';
					if ($bankaccountid > 0) {
						$res = $paiement->addPaymentToBank($user, 'payment', $label, $bankaccountid, '', '');
						if ($res > 0) {
							$db->commit();
							$added++;

							setEventMessage("Paiement Stripe2 réconcilié pour la facture ID " . $object->id . " (Montant: " . price($txn->amount / 100, 0, '', 1, -1, -1, $txn->currency) . ")", 'mesgs');

							dol_syslog("STRIPE2 RECONCILIATION: Paiement créé pour facture ID " . $object->id . ", ext_payment_id=" . $ext_payment_id . ", montant=" . ($txn->amount / 100) . " " . strtoupper($txn->currency), LOG_INFO);
						} else {
							dol_syslog("STRIPE2 RECONCILIATION ERROR: Échec de addPaymentToBank pour facture ID " . $object->id, LOG_ERR);
							$db->rollback();
						}
					} else {
						dol_syslog("STRIPE2 RECONCILIATION ERROR: Aucun compte bancaire Stripe2 défini", LOG_WARNING);
						setEventMessage("Aucun compte bancaire Stripe2 défini", 'warnings');
						$db->rollback();
					}
				} else {
					dol_syslog("STRIPE2 RECONCILIATION ERROR: Échec de création du paiement pour la facture ID " . $object->id . ". Erreur: " . $paiement->error, LOG_ERR);
					$db->rollback();
				}
			}
		}
		setEventMessage($langs->trans("Stripe2ManualPaymentsAdded", $added), 'mesgs');
	} catch (Exception $e) {
		setEventMessage($e->getMessage(), 'errors');
	}
}


if ($action == 'manual_update_stripe2_fees' && $user->admin) {

	require_once DOL_DOCUMENT_ROOT . '/compta/bank/class/account.class.php';
	require_once DOL_DOCUMENT_ROOT . '/stripe2/class/stripe2.class.php';

	$langs->load("stripe2");

	$stripe2 = new Stripe2($db);
	$form = new Form($db);

	$fk_account_stripe2 = 5;

	$date_start = dol_mktime(0, 0, 0, GETPOST('date_startmonth', 'int'), GETPOST('date_startday', 'int'), GETPOST('date_startyear', 'int'));
	$date_end = dol_mktime(23, 59, 59, GETPOST('date_endmonth', 'int'), GETPOST('date_endday', 'int'), GETPOST('date_endyear', 'int'));

	$added = 0;
	$error = 0;
	$logDetails = [];


	try {
		$service = (getDolGlobalString('STRIPE2_LIVE') && !GETPOST('forcesandbox')) ? 'StripeLive' : 'StripeTest';
		$stripe2acc = $stripe2->getStripe2Account($service);

		$params = array('limit' => 100);
		$options = $stripe2acc ? array('stripe_account' => $stripe2acc) : array();

		$txn_all = \Stripe\BalanceTransaction::all($params, $options);

		foreach ($txn_all->data as $txn) {
			if ($txn->fee > 0) {
				if (($txn->created >= $date_start) && ($txn->created <= $date_end)) {
					$res = stripe2AddPaymentFeeOnBank(
						$txn->source,
						$txn->fee / 100,
						$fk_account_stripe2,
						$txn->id,
						$txn->created,
						$txn->created
					);
					if ($res > 0) {
						$added++;
						$logDetails[] = [
							'date' => dol_print_date($txn->created, 'dayhour'),
							'id' => $txn->id,
							'amount' => price($txn->fee / 100, 0, '', 1, -1, -1, strtoupper($txn->currency)),
						];
					}
				}
			}
		}

		setEventMessage($langs->trans("Stripe2ManualFeesAdded", $added), 'mesgs');

		// Log et popup
		if (!empty($logDetails)) {
			foreach ($logDetails as $log) {
				dol_syslog("Stripe2 fee added: " . $log['date'] . ' - ' . $log['id'] . ' : ' . $log['amount']);
			}
		}
	} catch (Exception $e) {
		setEventMessage($e->getMessage(), 'errors');
	}
}

if ($action == "setlive") {
	$liveenable = GETPOSTINT('value');
	$res = dolibarr_set_const($db, "STRIPE2_LIVE", $liveenable, 'yesno', 0, '', $conf->entity);
	if ($res > 0) {
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	} else {
		setEventMessages($langs->trans("Error"), null, 'errors');
	}
}
//TODO: import script for stripe account saving in alone or connect mode for stripe.class.php


/*
 *	View
 */

$form = new Form($db);
$formproduct = new FormProduct($db);

llxHeader('', $langs->trans("Stripe2Setup"));

$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1">' . $langs->trans("BackToModuleList") . '</a>';
print load_fiche_titre($langs->trans("ModuleSetup") . ' Stripe2', $linkback);

$head = stripe2admin_prepare_head();

print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="setvalue">';

print dol_get_fiche_head($head, 'stripe2account', '', -1);

$stripe2arrayofwebhookevents = array('account.updated', 'payout.created', 'payout.paid', 'charge.pending', 'charge.refunded', 'charge.succeeded', 'charge.failed', 'payment_intent.succeeded', 'payment_intent.payment_failed', 'payment_method.attached', 'payment_method.updated', 'payment_method.card_automatically_updated', 'payment_method.detached', 'source.chargeable', 'customer.deleted');

print '<span class="opacitymedium">' . $langs->trans("Stripe2Desc") . "</span><br>\n";

print '<br>';

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("AccountParameter") . '</td>';
print '<td>' . $langs->trans("Value") . '</td>';
print '<td></td>';
print "</tr>\n";

print '<tr class="oddeven">';
print '<td>';
print $langs->trans("Stripe2LiveEnabled") . '</td><td>';
if ($conf->use_javascript_ajax) {
	print ajax_constantonoff('STRIPE2_LIVE');
} else {
	$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
	print $form->selectarray("STRIPE2_LIVE", $arrval, $conf->global->STRIPE2_LIVE);
}
print '</td><td></td></tr>';

if (empty($conf->stripe2connect->enabled)) {
	print '<tr class="oddeven"><td>';
	print '<span class="fieldrequired">' . $langs->trans("STRIPE2_TEST_PUBLISHABLE_KEY") . '</span></td><td>';
	print '<input class="minwidth300" type="text" name="STRIPE2_TEST_PUBLISHABLE_KEY" value="' . getDolGlobalString('STRIPE2_TEST_PUBLISHABLE_KEY') . '" placeholder="' . $langs->trans("Example") . ': pk_test_xxxxxxxxxxxxxxxxxxxxxxxx">';
	print '</td><td></td></tr>';

	print '<tr class="oddeven"><td>';
	print '<span class="titlefield fieldrequired">' . $langs->trans("STRIPE2_TEST_SECRET_KEY") . '</span></td><td>';
	print '<input class="minwidth300" type="text" name="STRIPE2_TEST_SECRET_KEY" value="' . getDolGlobalString('STRIPE2_TEST_SECRET_KEY') . '" placeholder="' . $langs->trans("Example") . ': sk_test_xxxxxxxxxxxxxxxxxxxxxxxx">';
	print '</td><td></td></tr>';

	print '<tr class="oddeven"><td>';
	print '<span class="titlefield">' . $langs->trans("STRIPE2_TEST_WEBHOOK_KEY") . '</span></td><td>';
	if (getDolGlobalInt('MAIN_FEATURES_LEVEL') >= 2) {
		print '<input class="minwidth300" type="text" name="STRIPE2_TEST_WEBHOOK_ID" value="' . getDolGlobalString('STRIPE2_TEST_WEBHOOK_ID') . '" placeholder="' . $langs->trans("Example") . ': we_xxxxxxxxxxxxxxxxxxxxxxxx">';
		print '<br>';
	}
	print '<input class="minwidth300" type="text" name="STRIPE2_TEST_WEBHOOK_KEY" value="' . getDolGlobalString('STRIPE2_TEST_WEBHOOK_KEY') . '" placeholder="' . $langs->trans("Example") . ': whsec_xxxxxxxxxxxxxxxxxxxxxxxx">';
	$out = img_picto('', 'globe') . ' <span class="opacitymedium">' . $langs->trans("ToOfferALinkForTestWebhook") . '</span> ';
	$url = dol_buildpath('/public/stripe2/ipn.php', 3);
	$url .= '?test=1';
	//global $dolibarr_main_instance_unique_id;
	//$url .= '&securitykey='.dol_hash('stripe2ipn-'.$dolibarr_main_instance_unique_id.'-'.$conf->global->STRIPE2_TEST_PUBLISHABLE_KEY, 'md5');
	$out .= '<input type="text" id="onlinetestwebhookurl" class="minwidth500" value="' . $url . '" disabled>';
	$out .= ajax_autoselect("onlinetestwebhookurl");
	print '<br>' . $out;
	print '</td><td>';
	if (getDolGlobalInt('MAIN_FEATURES_LEVEL') >= 2) {
		if (getDolGlobalString('STRIPE2_TEST_WEBHOOK_KEY') && getDolGlobalString('STRIPE2_TEST_SECRET_KEY') && getDolGlobalString('STRIPE2_TEST_WEBHOOK_ID')) {
			if (utf8_check($conf->global->STRIPE2_TEST_SECRET_KEY)) {
				try {
					\Stripe\Stripe2::setApiKey($conf->global->STRIPE2_TEST_SECRET_KEY);
					$endpoint = \Stripe\WebhookEndpoint::retrieve($conf->global->STRIPE2_TEST_WEBHOOK_ID);
					$endpoint->enabled_events = $stripe2arrayofwebhookevents;
					if (GETPOST('webhook', 'alpha') == $conf->global->STRIPE22_TEST_WEBHOOK_ID) {
						if (!GETPOST('status', 'alpha')) {
							$endpoint->disabled = true;
						} else {
							$endpoint->disabled = false;
						}
					}
					$endpoint->url = $url;
					// @phan-suppress-next-line PhanDeprecatedFunction
					$endpoint->save();

					if ($endpoint->status == 'enabled') {
						print '<a class="reposition" href="' . $_SERVER['PHP_SELF'] . '?action=ipn&webhook=' . $endpoint->id . '&status=0">';
						print img_picto($langs->trans("Activated"), 'switch_on');
					} else {
						print '<a class="reposition" href="' . $_SERVER['PHP_SELF'] . '?action=ipn&webhook=' . $endpoint->id . '&status=1">';
						print img_picto($langs->trans("Disabled"), 'switch_off');
					}
				} catch (Exception $e) {
					print $e->getMessage();
				}
			} else {
				print 'Bad value for the secret key. Reenter and save it again to fix this.';
			}
		} else {
			print img_picto($langs->trans("Inactive"), 'statut5');
		}
	}
	print '</td></tr>';
}

// 	//MODIF JOSE
// if (empty($conf->stripeconnect->enabled)) {
// 	foreach ($stripeAccounts as $code => $label) {
// 		print '<tr class="liste_titre"><td colspan="3">' . $langs->trans("StripeAccountConfig") . ' (TEST) - ' . $label . '</td></tr>';

// 		print '<tr class="oddeven"><td>' . $langs->trans("STRIPE_TEST_PUBLISHABLE_KEY") . '</td>';
// 		print '<td class="right"><input class="minwidth300" type="text" name="STRIPE_TEST_PUBLISHABLE_KEY_' . strtoupper($code) . '" value="' . dol_escape_htmltag(getDolGlobalString('STRIPE_TEST_PUBLISHABLE_KEY_' . strtoupper($code))) . '"></td><td></td></tr>';

// 		print '<tr class="oddeven"><td>' . $langs->trans("STRIPE_TEST_SECRET_KEY") . '</td>';
// 		print '<td class="right"><input class="minwidth300" type="text" name="STRIPE_TEST_SECRET_KEY_' . strtoupper($code) . '" value="' . dol_escape_htmltag(getDolGlobalString('STRIPE_TEST_SECRET_KEY_' . strtoupper($code))) . '"></td><td></td></tr>';

// 		print '<tr class="oddeven"><td>' . $langs->trans("STRIPE_TEST_WEBHOOK_KEY") . '</td>';
// 		print '<td class="right">';
// 		print '<input class="minwidth300" type="text" name="STRIPE_TEST_WEBHOOK_ID_' . strtoupper($code) . '" value="' . dol_escape_htmltag(getDolGlobalString('STRIPE_TEST_WEBHOOK_ID_' . strtoupper($code))) . '" placeholder="we_..."><br>';
// 		print '<input class="minwidth300" type="text" name="STRIPE_TEST_WEBHOOK_KEY_' . strtoupper($code) . '" value="' . dol_escape_htmltag(getDolGlobalString('STRIPE_TEST_WEBHOOK_KEY_' . strtoupper($code))) . '" placeholder="whsec_...">';

// 		$url = dol_buildpath('/public/stripe/ipn.php', 3) . '?test=1?account=' . $code;;
// 		$out = img_picto('', 'globe') . ' <span class="opacitymedium">' . $langs->trans("ToOfferALinkForTestWebhook") . '</span> ';
// 		$out .= '<input type="text" id="onlinetestwebhookurl_' . $code . '" class="minwidth500" value="' . $url . '" disabled>';
// 		$out .= ajax_autoselect("onlinetestwebhookurl_" . $code);
// 		print '<br>' . $out;
// 		print '</td><td></td></tr>';
// 	}
// } else {
// 	print '<tr class="oddeven"><td>' . $langs->trans("StripeConnect") . '</td>';
// 	print '<td><b>' . $langs->trans("StripeConnect_Mode") . '</b><br>';
// 	print $langs->trans("STRIPE_APPLICATION_FEE_PLATFORM") . ' ';
// 	print price($conf->global->STRIPE_APPLICATION_FEE_PERCENT);
// 	print '% + ';
// 	print price($conf->global->STRIPE_APPLICATION_FEE);
// 	print ' ' . $langs->getCurrencySymbol($conf->currency) . ' ' . $langs->trans("minimum") . ' ' . price($conf->global->STRIPE_APPLICATION_FEE_MINIMAL) . ' ' . $langs->getCurrencySymbol($conf->currency);
// 	print '</td><td></td></tr>';
// }

// if (empty($conf->stripeconnect->enabled)) {
// 	foreach ($stripeAccounts as $code => $label) {
// 		print '<tr class="liste_titre"><td colspan="3">' . $langs->trans("StripeAccountConfig") . ' (LIVE) - ' . $label . '</td></tr>';

// 		print '<tr class="oddeven"><td>' . $langs->trans("STRIPE_LIVE_PUBLISHABLE_KEY") . '</td>';
// 		print '<td class="right"><input class="minwidth300" type="text" name="STRIPE_LIVE_PUBLISHABLE_KEY_' . strtoupper($code) . '" value="' . dol_escape_htmltag(getDolGlobalString('STRIPE_LIVE_PUBLISHABLE_KEY_' . strtoupper($code))) . '"></td><td></td></tr>';

// 		print '<tr class="oddeven"><td>' . $langs->trans("STRIPE_LIVE_SECRET_KEY") . '</td>';
// 		print '<td class="right"><input class="minwidth300" type="text" name="STRIPE_LIVE_SECRET_KEY_' . strtoupper($code) . '" value="' . dol_escape_htmltag(getDolGlobalString('STRIPE_LIVE_SECRET_KEY_' . strtoupper($code))) . '"></td><td></td></tr>';

// 		print '<tr class="oddeven"><td>' . $langs->trans("STRIPE_LIVE_WEBHOOK_KEY") . '</td>';
// 		print '<td class="right">';
// 		print '<input class="minwidth300" type="text" name="STRIPE_LIVE_WEBHOOK_ID_' . strtoupper($code) . '" value="' . dol_escape_htmltag(getDolGlobalString('STRIPE_LIVE_WEBHOOK_ID_' . strtoupper($code))) . '" placeholder="we_..."><br>';
// 		print '<input class="minwidth300" type="text" name="STRIPE_LIVE_WEBHOOK_KEY_' . strtoupper($code) . '" value="' . dol_escape_htmltag(getDolGlobalString('STRIPE_LIVE_WEBHOOK_KEY_' . strtoupper($code))) . '" placeholder="whsec_...">';

// 		$url = dol_buildpath('/public/stripe/ipn.php', 3) . '?account=' . $code;
// 		$out = img_picto('', 'globe') . ' <span class="opacitymedium">' . $langs->trans("ToOfferALinkForLiveWebhook") . '</span> ';
// 		$out .= '<input type="text" id="onlinelivewebhookurl_' . $code . '" class="minwidth500" value="' . $url . '" disabled>';
// 		$out .= ajax_autoselect("onlinelivewebhookurl_" . $code);
// 		print '<br>' . $out;
// 		print '</td><td></td></tr>';
// 	}
// }


if (empty($conf->stripe2connect->enabled)) {
	print '<tr class="oddeven"><td>';
	print '<span class="fieldrequired">' . $langs->trans("STRIPE2_LIVE_PUBLISHABLE_KEY") . '</span></td><td>';
	print '<input class="minwidth300" type="text" name="STRIPE2_LIVE_PUBLISHABLE_KEY" value="' . getDolGlobalString('STRIPE2_LIVE_PUBLISHABLE_KEY') . '" placeholder="' . $langs->trans("Example") . ': pk_live_xxxxxxxxxxxxxxxxxxxxxxxx">';
	print '</td><td></td></tr>';

	print '<tr class="oddeven"><td>';
	print '<span class="fieldrequired">' . $langs->trans("STRIPE2_LIVE_SECRET_KEY") . '</span></td><td>';
	print '<input class="minwidth300" type="text" name="STRIPE2_LIVE_SECRET_KEY" value="' . getDolGlobalString('STRIPE2_LIVE_SECRET_KEY') . '" placeholder="' . $langs->trans("Example") . ': sk_live_xxxxxxxxxxxxxxxxxxxxxxxx">';
	print '</td><td></td></tr>';

	print '<tr class="oddeven"><td>';
	print '<span class="titlefield">' . $langs->trans("STRIPE2_LIVE_WEBHOOK_KEY") . '</span></td><td>';
	if (getDolGlobalInt('MAIN_FEATURES_LEVEL') >= 2) {
		print '<input class="minwidth300" type="text" name="STRIPE2_LIVE_WEBHOOK_ID" value="' . getDolGlobalString('STRIPE2_LIVE_WEBHOOK_ID') . '" placeholder="' . $langs->trans("Example") . ': we_xxxxxxxxxxxxxxxxxxxxxxxx">';
		print '<br>';
	}
	print '<input class="minwidth300" type="text" name="STRIPE2_LIVE_WEBHOOK_KEY" value="' . getDolGlobalString('STRIPE2_LIVE_WEBHOOK_KEY') . '" placeholder="' . $langs->trans("Example") . ': whsec_xxxxxxxxxxxxxxxxxxxxxxxx">';
	$out = img_picto('', 'globe', 'class="pictofixedwidth"') . ' <span class="opacitymedium">' . $langs->trans("ToOfferALinkForLiveWebhook") . '</span> ';
	$url = dol_buildpath('/public/stripe2/ipn.php', 3);
	//global $dolibarr_main_instance_unique_id;
	//$url .= '?securitykey='.dol_hash('stripe2ipn-'.$dolibarr_main_instance_unique_id.'-'.$conf->global->STRIPE2_LIVE_PUBLISHABLE_KEY, 'md5');
	$out .= '<input type="text" id="onlinelivewebhookurl" class="minwidth500" value="' . $url . '" disabled>';
	$out .= ajax_autoselect("onlinelivewebhookurl", '0');
	print '<br>' . $out;
	print '</td><td>';
	if (getDolGlobalInt('MAIN_FEATURES_LEVEL') >= 2) {
		if (getDolGlobalString('STRIPE2_LIVE_WEBHOOK_KEY') && getDolGlobalString('STRIPE2_LIVE_SECRET_KEY') && getDolGlobalString('STRIPE2_LIVE_WEBHOOK_ID')) {
			if (utf8_check($conf->global->STRIPE2_TEST_SECRET_KEY)) {
				try {
					\Stripe\Stripe2::setApiKey($conf->global->STRIPE2_LIVE_SECRET_KEY);
					$endpoint = \Stripe\WebhookEndpoint::retrieve($conf->global->STRIPE2_LIVE_WEBHOOK_ID);
					$endpoint->enabled_events = $stripe2arrayofwebhookevents;
					if (GETPOST('webhook', 'alpha') == $conf->global->STRIPE2_LIVE_WEBHOOK_ID) {
						if (empty(GETPOST('status', 'alpha'))) {
							$endpoint->disabled = true;
						} else {
							$endpoint->disabled = false;
						}
					}
					$endpoint->url = $url;
					// @phan-suppress-next-line PhanDeprecatedFunction
					$endpoint->save();
					if ($endpoint->status == 'enabled') {
						print '<a class="reposition" href="' . $_SERVER['PHP_SELF'] . '?action=ipn&webhook=' . $endpoint->id . '&status=0">';
						print img_picto($langs->trans("Activated"), 'switch_on');
					} else {
						print '<a class="reposition" href="' . $_SERVER['PHP_SELF'] . '?action=ipn&webhook=' . $endpoint->id . '&status=1">';
						print img_picto($langs->trans("Disabled"), 'switch_off');
					}
				} catch (Exception $e) {
					print $e->getMessage();
				}
			}
		} else {
			print img_picto($langs->trans("Inactive"), 'statut5');
		}
	}
	print '</td></tr>';
}

print '</table>';
print '</div>';

print '<br>';


print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("UsageParameter") . '</td>';
print '<td>' . $langs->trans("Value") . '</td>';
print "</tr>\n";

print '<tr class="oddeven"><td>';
print $langs->trans("PublicVendorName") . '</td><td>';
print '<input class="minwidth300" type="text" name="ONLINE_PAYMENT_CREDITOR" value="' . getDolGlobalString('ONLINE_PAYMENT_CREDITOR') . '">';
print ' &nbsp; <span class="opacitymedium">' . $langs->trans("Example") . ': ' . $mysoc->name . '</span>';
print '</td></tr>';

print '<tr class="oddeven"><td>';
print $langs->trans("BankAccount") . '</td><td>';
print img_picto('', 'bank_account', 'class="pictofixedwidth"');
$form->select_comptes(getDolGlobalString('STRIPE2_BANK_ACCOUNT_FOR_PAYMENTS'), 'STRIPE2_BANK_ACCOUNT_FOR_PAYMENTS', 0, '', 1);
print '</td></tr>';


// Param to record automatically payouts (received from IPN payout.paid and payout.created)
print '<tr class="oddeven"><td>';
print $langs->trans("Stripe2AutoRecordPayout") . '</td><td>';
if ($conf->use_javascript_ajax) {
	print ajax_constantonoff('STRIPE2_AUTO_RECORD_PAYOUT', array(), null, 0, 0, 1);
} else {
	$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
	print $form->selectarray("STRIPE2_AUTO_RECORD_PAYOUT", $arrval, getDolGlobalInt('STRIPE2_AUTO_RECORD_PAYOUT'));
}
print '</td></tr>';

if (getDolGlobalInt('STRIPE2_AUTO_RECORD_PAYOUT')) {
	print '<tr class="oddeven"><td>';
	print $langs->trans("Stripe2UserAccountForActions") . '</td><td>';
	print img_picto('', 'user', 'class="pictofixedwidth"') . $form->select_dolusers(getDolGlobalString('STRIPE2_USER_ACCOUNT_FOR_ACTIONS'), 'STRIPE2_USER_ACCOUNT_FOR_ACTIONS', 0);
	print '</td></tr>';

	print '<tr class="oddeven"><td>';
	print $langs->trans("BankAccountForBankTransfer") . '</td><td>';
	print img_picto('', 'bank_account', 'class="pictofixedwidth"');
	$form->select_comptes(getDolGlobalString('STRIPE2_BANK_ACCOUNT_FOR_BANKTRANSFERS'), 'STRIPE2_BANK_ACCOUNT_FOR_BANKTRANSFERS', 0, '', 1);
	print '</td></tr>';
}

// Card Present for Stripe Terminal
if (getDolGlobalInt('MAIN_FEATURES_LEVEL') >= 2) {	// TODO Not used by current code
	print '<tr class="oddeven"><td>';
	print $langs->trans("STRIPE2_CARD_PRESENT") . '</td><td>';
	if ($conf->use_javascript_ajax) {
		print ajax_constantonoff('STRIPE2_CARD_PRESENT');
	} else {
		$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
		print $form->selectarray("STRIPE2_CARD_PRESENT", $arrval, $conf->global->STRIPE2_CARD_PRESENT);
	}
	print '</td></tr>';
}

// Locations for Stripe Terminal
if (getDolGlobalInt('MAIN_FEATURES_LEVEL') >= 2) {	// TODO Not used by current code
	print '<tr class="oddeven"><td>';
	print $langs->trans("TERMINAL_LOCATION") . '</td><td>';
	$service = 'StripeTest';
	$servicestatus = 0;
	if (getDolGlobalString('STRIPE2_LIVE') && !GETPOST('forcesandbox', 'alpha')) {
		$service = 'StripeLive';
		$servicestatus = 1;
	}

	try {
		global $stripe2arrayofkeysbyenv;
		$site_account = $stripe2arrayofkeysbyenv[$servicestatus]['secret_key'];
		if (!empty($site_account)) {
			\Stripe\Stripe2::setApiKey($site_account);
		}
		if (isModEnabled('stripe2') && (!getDolGlobalString('STRIPE2_LIVE') || GETPOST('forcesandbox', 'alpha'))) {
			$service = 'StripeTest';
			$servicestatus = '0';
			dol_htmloutput_mesg($langs->trans('YouAreCurrentlyInSandboxMode', 'Stripe2'), [], 'warning');
		} else {
			$service = 'StripeLive';
			$servicestatus = '1';
		}
		$stripe2 = new Stripe2($db);
		if (!empty($site_account)) {
			// If $site_account not defined, then key not set and no way to call API Location
			$stripe2acc = $stripe2->getStripe2Account($service);
			if ($stripe2acc) {
				$locations = \Stripe\Terminal\Location::all('', array("stripe_account" => $stripe2acc));
			} else {
				$locations = \Stripe\Terminal\Location::all();
			}
		}
	} catch (Exception $e) {
		print $e->getMessage() . '<br>';
	}

	// Define the array $location
	$location = array();
	$location[""] = $langs->trans("NotDefined");
	if (!empty($locations)) {
		foreach ($locations as $tmplocation) {
			$location[$tmplocation->id] = $tmplocation->display_name;
		}
	}

	print $form->selectarray("STRIPE2_LOCATION", $location, getDolGlobalString('STRIPE2_LOCATION'));
	print '</td></tr>';
}

print '<tr class="oddeven"><td>';
print $langs->trans("STRIPE2_SEPA_DIRECT_DEBIT") . '</td><td>';
if ($conf->use_javascript_ajax) {
	print ajax_constantonoff('STRIPE2_SEPA_DIRECT_DEBIT');
} else {
	$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
	print $form->selectarray("STRIPE2_SEPA_DIRECT_DEBIT", $arrval, getDolGlobalString('STRIPE2_SEPA_DIRECT_DEBIT'));
}
print '</td></tr>';


// Activate Klarna
if (getDolGlobalInt('MAIN_FEATURES_LEVEL') >= 2) {	// TODO Not used by current code
	print '<tr class="oddeven"><td>';
	print $langs->trans("STRIPE2_KLARNA") . '</td><td>';
	if ($conf->use_javascript_ajax) {
		print ajax_constantonoff('STRIPE2_KLARNA');
	} else {
		$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
		print $form->selectarray("STRIPE2_KLARNA", $arrval, $conf->global->STRIPE2_KLARNA);
	}
	print '</td></tr>';
}

// Activate Bancontact
if (getDolGlobalInt('MAIN_FEATURES_LEVEL') >= 2) {	// TODO Not used by current code
	print '<tr class="oddeven"><td>';
	print $langs->trans("STRIPE2_BANCONTACT") . '</td><td>';
	if ($conf->use_javascript_ajax) {
		print ajax_constantonoff('STRIPE2_BANCONTACT');
	} else {
		$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
		print $form->selectarray("STRIPE2_BANCONTACT", $arrval, $conf->global->STRIPE2_BANCONTACT);
	}
	print ' &nbsp; <span class="opacitymedium">' . $langs->trans("ExampleOnlyForBECustomers") . '</span>';
	print '</td></tr>';
}

// Activate iDEAL
if (getDolGlobalInt('MAIN_FEATURES_LEVEL') >= 2) {	// TODO Not used by current code
	print '<tr class="oddeven"><td>';
	print $langs->trans("STRIPE2_IDEAL") . '</td><td>';
	if ($conf->use_javascript_ajax) {
		print ajax_constantonoff('STRIPE2_IDEAL');
	} else {
		$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
		print $form->selectarray("STRIPE2_IDEAL", $arrval, $conf->global->STRIPE2_SEPA_DIRECT_DEBIT);
	}
	print ' &nbsp; <span class="opacitymedium">' . $langs->trans("ExampleOnlyForNLCustomers") . '</span>';
	print '</td></tr>';
}

// Activate Giropay
if (getDolGlobalInt('MAIN_FEATURES_LEVEL') >= 2) {	// TODO Not used by current code
	print '<tr class="oddeven"><td>';
	print $langs->trans("STRIPE2_GIROPAY") . '</td><td>';
	if ($conf->use_javascript_ajax) {
		print ajax_constantonoff('STRIPE2_GIROPAY');
	} else {
		$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
		print $form->selectarray("STRIPE2_GIROPAY", $arrval, $conf->global->STRIPE2_GIROPAY);
	}
	print ' &nbsp; <span class="opacitymedium">' . $langs->trans("ExampleOnlyForDECustomers") . '</span>';
	print '</td></tr>';
}

// Activate Sofort
if (getDolGlobalInt('MAIN_FEATURES_LEVEL') >= 2) {	// TODO Not used by current code
	print '<tr class="oddeven"><td>';
	print $langs->trans("STRIPE2_SOFORT") . '</td><td>';
	if ($conf->use_javascript_ajax) {
		print ajax_constantonoff('STRIPE2_SOFORT');
	} else {
		$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
		print $form->selectarray("STRIPE2_SOFORT", $arrval, $conf->global->STRIPE2_SOFORT);
	}
	print ' &nbsp; <span class="opacitymedium">' . $langs->trans("ExampleOnlyForATBEDEITNLESCustomers") . '</span>';
	print '</td></tr>';
}

print '<tr class="oddeven"><td>';
print $langs->trans("CSSUrlForPaymentForm") . '</td><td>';
print '<input class="width500" type="text" name="ONLINE_PAYMENT_CSS_URL" value="' . getDolGlobalString('ONLINE_PAYMENT_CSS_URL') . '">';
print ' &nbsp; <span class="opacitymedium">' . $langs->trans("Example") . ': http://mysite/mycss.css</span>';
print '</td></tr>';

print '<tr class="oddeven"><td>';
print $langs->trans("MessageForm") . '</td><td>';
$doleditor = new DolEditor('ONLINE_PAYMENT_MESSAGE_FORM', getDolGlobalString("ONLINE_PAYMENT_MESSAGE_FORM"), '', 100, 'dolibarr_details', 'In', false, true, true, ROWS_2, '90%');
$doleditor->Create();
print '</td></tr>';

print '<tr class="oddeven"><td>';
print $langs->trans("MessageOK") . '</td><td>';
$doleditor = new DolEditor('ONLINE_PAYMENT_MESSAGE_OK', getDolGlobalString("ONLINE_PAYMENT_MESSAGE_OK"), '', 100, 'dolibarr_details', 'In', false, true, true, ROWS_2, '90%');
$doleditor->Create();
print '</td></tr>';

print '<tr class="oddeven"><td>';
print $langs->trans("MessageKO") . '</td><td>';
$doleditor = new DolEditor('ONLINE_PAYMENT_MESSAGE_KO', getDolGlobalString("ONLINE_PAYMENT_MESSAGE_KO"), '', 100, 'dolibarr_details', 'In', false, true, true, ROWS_2, '90%');
$doleditor->Create();
print '</td></tr>';

print '<tr class="oddeven"><td>';
print $langs->trans("ONLINE_PAYMENT_SENDEMAIL") . '</td><td>';
print img_picto('', 'email', 'class="pictofixedwidth"');
print '<input class="minwidth200" type="text" name="ONLINE_PAYMENT_SENDEMAIL" value="' . getDolGlobalString('ONLINE_PAYMENT_SENDEMAIL') . '">';
print ' &nbsp; <span class="opacitymedium">' . $langs->trans("Example") . ': myemail@myserver.com, Payment service &lt;myemail2@myserver2.com&gt;</span>';
print '</td></tr>';

print '</table>';
print '</div>';

print '<br>';

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';

print '<tr class="liste_titre">';
print '<td>' . $langs->trans("UrlGenerationParameters") . '</td>';
print '<td>' . $langs->trans("Value") . '</td>';
print "</tr>\n";

// Payment token for URL
print '<tr class="oddeven"><td>';
print $langs->trans("SecurityToken") . '</td><td>';
print '<input class="minwidth300"  type="text" id="PAYMENT_SECURITY_TOKEN" name="PAYMENT_SECURITY_TOKEN" value="' . getDolGlobalString('PAYMENT_SECURITY_TOKEN') . '">';
if (!empty($conf->use_javascript_ajax)) {
	print '&nbsp;' . img_picto($langs->trans('Generate'), 'refresh', 'id="generate_token" class="linkobject"');
}
if (getDolGlobalString('PAYMENT_SECURITY_ACCEPT_ANY_TOKEN')) {
	$langs->load("errors");
	print img_warning($langs->trans("WarningTheHiddenOptionIsOn", 'PAYMENT_SECURITY_ACCEPT_ANY_TOKEN'), '', 'pictowarning marginleftonly');
}
print '</td></tr>';

print '<tr class="oddeven"><td>';
print $langs->trans("SecurityTokenIsUnique") . '</td><td>';
if ($conf->use_javascript_ajax) {
	print ajax_constantonoff('PAYMENT_SECURITY_TOKEN_UNIQUE', array(), null, 0, 0, 1);
} else {
	$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
	print $form->selectarray("PAYMENT_SECURITY_TOKEN_UNIQUE", $arrval, $conf->global->PAYMENT_SECURITY_TOKEN_UNIQUE);
}
print '</td></tr>';





//MODIF JOSE
// $date_start = getDolGlobalInt('STRIPE2_FEES_DATE_START');
// $date_end = getDolGlobalInt('STRIPE2_FEES_DATE_END');


// print '<tr class="oddeven"><td>';
// print $langs->trans("StartDateForFeesImport") . '</td><td>';
// print $form->selectDate($date_start ? $date_start : '', 'date_start', 0, 0, 0, '', 1, 0, 0, '', '', '', '', 1, '', '', 'tzserver');
// print '</td></tr>';

// print '<tr class="oddeven"><td>';
// print $langs->trans("EndDateForFeesImport") . '</td><td>';
// print $form->selectDate($date_end ? $date_end : '', 'date_end', 0, 0, 0, '', 1, 0, 0, '', '', '', '', 1, '', '', 'tzserver');
// print '</td></tr>';
//END MODIF

print '</table>';
print '</div>';

print dol_get_fiche_end();

print $form->buttonsSaveCancel("Save", '');

print '</form>';

print '<br><br>';

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';

$date_start = GETPOSTISSET('date_startday') ? dol_mktime(0, 0, 0, GETPOST('date_startmonth', 'int'), GETPOST('date_startday', 'int'), GETPOST('date_startyear', 'int')) : getDolGlobalInt('STRIPE2_FEES_DATE_START');
$date_end = GETPOSTISSET('date_endday') ? dol_mktime(23, 59, 59, GETPOST('date_endmonth', 'int'), GETPOST('date_endday', 'int'), GETPOST('date_endyear', 'int')) : getDolGlobalInt('STRIPE2_FEES_DATE_END');

print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="manual_update_stripe2_fees">';


print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Update des fees") . '</td>';
print '<td class="right">' . $langs->trans("Valeur") . '</td>';
print "</tr>\n";

print '<tr class="oddeven"><td>';
print $langs->trans("Stripe2AutoAddFeesToBank") . '<td class="right">';
if ($conf->use_javascript_ajax) {
	print ajax_constantonoff('STRIPE2_AUTO_ADD_FEES_TO_BANK', array(), null, 0, 0, 1);
} else {
	$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
	print $form->selectarray("STRIPE2_AUTO_ADD_FEES_TO_BANK", $arrval, getDolGlobalInt('STRIPE2_AUTO_ADD_FEES_TO_BANK'));
}
print '</td></tr>';

if (getDolGlobalInt('STRIPE2_AUTO_ADD_FEES_TO_BANK')) {

	print '<tr class="oddeven">';
	print '<td>' . $langs->trans("PeriodeToUpdate") . '</td>';
	print '<td class="right">';

	print '<span style="margin-right: 10px;">' . $langs->trans("DateStart") . '</span>';
	print $form->selectDate($date_start ?: '', 'date_start', 0, 0, 0, '', 1, 0, 0, '', '', '', '', 1, '', '', 'tzserver');


	print '<span style="margin-right: 10px;">' . $langs->trans("DateEnd") . '</span>';
	print $form->selectDate($date_end ?: '', 'date_end', 0, 0, 0, '', 1, 0, 0, '', '', '', '', 1, '', '', 'tzserver');

	print '<input type="submit" class="button" value="' . $langs->trans("UpdateFees") . '">';


	print '</td>';
	print '</tr>';
}

print '</form>';

$date_start2 = dol_mktime(0, 0, 0, GETPOST('date_start2month', 'int'), GETPOST('date_start2day', 'int'), GETPOST('date_start2year', 'int'));
$date_end2 = dol_mktime(23, 59, 59, GETPOST('date_end2month', 'int'), GETPOST('date_end2day', 'int'), GETPOST('date_end2year', 'int'));


print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="manual_reconcile_stripe2_payments">';

print '<tr class="oddeven">';
print '<td>' . $langs->trans("PeriodeToreconciliate") . '</td>';
print '<td class="right">';


print '<span style="margin-right: 10px;">' . $langs->trans("DateStart") . '</span>';
print $form->selectDate($date_start2 ?: '', 'date_start2', 0, 0, 0, '', 1, 0, 0, '', '', '', '', 1, '', '', 'tzserver');

print '<span style="margin-right: 10px;">' . $langs->trans("DateEnd") . '</span>';
print $form->selectDate($date_end2 ?: '', 'date_end2', 0, 0, 0, '', 1, 0, 0, '', '', '', '', 1, '', '', 'tzserver');

print '<input type="submit" class="button" value="' . $langs->trans("ReconcileButton") . '">';
print '</td>';
print '</tr>';
print '</form>';



print '</table>';
print '</div>';



print '<br><br>';


$token = '';

include DOL_DOCUMENT_ROOT . '/core/tpl/onlinepaymentlinks.tpl.php';

print info_admin($langs->trans("ExampleOfTestCreditCard", '4242424242424242 (no 3DSecure) or 4000000000003063 (3DSecure required) or 4000002760003184 (3DSecure2 required on all transaction) or 4000003800000446 (3DSecure2 required, the off-session allowed)', '4000000000000101', '4000000000000069', '4000000000000341') . '. ' . $langs->trans('SeeAlso', 'https://docs.stripe.com/testing?testing-method=card-numbers'));

if (getDolGlobalString('STRIPE2_SEPA_DIRECT_DEBIT')) {
	print info_admin($langs->trans("ExampleOfTestBankAcountForSEPA", 'AT611904300234573201 (pending->succeed) or AT861904300235473202 (pending->failed)') . '. ' . $langs->trans('SeeAlso', 'https://docs.stripe.com/testing?payment-method=sepa-direct-debit'));
}



if (!empty($conf->use_javascript_ajax)) {
	print "\n" . '<script type="text/javascript">';
	print '$(document).ready(function () {
	            $("#apidoc").hide();
	            $("#apidoca").click(function() {
					console.log("We click on apidoca show/hide");
	                $("#apidoc").show();
	            	$("#apidoca").hide();
					return false;
	            });
		   });';
	print '</script>';
}

// End of page
llxFooter();
$db->close();
