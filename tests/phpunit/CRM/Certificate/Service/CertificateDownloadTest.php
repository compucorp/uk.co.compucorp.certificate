<?php

use Civi\Test\Invasive;

/**
 * Test service class for downloading cretificate
 * 
 * @group headless
 */
class CRM_Certificate_Service_CertificateDownloadTest extends BaseHeadlessTest {

  use CRM_Certificate_Test_Helper_Case;

  public function testLoadMessageTemplate() {
    $template = CRM_Certificate_Test_Fabricator_MessageTemplate::fabricate();

    $downloadService = new CRM_Certificate_Service_CertificateDownload();
    $result = Invasive::call([$downloadService, 'loadTemplate'], [$template['id']]);

    $this->assertTrue(is_array($result));
    $this->assertEquals($template['msg_subject'], $result['subject']);
    $this->assertEquals($template['msg_text'], $result['text']);
    $this->assertEquals($template['msg_html'], $result['html']);
  }

  public function testRenderMessageTemplate() {
    $case = $this->createCase();
    $caseId = $case['id'];
    $contact = array_shift($case['contacts']);
    $contactId = $contact['contact_id'];
    $content = $this->getMsgContent();

    $downloadService = new CRM_Certificate_Service_CertificateDownload();
    $result = Invasive::call([$downloadService, 'renderMessageTemplate'], [$content, $contactId, $caseId]);

    $this->assertArrayHasKey("html", $result);
    $this->assertArrayHasKey("text", $result);
    $this->assertArrayHasKey("subject", $result);

    //Ensure that tokens in message template are resolved
    $this->assertContains($contact['display_name'], $result['html']);
    $this->assertContains($case['subject'], $result['html']);
  }

  private function getMsgContent() {
    return [
      'html' => 'Hello {contact.display_name} Subject is {certificate_case.subject}',
      'text' => __FUNCTION__,
      'subject' => __FUNCTION__
    ];
  }
}
