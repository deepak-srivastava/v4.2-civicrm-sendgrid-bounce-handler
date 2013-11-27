<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 */

session_start();
//require_once '/home/vedaadmin/public_html/niab-test.vedaconsulting.co.uk/public/sites/default/civicrm.settings.php';
require_once '/var/www/cms.loc/sites/cms42.loc/civicrm.settings.php';
$config = CRM_Core_Config::singleton();

// authenticate
CRM_Utils_System::authenticateKey();

$json = json_decode($HTTP_RAW_POST_DATA, TRUE);
foreach ($json as $data) {
  switch( $data['event'] ) {
  case 'bounce':
    require_once 'api/v2/Location.php';
    require_once 'api/v2/Contact.php';

    $params = array('email' => $data['email']);
    $result = civicrm_contact_get($params);
    if (!empty($result)) {
      foreach ($result as $contact) {
	if (CRM_Utils_Array::value('contact_id', $contact)) {
	  $params = array('contact_id' => $contact['contact_id']);
	  $loc    = civicrm_location_get($params);
	  
	  $locationTypeID = NULL;
	  foreach ($loc['email'] as $email) {
	    if ($email['email'] == $data['email']) {
	      $locationTypeID = $email['location_type_id'];
	      break;
	    }
	  }
	  
	  if ($locationTypeID) {
	    $params = array(
	      'contact_id' => $contact['contact_id'],
	      'email' => array(
		array(
		  'email'   => $data['email'],
		  'on_hold' => 1,
		  'location_type_id' => $locationTypeID,
		)
	      )
	    );
	    $result = civicrm_location_update($params);
	  } else {
	    CRM_Core_Error::debug_log_message("Couldn't find location-type-id for contact-id - {$contact['contact_id']} and therefore couldn't process bounce.");
	  }
	}
      }
    }
    break;
  }
}

return 200;
