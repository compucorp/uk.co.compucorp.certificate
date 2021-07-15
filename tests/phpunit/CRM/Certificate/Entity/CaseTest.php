<?php

/**
 * Test case entity class
 * 
 * @group headless
 */
class CRM_Certificate_Entity_CaseTest extends BaseHeadlessTest {

  /**
   * Test the appropraite types are returned
   *  i.e. only active types are returned
   */
  public function testGetTypesReturnsActiveOnes() {
    $inactiveType = CRM_Certificate_Test_Fabricator_CaseType::fabricate(['is_active' => 0])['id'];
    $activeType = CRM_Certificate_Test_Fabricator_CaseType::fabricate(['is_active' => 1])['id'];

    $caseEntity = new CRM_Certificate_Entity_Case();
    $types = $caseEntity->getTypes();

    $this->assertTrue(is_array($types));
    $this->assertTrue(!empty(array_diff([$inactiveType], $types)));
    $this->assertTrue(empty(array_diff([$activeType], $types)));
  }

  /**
   * Test the appropraite statuses are returned
   *  i.e. only active statuses are returned
   */
  public function testGetStatusesReturnsActiveOnes() {
    $inactiveStatus = CRM_Certificate_Test_Fabricator_CaseStatus::fabricate(['is_active' => 0])['value'];
    $activeStatus = CRM_Certificate_Test_Fabricator_CaseStatus::fabricate(['is_active' => 1])['value'];

    $caseEntity = new CRM_Certificate_Entity_Case();
    $statuses = $caseEntity->getStatuses();

    $this->assertTrue(is_array($statuses));
    $this->assertTrue(!empty(array_diff([$inactiveStatus], $statuses)));
    $this->assertTrue(empty(array_diff([$activeStatus], $statuses)));
  }

  /**
   * Test that only the statuses configured for a case certificate is returned
   */
  public function testOnlyStatusesConfiguredForCertificateAreReturned() {
    $caseStatus = CRM_Certificate_Test_Fabricator_CaseStatus::fabricate();
    $caseType = CRM_Certificate_Test_Fabricator_CaseType::fabricate();

    $values = [
      'type' => CRM_Certificate_Enum_CertificateType::CASES,
      'linked_to' => [$caseType['id']],
      'statuses' => [$caseStatus['value']]
    ];

    $expectedStatus = ['id' => $caseStatus['value'], 'label' => $caseStatus['label']];

    $certificate = $this->createCertificate($values)['certificate'];

    $caseEntity = new CRM_Certificate_Entity_Case();
    $statuses = $caseEntity->getCertificateConfiguredStatuses($certificate->id);

    $this->assertTrue(is_array($statuses));
    $this->assertCount(1, $statuses);
    $this->assertContains($expectedStatus, $statuses);
  }

  /**
   * Test that only the types configured for a case certificate is returned 
   */
  public function testOnlyTypesConfiguredForCertificateAreReturned() {
    $caseStatus = CRM_Certificate_Test_Fabricator_CaseStatus::fabricate();
    $caseType = CRM_Certificate_Test_Fabricator_CaseType::fabricate();

    $values = [
      'type' => CRM_Certificate_Enum_CertificateType::CASES,
      'linked_to' => [$caseType['id']],
      'statuses' => [$caseStatus['value']]
    ];

    $expectedType = ['id' => $caseType['id'], 'label' => $caseType['name']];

    $certificate = $this->createCertificate($values)['certificate'];

    $caseEntity = new CRM_Certificate_Entity_Case();
    $types = $caseEntity->getCertificateConfiguredTypes($certificate->id);

    $this->assertTrue(is_array($types));
    $this->assertCount(1, $types);
    $this->assertContains($expectedType, $types);
  }

  private function createCertificate($values = []) {
    return CRM_Certificate_Test_Fabricator_CompuCertificate::fabricate(CRM_Certificate_Enum_CertificateType::CASES, $values);
  }
}
