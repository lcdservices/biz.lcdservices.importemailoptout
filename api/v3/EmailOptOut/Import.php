<?php
use CRM_Importemailoptout_ExtensionUtil as E;

/**
 * EmailOptOut.Import API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_email_opt_out_Import_spec(&$spec) {
  $spec['file']['api.required'] = 1;
  $spec['file']['description'] = 'Required. Full file name, found in the extension /data directory. e.g. MyEmailList.txt';

  $spec['limit']['api.required'] = 0;
  $spec['limit']['description'] = 'Optionally set a value for the number of records you want to process. This is helpful when testing the extension before processing your entire file.';

  $spec['group']['api.required'] = 0;
  $spec['group']['description'] = 'Optionally enter a group ID to remove matching contacts from the group.';
}

/**
 * EmailOptOut.Import API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_email_opt_out_Import($params) {
  $limit = CRM_Utils_Array::value('limit', $params);
  $group = CRM_Utils_Array::value('group', $params);
  $skipFilter = CRM_Utils_Array::value('skipFilter', $params, 0);
  $i = $p = 0;

  $path = CRM_Core_Resources::singleton()->getPath(CRM_Importemailoptout_ExtensionUtil::LONG_NAME);
  $file = $path.'/data/'.$params['file'];

  if (!file_exists($file)) {
    throw new API_Exception('File could not be found.', 900);
  }

  $fd = fopen($file, 'r');
  while ($row = fgets($fd)) {
    //Civi::log()->debug('civicrm_api3_email_opt_out_Import', ['row' => $row]);

    if ($skipFilter) {
      $sql = "
        SELECT id, contact_id
        FROM civicrm_email
        WHERE email = %1
        GROUP BY contact_id
      ";
    }
    else {
      $sql = "
        SELECT e.id, e.contact_id
        FROM civicrm_email e
        LEFT JOIN civicrm_ia_emailoptout_log ia
          ON e.email = ia.email
        WHERE e.email = %1
          AND ia.id IS NULL
        GROUP BY e.contact_id
      ";
    }

    $dao = CRM_Core_DAO::executeQuery($sql, [1 => [trim($row), 'String']]);

    while ($dao->fetch()) {
      //set all to opt out
      try {
        civicrm_api3('contact', 'create', [
          'id' => $dao->contact_id,
          'is_opt_out' => TRUE,
        ]);

        if ($group) {
          civicrm_api3('GroupContact', 'create', [
            'group_id' => $group,
            'contact_id' => $dao->contact_id,
            'status' => 'Removed',
          ]);
        }

        _optout_logEmail(trim($row), 'processed');

        $p++;
      }
      catch (CRM_API3_Exception $e) {
        Civi::log()->debug('civicrm_api3_email_opt_out_Import', ['e' => $e]);
      }
    }

    if (empty($dao->N)) {
      _optout_logEmail(trim($row), 'unmatched');
    }

    $i++;
    if (!empty($limit) && $i >= $limit) {
      break;
    }
  }

  return civicrm_api3_create_success(['processed' => $i, 'updated' => $p], $params, 'EmailOptOut', 'import');
}

function _optout_logEmail($email, $status) {
  CRM_Core_DAO::executeQuery("
    INSERT IGNORE INTO civicrm_ia_emailoptout_log
    (email, status)
    VALUES
    (%1, %2)
  ", [
    1 => [$email, 'String'],
    2 => [$status, 'String'],
  ]);
}
