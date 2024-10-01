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
 * GNU General Public License for more detaile.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       htdocs/keycloak/class/keycloak.class.php
 *  \ingroup    keycloak
 *  \brief      File of class to manage keycloaks
 */
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobjectline.class.php';
require_once DOL_DOCUMENT_ROOT . '/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/price.lib.php';

/**
 * Class to manage products or services
 */
class Keycloak extends CommonObject
{
    /**
     * @var string ID to identify managed object
     */
    public $element = 'keycloak';

    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element = '';

    /**
     * @var string Name of subtable line
     */
    public $table_element_line = '';

    /**
     * @var string Name of class line
     */
    public $class_element_line = '';

    /**
     * @var string Field name with ID of parent key if this field has a parent
     */
    public $fk_element = '';

    /**
     * @var string String with name of icon for commande class. Here is object_order.png
     */
    public $picto = 'keycloak@keycloak';

    /**
     * 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
     * @var int
     */
    public $ismultientitymanaged = 1;
    /**
     * {@inheritdoc}
     */
    protected $table_ref_field = '';


    /**
     *  Constructor
     *
     * @param DoliDB $db Database handler
     */
    function __construct($db)
    {
        global $langs;

        $this->db = $db;
    }

    protected function getBaseUrl()
    {
        global $conf;

        return rtrim(rtrim($conf->global->KEYCLOAK_BASE_URL, '/').'/realms/'.$conf->global->KEYCLOAK_REALM, '/');
    }


    /**
     * {@inheritdoc}
     */
    public function getAuthUrl($state = null)
    {
        return $this->buildAuthUrlFromBase($this->getBaseUrl().'/protocol/openid-connect/auth', $state);
    }

    protected function getTokenUrl()
    {
        return $this->getBaseUrl().'/protocol/openid-connect/token';
    }

    /**
     * Build the authentication URL for the provider from the given base URL.
     *
     * @param  string  $url
     * @param  string  $state
     * @return string
     */
    protected function buildAuthUrlFromBase($url, $state)
    {
        return $url.'?'.http_build_query($this->getCodeFields($state), '', '&', PHP_QUERY_RFC1738);
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @param  string|null  $state
     * @return array
     */
    protected function getCodeFields($state = null)
    {
        global $conf;

        $fields = [
            'client_id' => $conf->global->KEYCLOAK_CLIENT_ID,
            'redirect_uri' => $conf->global->KEYCLOAK_REDIRECT_URI,
            'response_type' => 'code',
        ];

        return $fields;
    }
}
