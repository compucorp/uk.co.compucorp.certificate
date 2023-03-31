<?php


/**
 * Test certificate token fields are resolved
 *
 * @group headless
 */
class CRM_Certificate_Token_CertificateTest extends BaseHeadlessTest {

  public function testCanResolveCertificateTokenFields() {
    $certificateTokenSubscriber = new CRM_Certificate_Token_Certificate([]);
    $certificateFields = array_keys($certificateTokenSubscriber::certificateFields);

    $eventId = CRM_Certificate_Test_Fabricator_Event::fabricate(['is_active' => 1])['id'];
    $statusId = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate(['is_active' => 1])['id'];
    $values = [
      'type' => CRM_Certificate_Enum_CertificateType::EVENTS,
      'linked_to' => [$eventId],
      'statuses' => [$statusId],
      'participant_type_id' => 1,
    ];
    $result = $this->createCertificate($values);

    $tokenValueEventMock = $this->createMock(\Civi\Token\Event\TokenValueEvent::class);
    $tokenProcessorMock = $this->createMock(\Civi\Token\TokenProcessor::class);

    $tokenValueEventMock->method('getTokenProcessor')->willReturn($tokenProcessorMock);
    $tokenProcessorMock->method('getContextValues')->willReturn([$result['certificate']->id]);

    $prefetchedTokens = $certificateTokenSubscriber->prefetch($tokenValueEventMock);

    $this->assertTrue(is_array($prefetchedTokens));
    array_walk($certificateFields, function ($key) use ($prefetchedTokens) {
      $this->assertArrayHasKey($key, $prefetchedTokens);
      $this->assertNotEmpty($prefetchedTokens[$key]);
    });
  }

  private function createCertificate($values = []) {
    return CRM_Certificate_Test_Fabricator_CompuCertificate::fabricate(CRM_Certificate_Enum_CertificateType::EVENTS, $values);
  }

}
