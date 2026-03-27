<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2025		SuperAdmin
 * Copyright (C) 2024       Frédéric France         <frederic.france@free.fr>
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
 * \file    objectbanner/admin/about.php
 * \ingroup objectbanner
 * \brief   About page of module ObjectBanner.
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
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once '../lib/objectbanner.lib.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

// Translations
$langs->loadLangs(array("errors", "admin", "objectbanner@objectbanner"));

// Access control
if (!$user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');


/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);

$help_url = '';
$title = "ObjectBannerSetup";

llxHeader('', $langs->trans($title), $help_url, '', 0, 0, '', '', '', 'mod-objectbanner page-admin_about');

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($title), $linkback, 'title_setup');

// Configuration header
$head = objectbannerAdminPrepareHead();
print dol_get_fiche_head($head, 'about', $langs->trans($title), 0, 'objectbanner@objectbanner');

// About page content
print '<div class="div-table-responsive-no-min">';
print '<table class="border centpercent">';

// What is ObjectBanner?
print '<tr><td class="titlefield">'.$langs->trans("AboutObjectBannerWhat").'</td>';
print '<td>'.$langs->trans("AboutObjectBannerWhatDesc").'</td></tr>';

// Integration
print '<tr><td class="titlefield">'.$langs->trans("AboutObjectBannerIntegration").'</td>';
print '<td>'.$langs->trans("AboutObjectBannerIntegratedBy").'<br>';
print '<strong>Anatole Conseil</strong> (<a href="mailto:nz@anatoleconseil.com">nz@anatoleconseil.com</a>)';
print '</td></tr>';

// Features
print '<tr><td class="titlefield">'.$langs->trans("AboutObjectBannerFeatures").'</td>';
print '<td><ul>';
print '<li>'.$langs->trans("AboutObjectBannerFeature1").'</li>';
print '<li>'.$langs->trans("AboutObjectBannerFeature2").'</li>';
print '<li>'.$langs->trans("AboutObjectBannerFeature3").'</li>';
print '<li>'.$langs->trans("AboutObjectBannerFeature4").'</li>';
print '</ul></td></tr>';

// License
print '<tr><td class="titlefield">'.$langs->trans("AboutObjectBannerLicense").'</td>';
print '<td>';
print '<strong>'.$langs->trans("AboutObjectBannerLicenseType").'</strong>';
print '</td></tr>';

print '</table>';
print '</div>';

// Page end
print dol_get_fiche_end();
llxFooter();
$db->close();
