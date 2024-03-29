<?php

use CRM_Certificate_Enum_CertificateType as CertificateType;
use CRM_Certificate_Test_Fabricator_CompuCertificate as CompuCertificateFabricator;

trait CRM_Certificate_Test_Helper_Case {

  private function createCase($params = []) {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $caseType = CRM_Certificate_Test_Fabricator_CaseType::fabricate();

    $params = array_merge([
      'contact_id' => $contact['id'],
      'creator_id' => $contact['id'],
      'case_type_id' => $caseType['id'],
    ], $params);

    $case = CRM_Certificate_Test_Fabricator_Case::fabricate($params);

    $result = civicrm_api3('Case', 'getdetails', [
      'contact_id' => $contact['id'],
      'id' => $case['id'],
      'is_active' => 1,
    ]);

    $case = array_shift($result['values']);
    return $case;
  }

  private function createCaseCertificate($params = []) {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $clientId = $params['client_id'] ?? $contact['id'];
    $creatorId = $params['creator_id'] ?? $contact['id'];
    $caseType = CRM_Certificate_Test_Fabricator_CaseType::fabricate();
    $caseStatus = CRM_Certificate_Test_Fabricator_CaseStatus::fabricate();

    $case = CRM_Certificate_Test_Fabricator_Case::fabricate(
      [
        'status_id' => $caseStatus['value'],
        'contact_id' => $clientId,
        'creator_id' => $creatorId,
        'case_type_id' => $caseType['id'],
      ]
    );

    $values = [
      'type' => CertificateType::CASES,
      'linked_to' => [$caseType['id']],
      'statuses' => [$caseStatus['value']],
      'start_date' => $params['start_date'] ?? date('Y-m-d'),
      'min_valid_from_date' => $params['min_valid_from_date'] ?? date("Y-m-d"),
      'max_valid_through_date' => $params['max_valid_through_date'] ?? date("Y-m-d", strtotime(date("Y-m-d") . " + 30 days")),
      'relationship_types' => $params['relationship_types'] ?? [],
      'end_date' => $params['end_date'] ?? date('Y-m-d', strtotime(date('Y-m-d') . " 10 days")),
    ];

    CompuCertificateFabricator::fabricate(CertificateType::CASES, $values);

    return $case;
  }

}
