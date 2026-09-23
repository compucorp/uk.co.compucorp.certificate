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

    $this->assertStringContainsString($contact['display_name'], $result['html']);
    $this->assertStringContainsString($case['subject'], $result['html']);
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

    $this->assertStringContainsString($customTokenValue, $result['html']);
  }

  public function testGenerateCertificateWillNotEvaluateSmartyWhenMailSmartyIsDisabled() {
    if (CRM_Utils_Constant::value('CIVICRM_MAIL_SMARTY')) {
      $this->markTestSkipped('CIVICRM_MAIL_SMARTY is enabled on this site.');
    }

    [$result] = $this->generateWithSmartyLogic(new CRM_Certificate_Service_CertificateGenerator());

    $this->assertStringContainsString('{if 1 > 2}Hidden{/if}Shown', $result['html']);
  }

  public function testGenerateCertificateWillEvaluateSmartyWhenMailSmartyIsEnabled() {
    [$result, $contact] = $this->generateWithSmartyLogic($this->getSmartyEnabledGenerator());

    $this->assertStringNotContainsString('Hidden', $result['html']);
    $this->assertStringContainsString('Shown', $result['html']);
    $this->assertStringContainsString($contact['display_name'], $result['html']);
  }

  public function testGenerateCertificateWillThrowReadableErrorForInvalidSmarty() {
    $this->expectException(CRM_Core_Exception::class);
    $this->expectExceptionMessage('The certificate could not be generated.');

    $this->generateWithSmartyLogic($this->getSmartyEnabledGenerator(), ' {if 1 > 2}Unclosed');
  }

  private function getSmartyEnabledGenerator() {
    return new class() extends CRM_Certificate_Service_CertificateGenerator {

      protected function isSmartyEnabled(): bool {
        return TRUE;
      }

    };
  }

  /**
   * Generates a case certificate whose template contains Smarty logic.
   *
   * @param CRM_Certificate_Service_CertificateGenerator $generatorService
   * @param string $smarty
   *   Smarty to append to the template.
   *
   * @return array
   *   The generated content and the case contact.
   */
  private function generateWithSmartyLogic(CRM_Certificate_Service_CertificateGenerator $generatorService, $smarty = ' {if 1 > 2}Hidden{/if}Shown') {
    $content = $this->getMsgContent();
    $content['msg_html'] = $content['msg_html'] . $smarty;
    $template = CRM_Certificate_Test_Fabricator_MessageTemplate::fabricate($content);
    $case = $this->createCase();
    $contact = array_shift($case['contacts']);

    $result = $generatorService->generate($template['id'], $contact['contact_id'], $case['id']);

    return [$result, $contact];
  }

  private function getMsgContent() {
    return [
      'msg_html' => 'Hello {contact.display_name} Subject is {certificate_case.subject}',
      'msg_text' => __FUNCTION__,
      'msg_subject' => __FUNCTION__,
    ];
  }

}
