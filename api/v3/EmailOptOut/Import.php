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
  $i = $p = 0;

  $path = CRM_Core_Resources::singleton()->getPath(CRM_Importemailoptout_ExtensionUtil::LONG_NAME);
  $file = $path.'/data/'.$params['file'];

  if (!file_exists($file)) {
    throw new API_Exception('File could not be found.', 900);
  }

  $fd = fopen($file, 'r');
  while ($row = fgets($fd)) {
    Civi::log()->debug('civicrm_api3_email_opt_out_Import', ['row' => $row]);

    $dao = CRM_Core_DAO::executeQuery("
      SELECT id, contact_id
      FROM civicrm_email
      WHERE email = %1
      GROUP BY contact_id
    ", [1 => [trim($row), 'String']]);

    while ($dao->fetch()) {
      //set all to opt out
      try {
        civicrm_api3('contact', 'create', [
          'id' => $dao->contact_id,
          'is_opt_out' => TRUE,
        ]);

        $p++;
      }
      catch (CRM_API3_Exception $e) {
        Civi::log()->debug('civicrm_api3_email_opt_out_Import', ['e' => $e]);
      }
    }

    $i++;
    if (!empty($limit) && $i >= $limit) {
      break;
    }
  }

  return civicrm_api3_create_success(['processed' => $i, 'updated' => $p], $params, 'EmailOptOut', 'import');
}
