<?php

/**
 * CompuCertificate.Getcontactcertificates API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_compu_certificate_Getcontactcertificates_spec(&$spec) {
  $spec['entity'] = [
    'title' => 'Entity',
    'description' => 'Retrieve certificates for specific entity',
    'type' => CRM_Utils_Type::T_STRING,
  ];
}

/**
 * CompuCertificate.Getcontactcertificates API
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
function civicrm_api3_compu_certificate_Getcontactcertificates($params) {
  $ceertificateWrapper = new CRM_Certificate_Api_Wrapper_CompuCertificate();
  $certificateList = $ceertificateWrapper->getContactCertificates($params);
  return civicrm_api3_create_success($certificateList, $params, 'CompuCertificate', 'Getcontactcertificates');
}
