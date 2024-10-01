<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2017 Mikael Carlavan <contact@mika-carl.fr>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       htdocs/keycloak/admin/setup.php
 *  \ingroup    keycloak
 *  \brief      Admin page
 */


$res=@include("../../main.inc.php");                   // For root directory
if (! $res) $res=@include("../../../main.inc.php");    // For "custom" directory

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
dol_include_once("/keycloak/lib/keycloak.lib.php");

// Translations
$langs->load("keycloak@keycloak");
$langs->load("admin");

// Access control
if (! $user->admin) accessforbidden();

// Parameters
$action = GETPOST('action', 'alpha');
$value = GETPOST('value', 'alpha');

$reg = array();

/*
 * Actions
 */


include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';

$error=0;

// Action mise a jour ou ajout d'une constante
if ($action == 'update')
{
	$constname=GETPOST('constname','alpha');
	$constvalue=(GETPOST('constvalue_'.$constname) ? GETPOST('constvalue_'.$constname) : GETPOST('constvalue'));


	$consttype=GETPOST('consttype','alpha');
	$constnote=GETPOST('constnote');
	$res = dolibarr_set_const($db,$constname,$constvalue,'chaine',0,$constnote,$conf->entity);

	if (! $res > 0) $error++;

	if (! $error) {
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	} else {
		setEventMessages($langs->trans("Error"), null, 'errors');
	}
}

/*
 * View
 */

llxHeader('', $langs->trans('KeycloakSetup'));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';

print load_fiche_titre($langs->trans('KeycloakSetup'), $linkback);

// Configuration header
$head = keycloak_prepare_admin_head();
dol_fiche_head(
	$head,
	'settings',
	$langs->trans("ModuleKeycloakName"),
	0,
	"keycloak@keycloak"
);

$form = new Form($db);

print load_fiche_titre($langs->trans("KeycloakOptions"),'','');

print '<table class="noborder" width="100%">';
print '<tbody>';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Name").'</td>';
print '<td align="center">'.$langs->trans("Action").'</td>';
print "</tr>\n";


print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
print '<tr class="oddeven">';
print '<td>';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="update">';
print '<input type="hidden" name="constname" value="KEYCLOAK_CLIENT_ID">';
print '<input type="hidden" name="constnote" value="">';
print $langs->trans('DescKEYCLOAK_CLIENT_ID');
print '</td>';
print '<td>';
print '<input type="text" name="constvalue" value="'.$conf->global->KEYCLOAK_CLIENT_ID.'" />';
print '<input type="hidden" name="consttype" value="chaine">';
print '</td>';
print '<td align="center">';
print '<input type="submit" class="button" value="'.$langs->trans("Update").'" name="Button">';
print '</td>';
print '</tr>';
print '</form>';


print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
print '<tr class="oddeven">';
print '<td>';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="update">';
print '<input type="hidden" name="constname" value="KEYCLOAK_CLIENT_SECRET">';
print '<input type="hidden" name="constnote" value="">';
print $langs->trans('DescKEYCLOAK_CLIENT_SECRET');
print '</td>';
print '<td>';
print '<input type="text" name="constvalue" value="'.$conf->global->KEYCLOAK_CLIENT_SECRET.'" />';
print '<input type="hidden" name="consttype" value="chaine">';
print '</td>';
print '<td align="center">';
print '<input type="submit" class="button" value="'.$langs->trans("Update").'" name="Button">';
print '</td>';
print '</tr>';
print '</form>';

print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
print '<tr class="oddeven">';
print '<td>';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="update">';
print '<input type="hidden" name="constname" value="KEYCLOAK_REDIRECT_URI">';
print '<input type="hidden" name="constnote" value="">';
print $langs->trans('DescKEYCLOAK_REDIRECT_URI');
print '</td>';
print '<td>';
print '<input type="text" name="constvalue" value="'.$conf->global->KEYCLOAK_REDIRECT_URI.'" />';
print '<input type="hidden" name="consttype" value="chaine">';
print '</td>';
print '<td align="center">';
print '<input type="submit" class="button" value="'.$langs->trans("Update").'" name="Button">';
print '</td>';
print '</tr>';
print '</form>';

print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
print '<tr class="oddeven">';
print '<td>';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="update">';
print '<input type="hidden" name="constname" value="KEYCLOAK_BASE_URL">';
print '<input type="hidden" name="constnote" value="">';
print $langs->trans('DescKEYCLOAK_BASE_URL');
print '</td>';
print '<td>';
print '<input type="text" name="constvalue" value="'.$conf->global->KEYCLOAK_BASE_URL.'" />';
print '<input type="hidden" name="consttype" value="chaine">';
print '</td>';
print '<td align="center">';
print '<input type="submit" class="button" value="'.$langs->trans("Update").'" name="Button">';
print '</td>';
print '</tr>';
print '</form>';

print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
print '<tr class="oddeven">';
print '<td>';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="update">';
print '<input type="hidden" name="constname" value="KEYCLOAK_REALM">';
print '<input type="hidden" name="constnote" value="">';
print $langs->trans('DescKEYCLOAK_REALM');
print '</td>';
print '<td>';
print '<input type="text" name="constvalue" value="'.$conf->global->KEYCLOAK_REALM.'" />';
print '<input type="hidden" name="consttype" value="chaine">';
print '</td>';
print '<td align="center">';
print '<input type="submit" class="button" value="'.$langs->trans("Update").'" name="Button">';
print '</td>';
print '</tr>';
print '</form>';

print '</tbody>';
print '</table>';

// Page end
dol_fiche_end();
llxFooter();
