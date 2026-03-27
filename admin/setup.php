<?php
/* Copyright (C) 2004-2017  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2024       Frédéric France         <frederic.france@free.fr>
 * Copyright (C) 2025		SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    objectbanner/admin/setup.php
 * \ingroup objectbanner
 * \brief   ObjectBanner setup page.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

// Libraries
require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
require_once '../lib/objectbanner.lib.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

// Translations
$langs->loadLangs(array("admin", "objectbanner@objectbanner"));

// Initialize a technical object to manage hooks of page. Note that conf->hooks_modules contains an array of hook context
/** @var HookManager $hookmanager */
$hookmanager->initHooks(array('objectbannersetup', 'globalsetup'));

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$modulepart = GETPOST('modulepart', 'aZ09');	// Used by actions_setmoduleoptions.inc.php

$error = 0;
$setupnotempty = 0;

// Access control
if (!$user->admin) {
	accessforbidden();
}


/*
 * Actions
 */

if ($action == 'update') {
	$error = 0;

	// Toggle options
	$showPropal = GETPOST('OBJECTBANNER_SHOW_PROPAL', 'int') ? 1 : 0;
	$showCommande = GETPOST('OBJECTBANNER_SHOW_COMMANDE', 'int') ? 1 : 0;
	$showFacture = GETPOST('OBJECTBANNER_SHOW_FACTURE', 'int') ? 1 : 0;
	$showExpedition = GETPOST('OBJECTBANNER_SHOW_EXPEDITION', 'int') ? 1 : 0;

	$res = dolibarr_set_const($db, 'OBJECTBANNER_SHOW_PROPAL', $showPropal, 'chaine', 0, '', $conf->entity);
	if (!($res > 0)) $error++;
	$res = dolibarr_set_const($db, 'OBJECTBANNER_SHOW_COMMANDE', $showCommande, 'chaine', 0, '', $conf->entity);
	if (!($res > 0)) $error++;
	$res = dolibarr_set_const($db, 'OBJECTBANNER_SHOW_FACTURE', $showFacture, 'chaine', 0, '', $conf->entity);
	if (!($res > 0)) $error++;
	$res = dolibarr_set_const($db, 'OBJECTBANNER_SHOW_EXPEDITION', $showExpedition, 'chaine', 0, '', $conf->entity);
	if (!($res > 0)) $error++;

	if (!$error) {
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	} else {
		setEventMessages($langs->trans("Error"), null, 'errors');
	}
}

include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';


/*
 * View
 */

$form = new Form($db);

$help_url = '';
$title = "ObjectBannerSetup";

llxHeader('', $langs->trans($title), $help_url, '', 0, 0, '', '', '', 'mod-objectbanner page-admin');

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($title), $linkback, 'title_setup');

// Configuration header
$head = objectbannerAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', $langs->trans($title), -1, "objectbanner@objectbanner");

// Setup page goes here
print '<span class="opacitymedium">'.$langs->trans("ObjectBannerSetupPage").'</span><br><br>';

// Module info
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';

print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '</tr>';

// Module version
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ModuleVersion").'</td>';
print '<td>0.1</td>';
print '</tr>';

// Module status info
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ObjectBannerStatus").'</td>';
print '<td><span class="badge badge-status4 badge-status">'.$langs->trans("Active").'</span></td>';
print '</tr>';

// Hooks info
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ObjectBannerHooks").'</td>';
print '<td>propalcard, ordercard, invoicecard, expeditioncard</td>';
print '</tr>';

print '</table>';
print '</div>';

print '<br>';

// Toggle configuration form
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="update">';

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';

print '<tr class="liste_titre">';
print '<td>'.$langs->trans("ObjectBannerDisplayOptions").'</td>';
print '<td class="center">'.$langs->trans("Status").'</td>';
print '</tr>';

// Propal toggle
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ObjectBannerShowPropal").'</td>';
print '<td class="center">';
print '<input type="checkbox" name="OBJECTBANNER_SHOW_PROPAL" value="1"'.(getDolGlobalInt('OBJECTBANNER_SHOW_PROPAL', 1) ? ' checked' : '').'>';
print '</td>';
print '</tr>';

// Commande toggle
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ObjectBannerShowCommande").'</td>';
print '<td class="center">';
print '<input type="checkbox" name="OBJECTBANNER_SHOW_COMMANDE" value="1"'.(getDolGlobalInt('OBJECTBANNER_SHOW_COMMANDE', 1) ? ' checked' : '').'>';
print '</td>';
print '</tr>';

// Facture toggle
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ObjectBannerShowFacture").'</td>';
print '<td class="center">';
print '<input type="checkbox" name="OBJECTBANNER_SHOW_FACTURE" value="1"'.(getDolGlobalInt('OBJECTBANNER_SHOW_FACTURE', 1) ? ' checked' : '').'>';
print '</td>';
print '</tr>';

// Expedition toggle
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ObjectBannerShowExpedition").'</td>';
print '<td class="center">';
print '<input type="checkbox" name="OBJECTBANNER_SHOW_EXPEDITION" value="1"'.(getDolGlobalInt('OBJECTBANNER_SHOW_EXPEDITION', 1) ? ' checked' : '').'>';
print '</td>';
print '</tr>';

print '</table>';
print '</div>';

print '<br>';
print '<div class="center">';
print '<input type="submit" class="button button-save" value="'.$langs->trans("Save").'">';
print '</div>';

print '</form>';

// Page end
print dol_get_fiche_end();

llxFooter();
$db->close();
