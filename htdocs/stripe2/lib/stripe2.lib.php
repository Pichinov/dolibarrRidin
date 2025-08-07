<?php
/* Copyright (C) 2017      Alexandre Spangaro   <aspangaro@open-dsi.fr>
 * Copyright (C) 2024		Frédéric France			<frederic.france@free.fr>
 * Copyright (C) 2024		MDW						<mdeweerd@users.noreply.github.com>
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
 *	\file			htdocs/stripe2/lib/stripe2.lib.php
 *	\ingroup		stripe2
 *  \brief			Library for common stripe2 functions
 */

require_once DOL_DOCUMENT_ROOT . '/core/lib/payments.lib.php';

/**
 *  Define head array for tabs of stripe2 tools setup pages
 *
 * @return	array<array{0:string,1:string,2:string}>	Array of tabs to show
 */
function stripe2admin_prepare_head()
{
	global $langs, $conf;

	$h = 0;
	$head = array();

	$head[$h][0] = DOL_URL_ROOT . "/stripe2/admin/stripe2.php";
	$head[$h][1] = $langs->trans("Stripe2");
	$head[$h][2] = 'stripe2account';
	$h++;

	$object = new stdClass();

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
	// $this->tabs = array('entity:-tabname);   												to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'stripe2admin');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'stripe2admin', 'remove');

	return $head;
}


/**
 * Show footer of company in HTML pages
 *
 * @param   Societe		$fromcompany	Third party
 * @param   Translate	$langs			Output language
 * @return	void
 */
function html_print_stripe2_footer($fromcompany, $langs)
{
	global $conf;

	// Juridical status
	$line1 = "";
	if ($fromcompany->forme_juridique_code) {
		$line1 .= ($line1 ? " - " : "") . getFormeJuridiqueLabel((string) $fromcompany->forme_juridique_code);
	}
	// Capital
	if ($fromcompany->capital) {
		$line1 .= ($line1 ? " - " : "") . $langs->transnoentities("CapitalOf", $fromcompany->capital) . " " . $langs->transnoentities("Currency" . $conf->currency);
	}

	$reg = array();

	// Prof Id 1
	if ($fromcompany->idprof1 && ($fromcompany->country_code != 'FR' || !$fromcompany->idprof2)) {
		$field = $langs->transcountrynoentities("ProfId1", $fromcompany->country_code);
		if (preg_match('/\((.*)\)/i', $field, $reg)) {
			$field = $reg[1];
		}
		$line1 .= ($line1 ? " - " : "") . $field . ": " . $fromcompany->idprof1;
	}
	// Prof Id 2
	if ($fromcompany->idprof2) {
		$field = $langs->transcountrynoentities("ProfId2", $fromcompany->country_code);
		if (preg_match('/\((.*)\)/i', $field, $reg)) {
			$field = $reg[1];
		}
		$line1 .= ($line1 ? " - " : "") . $field . ": " . $fromcompany->idprof2;
	}

	// Second line of company infos
	$line2 = "";
	// Prof Id 3
	if ($fromcompany->idprof3) {
		$field = $langs->transcountrynoentities("ProfId3", $fromcompany->country_code);
		if (preg_match('/\((.*)\)/i', $field, $reg)) {
			$field = $reg[1];
		}
		$line2 .= ($line2 ? " - " : "") . $field . ": " . $fromcompany->idprof3;
	}
	// Prof Id 4
	if ($fromcompany->idprof4) {
		$field = $langs->transcountrynoentities("ProfId4", $fromcompany->country_code);
		if (preg_match('/\((.*)\)/i', $field, $reg)) {
			$field = $reg[1];
		}
		$line2 .= ($line2 ? " - " : "") . $field . ": " . $fromcompany->idprof4;
	}
	// IntraCommunautary VAT
	if ($fromcompany->tva_intra != '') {
		$line2 .= ($line2 ? " - " : "") . $langs->transnoentities("VATIntraShort") . ": " . $fromcompany->tva_intra;
	}

	print '<br><br><hr>' . "\n";
	print '<div class="center"><span style="font-size: 10px;">' . "\n";
	print $fromcompany->name . '<br>';
	print $line1 . '<br>';
	print $line2;
	print '</span></div>' . "\n";
}

//MODIF JOSE
/**
 * Add a line on bank account with Stripe2 fees
 *
 * @param string  $ref         Reference to add in the description (e.g. transaction ID)
 * @param float   $totalamount Fee amount to insert (should be positive, will be inverted in the function)
 * @param int     $fk_account  ID of the Stripe2 bank account in Dolibarr
 * @param string  $stripe2_id   Stripe2 transaction ID (used in num_chq to detect duplicates)
 * @param int     $date        Payment date (timestamp), default now
 * @param int     $datev       Value date (timestamp), default now
 * @return int                 Bank line ID or -1 on error
 */
function stripe2AddPaymentFeeOnBank($ref, $totalamount, $fk_account, $stripe2_id, $date = null, $datev = null)
{
	global $db, $conf, $user, $langs;

	$db->begin();
	$error = 0;

	if (empty($date))  $date = dol_now();
	if (empty($datev)) $datev = dol_now();

	$amount = 0 - abs($totalamount);

	include_once DOL_DOCUMENT_ROOT . '/compta/bank/class/account.class.php';
	$acc = new Account($db);
	$result = $acc->fetch($fk_account);
	if ($result < 0) {
		return -1;
	}

	$label = $langs->trans('Stripe2Fees');
	if (!empty($ref)) {
		$label .= " " . $ref;
	} elseif (!empty($date)) {
		$label .= " " . dol_print_date($date, 'day');
	}

	$sql = "SELECT count(*) as count FROM " . MAIN_DB_PREFIX . "bank
	        WHERE num_chq = '" . $db->escape($stripe2_id) . "'
	          AND fk_account = '" . (int) $fk_account . "'
	          AND amount = " . (float) $amount;
	$resql = $db->query($sql);
	if ($resql) {
		$obj = $db->fetch_object($resql);
		if ($obj->count > 0) {
			dol_syslog("stripe2AddPaymentFeeOnBank duplicate found for ID $stripe2_id");
			$db->commit();
			return 0;
		} else {
			dol_syslog("stripe2AddPaymentFeeOnBank inserting fee for ID $stripe2_id");

			$bank_line_id = $acc->addline(
				$date,
				'PRE',
				$label,
				$amount,
				$stripe2_id,
				'',
				$user,
				'',
				'',
				'',
				$datev
			);

			if ($bank_line_id <= 0) {
				$error++;
				$db->rollback();
				return -1;
			}
		}
	} else {
		$error++;
		$db->rollback();
		return -1;
	}

	$db->commit();
	return $bank_line_id;
}


function trouverIdTiersParEmail($email)
{
	global $db; // Obtenir l'objet de connexion à la base de données de Dolibarr

	// Préparer la requête SQL pour récupérer l'ID du tiers en fonction de l'email
	// $sql = "SELECT fk_soc FROM llx_socpeople WHERE email = '$email'";
	$sql = "SELECT fk_soc FROM llx_socpeople WHERE email = '" . $db->escape($email) . "'";

	// Exécuter la requête avec la méthode fetch_array pour obtenir un tableau de résultats

	$result = $db->query($sql);
	if ($result) {
		// Récupérer l'ID du tiers
		$row = $db->fetch_array($result);
		if ($row) {
			$idTiers = $row['fk_soc'];
			return $idTiers;
		}
	}

	return false;
}

function trouverIdFactureParRefClient($refClient)
{
	global $db; // Obtenir l'objet de connexion à la base de données de Dolibarr

	// Préparer la requête SQL pour récupérer l'ID de la facture en fonction de la référence client
	$sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "facture WHERE ref_client = '" . $db->escape($refClient) . "'";

	// Exécuter la requête avec la méthode query pour obtenir un résultat
	$result = $db->query($sql);
	if ($result) {
		// Récupérer l'ID de la facture
		$row = $db->fetch_array($result);
		if ($row) {
			$idFacture = $row['rowid'];
			return $idFacture;
		}
	}

	return false;
}
//END MODIF