<?php

use Symfony\Component\VarDumper\VarDumper;

/**
 * Test service class for storing new cretificate
 * 
 * @group headless
 */
class CRM_Certificate_Service_StoreCertificateConfigurationTest extends BaseHeadlessTest {

  /**
   * Test new instance of certificate configuration is created
   */
  public function testCreateCertificateConfiguration() {
    $certificateConfiguration = [
      'certificate_name' => 'test cert',
      'certificate_type' => CRM_Certificate_Enum_CertificateType::CASES,
      'certificate_msg_template'  => 1,
      'certificate_status' => '1,2',
      'certificate_linked_to' => '1,2'
    ];

    $certificateCreator = new CRM_Certificate_Service_StoreCertificateConfiguration($certificateConfiguration);
    $result = $certificateCreator->store();
    $this->assertTrue(is_array($result));
    $this->assertArrayHasKey('statuses', $result);
    $this->assertArrayHasKey('certificate', $result);
    $this->assertArrayHasKey('entityTypes', $result);
  }
}
