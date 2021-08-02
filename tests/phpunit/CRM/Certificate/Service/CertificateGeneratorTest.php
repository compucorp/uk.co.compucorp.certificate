<?php


/**
 * Test service class for generating certificate html content
 * 
 * @group headless
 */
class CRM_Certificate_Service_CertificateGeneratorTest extends BaseHeadlessTest {

  use CRM_Certificate_Test_Helper_Case;

  public function testGenerateCertificateReturnCorrectFormat() {
    $content = $this->getMsgContent();
    $template = CRM_Certificate_Test_Fabricator_MessageTemplate::fabricate($content);
    $case = $this->createCase();
    $caseId = $case['id'];
    $contact = array_shift($case['contacts']);
    $contactId = $contact['contact_id'];

    $generatorService = new CRM_Certificate_Service_CertificateGenerator();
    $result = $generatorService->generate($template['id'], $contactId, $caseId);

    $this->assertTrue(is_array($result));
    $this->assertArrayHasKey("html", $result);
    $this->assertArrayHasKey("text", $result);
    $this->assertArrayHasKey("subject", $result);
  }

  public function testGenerateCertificateWillResolveTokens() {
    $content = $this->getMsgContent();
    $template = CRM_Certificate_Test_Fabricator_MessageTemplate::fabricate($content);
    $case = $this->createCase();
    $caseId = $case['id'];
    $contact = array_shift($case['contacts']);
    $contactId = $contact['contact_id'];

    $generatorService = new CRM_Certificate_Service_CertificateGenerator();
    $result = $generatorService->generate($template['id'], $contactId, $caseId);

    $this->assertContains($contact['display_name'], $result['html']);
    $this->assertContains($case['subject'], $result['html']);
  }

  private function getMsgContent() {
    return [
      'msg_html' => 'Hello {contact.display_name} Subject is {certificate_case.subject}',
      'msg_text' => __FUNCTION__,
      'msg_subject' => __FUNCTION__
    ];
  }
}
