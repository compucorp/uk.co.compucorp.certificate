<?php


/**
 * Test service class for generating case certificate html content
 *
 * @group headless
 */
class CRM_Certificate_Service_CaseCertificateGeneratorTest extends BaseHeadlessTest {

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

  public function testGenerateCertificateWillResolveCaseCustomFieldTokens() {
    $customField = CRM_Certificate_Test_Fabricator_CustomField::fabricate([]);
    $customToken = 'custom_' . $customField['id'];
    $customTokenValue = md5(mt_rand());

    $content = $this->getMsgContent();
    $content['msg_html'] = $content['msg_html'] . '{certificate_case.' . $customToken . '}';
    $template = CRM_Certificate_Test_Fabricator_MessageTemplate::fabricate($content);
    $case = $this->createCase([$customToken => $customTokenValue]);
    $caseId = $case['id'];
    $contact = array_shift($case['contacts']);
    $contactId = $contact['contact_id'];

    $generatorService = new CRM_Certificate_Service_CertificateGenerator();
    $result = $generatorService->generate($template['id'], $contactId, $caseId);

    $this->assertContains($customTokenValue, $result['html']);
  }

  private function getMsgContent() {
    return [
      'msg_html' => 'Hello {contact.display_name} Subject is {certificate_case.subject}',
      'msg_text' => __FUNCTION__,
      'msg_subject' => __FUNCTION__,
    ];
  }

}
