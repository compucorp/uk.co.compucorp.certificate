<?php

use Civi\Test\Invasive;

/**
 * Test case token fields are resolved
 *
 * @group headless
 */
class CRM_Certificate_Token_CaseTest extends BaseHeadlessTest {

  use CRM_Certificate_Test_Helper_Case;

  public function testCanResolveCaseTokenFields() {
    $caseTokenSubscriber = new CRM_Certificate_Token_Case([]);
    $caseFields = array_keys($caseTokenSubscriber::caseFields);
    $resolvedFileds = [];

    $case = $this->createCase();

    $tokenValueEventMock = $this->createMock(\Civi\Token\Event\TokenValueEvent::class);
    $tokenProcessorMock = $this->createMock(\Civi\Token\TokenProcessor::class);

    $tokenValueEventMock->method('getTokenProcessor')->willReturn($tokenProcessorMock);
    $tokenProcessorMock->method('getContextValues')->willReturn([1]);

    Invasive::call([$caseTokenSubscriber, 'resolveFields'], [$tokenValueEventMock, $case, &$resolvedFileds]);

    array_walk($caseFields, function ($key) use ($resolvedFileds) {
      $this->assertArrayHasKey($key, $resolvedFileds);
    });
  }

  public function testCanResolveCaseCustomFields() {
    $resolvedFields = [];
    $caseTokenSubscriber = new CRM_Certificate_Token_Case([]);
    $customField = $this->createCaseCustomField();
    $activeToken = 'custom_' . $customField['id'];
    $activeTokenValue = 'test';
    $case = $this->createCase([$activeToken => $activeTokenValue]);

    $caseTokenSubscriber->activeTokens = ['custom_' . $customField['id']];
    Invasive::call([$caseTokenSubscriber, 'resolveCustomFields'], [$case, &$resolvedFields]);

    $this->assertArrayHasKey($activeToken, $resolvedFields);
    $this->assertEquals($resolvedFields[$activeToken], $activeTokenValue);
  }

  public function testCanPrefetchCaseTokenValues() {
    $caseTokenSubscriber = new CRM_Certificate_Token_Case([]);
    $customField = $this->createCaseCustomField();
    $activeToken = 'custom_' . $customField['id'];
    $activeTokenValue = 'test';
    $case = $this->createCase([$activeToken => $activeTokenValue]);
    $caseTokenSubscriber->activeTokens = ['custom_' . $customField['id']];

    $tokenValueEventMock = $this->createMock(\Civi\Token\Event\TokenValueEvent::class);
    $tokenProcessorMock = $this->createMock(\Civi\Token\TokenProcessor::class);

    $caseFields = array_merge(array_keys($caseTokenSubscriber::caseFields), [$activeToken]);

    $tokenValueEventMock->method('getTokenProcessor')->willReturn($tokenProcessorMock);
    $tokenProcessorMock->method('getContextValues')->will($this->onConsecutiveCalls(
      [$case['id']],
      [array_shift($case['contact_id'])]
    ));

    $prefetchedTokens = $caseTokenSubscriber->prefetch($tokenValueEventMock);

    $this->assertTrue(is_array($prefetchedTokens));
    array_walk($caseFields, function ($key) use ($prefetchedTokens) {
      $this->assertArrayHasKey($key, $prefetchedTokens);
    });
  }

  private function createCaseCustomField($params = []) {
    return CRM_Certificate_Test_Fabricator_CustomField::fabricate($params);
  }

}
