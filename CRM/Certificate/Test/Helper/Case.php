<?php

trait CRM_Certificate_Test_Helper_Case {

  private function createCase($params = []) {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $caseType = CRM_Certificate_Test_Fabricator_CaseType::fabricate();

    $params = array_merge([
      'contact_id' => $contact['id'],
      'creator_id' => $contact['id'],
      'case_type_id' => $caseType['id']
    ], $params);

    $case = CRM_Certificate_Test_Fabricator_Case::fabricate($params);

    $case = civicrm_api3('Case', 'getsingle', [
      'contact_id' => $contact['id'],
      'id' => $case['id'],
      'is_active' => 1
    ]);

    return $case;
  }
}
