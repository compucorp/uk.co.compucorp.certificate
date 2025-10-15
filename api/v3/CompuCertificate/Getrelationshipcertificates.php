<?php

/**
 * CompuCertificate.Getrelationshipcertificates API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_compu_certificate_Getrelationshipcertificates_spec(&$spec) {
  $spec['entity'] = [
    'title' => 'Entity',
    'description' => 'Retrieve certificates for specific entity',
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $spec['contact_id'] = [
    'title' => 'Contact ID',
    'description' => 'Id of the contact to retrieve certificates for',
    'type' => CRM_Utils_Type::T_INT,
  ];
  $spec['primary_contact_id'] = [
    'title' => 'Primary Contact ID',
    'description' => 'Return only certificates whose primary (related) contact matches this ID.',
    'type' => CRM_Utils_Type::T_INT,
    'FKClassName' => 'CRM_Contact_BAO_Contact',
  ];
}

/**
 * CompuCertificate.Getrelationshipcertificates API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_compu_certificate_Getrelationshipcertificates($params) {
  $certificateWrapper = new CRM_Certificate_Api_Wrapper_CompuCertificate();
  $certificateList = $certificateWrapper->getRelationshipCertificates($params);
  return civicrm_api3_create_success($certificateList, $params, 'CompuCertificate', 'Getrelationshipcertificates');
}
