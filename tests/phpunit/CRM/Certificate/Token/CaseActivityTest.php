<?php

/**
 * Test case activity token fields are resolved
 * 
 * @group headless
 */
class CRM_Certificate_Token_CaseActivityTest extends BaseHeadlessTest {

  use CRM_Certificate_Test_Helper_Case;

  public function testCanGetFieldIdFromTokenNames() {
    $caseActivityTokenSubscriber = new CRM_Certificate_Token_CaseActivity([]);
    $firstCase = [1, 'test_test'];
    $secondCase = [11, 'pol'];
    $tokenNames = ["$firstCase[0]_$firstCase[1]", "$secondCase[0]_$secondCase[1]"];

    $fieldIds = $caseActivityTokenSubscriber->getFieldIdFromTokenNames($tokenNames);

    $this->assertTrue(is_array($fieldIds));
    $this->assertArrayHasKey($firstCase[0], $fieldIds);
    $this->assertArrayHasKey($secondCase[0], $fieldIds);
    $this->assertEquals($fieldIds[$firstCase[0]], $tokenNames[0]);
    $this->assertEquals($fieldIds[$secondCase[0]], $tokenNames[1]);
  }

  public function testCanPrefetchCaseActivityTokenValues() {
    $caseActivityTokenSubscriber = new CRM_Certificate_Token_CaseActivity([]);
    $case = $this->createCase();

    $tokenValueEventMock = $this->createMock(\Civi\Token\Event\TokenValueEvent::class);
    $tokenProcessorMock = $this->createMock(\Civi\Token\TokenProcessor::class);

    $tokenValueEventMock->method('getTokenProcessor')->willReturn($tokenProcessorMock);
    $tokenProcessorMock->method('getContextValues')->willReturn([$case['id']]);

    $caseActivityTokenSubscriber->activeTokens = array_keys($caseActivityTokenSubscriber::entityTokens());

    $prefetchedValues = $caseActivityTokenSubscriber->prefetch($tokenValueEventMock);

    $this->assertTrue(is_array($prefetchedValues));
  }
}
