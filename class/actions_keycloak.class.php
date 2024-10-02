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
 *  \file       htdocs/keycloak/class/actions_keycloak.class.php
 *  \ingroup    keycloak
 *  \brief      File of class to manage actions
 */
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT . '/user/class/usergroup.class.php';

dol_include_once("/keycloak/class/keycloak.class.php");


class ActionsKeycloak
{
    function beforeLoginAuthentication($parameters, &$object, &$action, $hookmanager)
    {
        global $db, $conf;

        if (GETPOSTISSET('code')) {
            $_GET['openid_mode'] = 'keycloak';
            return 0;
        } else {
            $keycloak = new Keycloak($db);
            $url = $keycloak->getAuthUrl();

            header("Location: ".$url);
            exit;
        }
    }

    // Required fix otherwise bug in style.css.php
    function afterLogin($parameters, &$object, &$action, $hookmanager)
    {
        global $db;

        $_SESSION["dol_screenwidth"] = intval($_SESSION["dol_screenwidth"]);
        $_SESSION["dol_screenheight"] = intval($_SESSION["dol_screenheight"]);

        return 0;
    }

    function afterLogout($parameters, &$object, &$action, $hookmanager)
    {
        global $db;

        $keycloak = new Keycloak($db);
        $url = $keycloak->getLogoutUrl();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }


        unset($_SESSION['dol_login']);
        unset($_SESSION['dol_entity']);
        unset($_SESSION['urlfrom']);


        header("Location: ".$url);
        exit;
    }

}


