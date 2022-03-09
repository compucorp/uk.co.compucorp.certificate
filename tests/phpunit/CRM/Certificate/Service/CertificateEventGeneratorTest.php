<?php


/**
 * Test service class for generating event certificate html content
 *
 * @group headless
 */
class CRM_Certificate_Service_EventCertificateGeneratorTest extends BaseHeadlessTest {

  use CRM_Certificate_Test_Helper_Event;

  public function testGenerateCertificateWillResolveEventTokens() {
    $content = $this->getMsgContent();
    $template = CRM_Certificate_Test_Fabricator_MessageTemplate::fabricate($content);
    $participant = $this->createParticipant();
    $event = $participant["event"];
    $contact = $participant["contact"];

    $generatorService = new CRM_Certificate_Service_CertificateGenerator();
    $result = $generatorService->generate($template['id'], $contact['id'], $participant['id']);

    $this->assertContains($contact['display_name'], $result['html']);
    $this->assertContains($event['title'], $result['html']);
  }

  public function testGenerateCertificateWillResolveParticipantTokens() {
    $content = $this->getMsgContent(
      "Your role is {certificate_participant.participant_role},
       source {certificate_participant.participant_source}"
    );
    $template = CRM_Certificate_Test_Fabricator_MessageTemplate::fabricate($content);
    $participantSource = md5(mt_rand());
    $participant = $this->createParticipant(['participant_source' => $participantSource]);
    $contact = $participant["contact"];

    $generatorService = new CRM_Certificate_Service_CertificateGenerator();
    $result = $generatorService->generate($template['id'], $contact['id'], $participant['id']);

    $this->assertContains($participant['participant_role'], $result['html']);
    $this->assertContains($participant['participant_source'], $result['html']);
  }

  public function testGenerateCertificateWillResolveEventTokenWithEmptySummaryField() {
    $content = $this->getMsgContent(
      "Summary: {certificate_event.summary} \n
      Start date: {certificate_event.start_date}"
    );
    $template = CRM_Certificate_Test_Fabricator_MessageTemplate::fabricate($content);
    $participant = $this->createParticipant();
    $event = $participant["event"];

    // Empty event summary and description.
    $result = civicrm_api3("Event", "create", array_merge(
      $event,
      [
        "summary" => "",
        "description" => "",
      ]
    ));

    $contact = $participant["contact"];

    $generatorService = new CRM_Certificate_Service_CertificateGenerator();
    $result = $generatorService->generate($template["id"], $contact["id"], $participant["id"]);

    $this->assertContains($contact["display_name"], $result["html"]);
    $this->assertContains($event["title"], $result["html"]);
    $this->assertContains(CRM_Utils_Date::customFormat($event["start_date"]), $result["html"]);
  }

  private function getMsgContent($extra = "") {
    return [
      'msg_html' => "Hello {contact.display_name} Subject is {certificate_event.title} {$extra}",
      'msg_text' => __FUNCTION__,
      'msg_subject' => __FUNCTION__,
    ];
  }

}
