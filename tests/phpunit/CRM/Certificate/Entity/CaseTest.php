<?php

/**
 * Test case entity class
 *
 * @group headless
 */
class CRM_Certificate_Entity_CaseTest extends BaseHeadlessTest {

  use CRM_Certificate_Test_Helper_Case;

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
      'statuses' => [$caseStatus['value']],
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
      'statuses' => [$caseStatus['value']],
    ];

    $expectedType = ['id' => $caseType['id'], 'label' => $caseType['name']];

    $certificate = $this->createCertificate($values)['certificate'];

    $caseEntity = new CRM_Certificate_Entity_Case();
    $types = $caseEntity->getCertificateConfiguredTypes($certificate->id);

    $this->assertTrue(is_array($types));
    $this->assertCount(1, $types);
    $this->assertContains($expectedType, $types);
  }

  /**
   * Test that a certificate configuration is returned
   * for a case that meets the status and type of the
   * certificate configuration
   */
  public function testCanGetCaseCertificateConfiguration() {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $caseStatus = CRM_Certificate_Test_Fabricator_CaseStatus::fabricate();
    $caseType = CRM_Certificate_Test_Fabricator_CaseType::fabricate();

    $case = CRM_Certificate_Test_Fabricator_Case::fabricate(
      [
        'status_id' => $caseStatus['value'],
        'contact_id' => $contact['id'],
        'creator_id' => $contact['id'],
        'case_type_id' => $caseType['id'],
      ]
    );

    $values = [
      'type' => CRM_Certificate_Enum_CertificateType::CASES,
      'linked_to' => [$caseType['id']],
      'statuses' => [$caseStatus['value']],
    ];
    $this->createCertificate($values);

    $caseEntity = new CRM_Certificate_Entity_Case();
    $configuration = $caseEntity->getCertificateConfiguration($case["id"], $contact['id']);

    $this->assertInstanceOf(CRM_Certificate_BAO_CompuCertificate::class, $configuration);
  }

  /**
   * Test that a certificacte configuration is not returned
   * when the case status and type of the certificate is not met
   */
  public function testCertificationConfigurationNotReturnedIfNoCertificateIsConfigured() {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $caseStatus = CRM_Certificate_Test_Fabricator_CaseStatus::fabricate();
    $caseType = CRM_Certificate_Test_Fabricator_CaseType::fabricate();

    $case = CRM_Certificate_Test_Fabricator_Case::fabricate(
      [
        'status_id' => $caseStatus['value'],
        'contact_id' => $contact['id'],
        'creator_id' => $contact['id'],
        'case_type_id' => $caseType['id'],
      ]
    );
    $caseEntity = new CRM_Certificate_Entity_Case();
    $configuration = $caseEntity->getCertificateConfiguration($case["id"], $contact['id']);

    $this->assertFalse($configuration);
  }

  /**
   * Test that only the case certificate for contact with the Id
   * passed is returned.
   */
  public function testOnlyCaseCertificateForContactIsReturned() {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $contactCases = array_map(function () use ($contact) {
      return $this->createCaseCertificate(['client_id' => $contact["id"]]);
    }, [1, 2]);

    $otherContact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $this->createCaseCertificate(['client_id' => $otherContact["id"]]);

    $caseEntity = new CRM_Certificate_Entity_Case();
    $avaliableCertificates = $caseEntity->getContactCertificates($contact["id"]);

    $this->assertEquals(count($contactCases), count($avaliableCertificates));

    $expectedCasesId = array_column($contactCases, "id");
    $avaliableCertificatesCaseId = array_column($avaliableCertificates, "case_id");
    $this->assertCount(0, array_diff($expectedCasesId, $avaliableCertificatesCaseId));
  }

  private function createCertificate($values = []) {
    return CRM_Certificate_Test_Fabricator_CompuCertificate::fabricate(CRM_Certificate_Enum_CertificateType::CASES, $values);
  }

}
