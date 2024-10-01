<?php
/* Copyright (C) 2017 Mikael Carlavan <contact@mika-carl.fr>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    core/triggers/interface_99_modKeycloak_KeycloakTriggers.class.php
 * \ingroup keycloak
 * \brief   Example trigger.
 *
 */

require_once DOL_DOCUMENT_ROOT . '/core/triggers/dolibarrtriggers.class.php';
require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
include_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';

include_once DOL_DOCUMENT_ROOT . '/core/class/html.formmail.class.php';

dol_include_once("/keycloak/class/keycloak.class.php");


/**
 *  Class of triggers for Keycloak module
 */
class InterfaceKeycloakTriggers extends DolibarrTriggers
{
    /**
     * @var DoliDB Database handler
     */
    protected $db;

    /**
     * Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;

        $this->name = preg_replace('/^Interface/i', '', get_class($this));
        $this->family = "crm";
        $this->description = "Keycloak triggers.";
        // 'development', 'experimental', 'dolibarr' or version
        $this->version = '1.0.0';
        $this->picto = 'keycloak@keycloak';
    }

    /**
     * Trigger name
     *
     * @return string Name of trigger file
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Trigger description
     *
     * @return string Description of trigger file
     */
    public function getDesc()
    {
        return $this->description;
    }


    /**
     * Function called when a Dolibarrr business event is done.
     * All functions "runTrigger" are triggered if file
     * is inside directory core/triggers
     *
     * @param string $action Event action code
     * @param CommonObject $object Object
     * @param User $user Object user
     * @param Translate $langs Object langs
     * @param Conf $conf Object conf
     * @return int                    <0 if KO, 0 if no triggered ran, >0 if OK
     */
    public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
    {
        if (empty($conf->keycloak->enabled)) return 0;     // Module not active, we do nothing

        // Put here code you want to execute when a Dolibarr business events occurs.
        // Data and type of action are stored into $object and $action

        $langs->load("other");

        switch ($action) {

        }

        return 0;
    }
}
