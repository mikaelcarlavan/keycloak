<?php
/* Copyright (C) 2022 Jeritiana Ravelojaona <jeritiana.rav@smartone.ai>
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
 *      \file       htdocs/core/login/functions_keycloak.php
 *      \ingroup    core
 *      \brief      OpenID Connect: Authorization Code flow authentication
 *
 *      See https://github.com/Dolibarr/dolibarr/issues/22740 for more information about setup keycloak
 */

include_once DOL_DOCUMENT_ROOT.'/core/lib/geturl.lib.php';
include_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

/**
 * Check validity of user/password/entity
 * If test is ko, reason must be filled into $_SESSION["dol_loginmesg"]
 *
 * @param	string	$usertotest		Login
 * @param	string	$passwordtotest	Password
 * @param   int		$entitytotest   Number of instance (always 1 if module multicompany not enabled)
 * @return	string					Login if OK, '' if KO
 */

function check_user_password_keycloak($usertotest, $passwordtotest, $entitytotest)
{
    global $db, $conf, $langs;

    // Force master entity in transversal mode
    $entity = $entitytotest;
    if (!empty($conf->multicompany->enabled) && getDolGlobalString('MULTICOMPANY_TRANSVERSE_MODE')) {
        $entity = 1;
    }

    $login = '';

    dol_syslog("functions_keycloak::check_user_password_keycloak usertotest=".$usertotest." passwordtotest=".preg_replace('/./', '*', $passwordtotest)." entitytotest=".$entitytotest);

    // Step 1 is done by user: request an authorization code

    if (GETPOSTISSET('username')) {
        // OIDC does not require credentials here: pass on to next auth handler
        $_SESSION["dol_loginmesg"] = "Not an OpenID Connect flow";
        dol_syslog("functions_keycloak::check_user_password_keycloak not an OIDC flow");
    } elseif (GETPOSTISSET('code')) {
        $auth_code = GETPOST('code', 'aZ09');
        dol_syslog("functions_keycloak::check_user_password_keycloak code=".$auth_code);

        // Step 2: turn the authorization code into an access token, using client_secret
        $auth_param = [
            'grant_type'    => 'authorization_code',
            'client_id'     => $conf->global->KEYCLOAK_CLIENT_ID,
            'client_secret' => $conf->global->KEYCLOAK_CLIENT_SECRET,
            'code'          => $auth_code,
            'redirect_uri'  => DOL_MAIN_URL_ROOT,
        ];

        $keycloak = new Keycloak($db);

        $token_response = getURLContent($keycloak->getTokenUrl(), 'POST', http_build_query($auth_param));
        $token_content = json_decode($token_response['content']);
        dol_syslog("functions_keycloak::check_user_password_keycloak /token=".print_r($token_response, true), LOG_DEBUG);

        if (property_exists($token_content, 'access_token')) {
            // Step 3: retrieve user info using token
            $userinfo_headers = array('Authorization: Bearer '.$token_content->access_token);
            $userinfo_response = getURLContent($keycloak->getUserInfoUrl(), 'GET', '', 1, $userinfo_headers);

            $userinfo_content = json_decode($userinfo_response['content']);

            dol_syslog("functions_keycloak::check_user_password_keycloak /userinfo=".print_r($userinfo_response, true), LOG_DEBUG);

            $roles = array();
            if (isset($userinfo_content->resource_access->tools->roles)) {
                $roles = $userinfo_content->resource_access->tools->roles;
            }


            $groups = array();
            if (isset($userinfo_content->resource_access->tools->groups)) {
                $groups = $userinfo_content->resource_access->tools->groups;
            }

            $isAdmin = in_array('Admin', $roles);
            $isUser = in_array('User', $roles);

            if (!$isUser && !$isAdmin) {
                return false;
            }

            if (isset($userinfo_content->email)) {
                // Success: retrieve claim to return to Dolibarr as login
                $sql = 'SELECT rowid, login, entity, datestartvalidity, dateendvalidity';
                $sql .= ' FROM '.MAIN_DB_PREFIX.'user';
                $sql .= " WHERE email = '".$db->escape($userinfo_content->email)."'";
                $sql .= ' AND entity IN (0,'.(array_key_exists('dol_entity', $_SESSION) ? ((int) $_SESSION["dol_entity"]) : 1).')';

                dol_syslog("functions_openid::check_user_password_openid", LOG_DEBUG);

                $resql = $db->query($sql);
                if ($resql) {
                    $num = $db->num_rows($resql);
                    $user = new User($db);

                    if ($num > 0) {
                        $obj = $db->fetch_object($resql);
                        if ($obj) {
                            // Note: Test on date validity is done later natively with isNotIntoValidityDateRange() by core after calling checkLoginPassEntity() that call this method

                            $user->fetch($obj->rowid);

                            $user->lastname = $userinfo_content->family_name;
                            $user->firstname = $userinfo_content->given_name;
                            $user->admin = $isAdmin;
                            $user->update($user);

                            $login = $user->login;
                        }
                    } else {
                        // Create user ?
                        if (!empty($conf->global->KEYCLOAK_CREATE_USER)) {
                            $user->lastname = $userinfo_content->family_name;
                            $user->firstname = $userinfo_content->given_name;
                            $user->email = $userinfo_content->email;
                            $user->login = dol_buildlogin($user->lastname, $user->firstname);
                            $user->admin = $isAdmin;
                            $res = $user->create($user);

                            if ($res == -6) { // login already exists, use email instead
                                $user->login = $user->email;
                                $user->create($user);
                            }

                            $login = $user->login;
                        }
                    }

                    if ($user->id > 0) {
                        // Remove roles/groups
                        $usergroup = new UserGroup($db);
                        $usergroups = $usergroup->listGroupsForUser($user->id, false);
                        if (count($usergroups)) {
                            foreach ($usergroups as $usergroup) {
                                $user->RemoveFromGroup($usergroup, $user->entity);
                            }
                        }

                        // Add roles/groups
                        if (count($roles)) {
                            foreach ($roles as $role) {
                                $usergroup = new UserGroup($db);
                                if ($usergroup->fetch('', $role, false) > 0) {
                                    $user->SetInGroup($usergroup, $user->entity);
                                }
                            }
                        }
                    }
                }
            } elseif ($userinfo_content->error) {
                // Got user info response but content is an error
                $_SESSION["dol_loginmesg"] = "Error in OAuth 2.0 flow (".$userinfo_content->error_description.")";
            } elseif ($userinfo_response['curl_error_no']) {
                // User info request error
                $_SESSION["dol_loginmesg"] = "Network error: ".$userinfo_response['curl_error_msg']." (".$userinfo_response['curl_error_no'].")";
            } else {
                // Other user info request error
                $_SESSION["dol_loginmesg"] = "Userinfo request error (".$userinfo_response['http_code'].")";
            }
        } elseif ($token_content->error) {
            // Got token response but content is an error
            $_SESSION["dol_loginmesg"] = "Error in OAuth 2.0 flow (".$token_content->error_description.")";
        } elseif ($token_response['curl_error_no']) {
            // Token request error
            $_SESSION["dol_loginmesg"] = "Network error: ".$token_response['curl_error_msg']." (".$token_response['curl_error_no'].")";
        } else {
            // Other token request error
            $_SESSION["dol_loginmesg"] = "Token request error (".$token_response['http_code'].")";
        }
    } else {
        // No code received
        $_SESSION["dol_loginmesg"] = "Error in OAuth 2.0 flow (no code received)";
    }

    dol_syslog("functions_keycloak::check_user_password_keycloak END");

    return !empty($login) ? $login : false;
}
