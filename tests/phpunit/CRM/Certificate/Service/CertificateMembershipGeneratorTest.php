<?php


/**
 * Test service class for generating membership certificate html content
 *
 * @group headless
 */
class CRM_Certificate_Service_MembershipCertificateGeneratorTest extends BaseHeadlessTest {

  use CRM_Certificate_Test_Helper_Membership;

  public function testGenerateCertificateWillResolveMembershipTokens() {
    $content = $this->getMsgContent();
    $template = CRM_Certificate_Test_Fabricator_MessageTemplate::fabricate($content);
    $membership = $this->createMembership();
    $contact = $membership["contact"];

    $generatorService = new CRM_Certificate_Service_CertificateGenerator();
    $result = $generatorService->generate($template['id'], $contact['id'], $membership['id']);

    $this->assertContains($contact['display_name'], $result['html']);
    $this->assertContains($membership['membership_name'], $result['html']);
  }

  private function getMsgContent($extra = "") {
    return [
      'msg_html' => "Hello {contact.display_name} Subject is {certificate_membership.membership_name} {$extra}",
      'msg_text' => __FUNCTION__,
      'msg_subject' => __FUNCTION__,
    ];
  }

}
