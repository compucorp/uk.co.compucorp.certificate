<?php

use Civi\Api4\Country;
use CRM_Certificate_Test_Fabricator_Contact as ContactFabricator;

/**
 * Test case token fields are resolved
 *
 * @group headless
 */
class CRM_Certificate_Token_ContactTest extends BaseHeadlessTest {

  use CRM_Certificate_Test_Helper_Case;

  public function testCanResolveContactEmployerAddressTokenFields() {
    $contactTokenSubscriber = new CRM_Certificate_Token_Contact([]);

    $employer = ContactFabricator::fabricateWithAddress();
    $contact = ContactFabricator::fabricate(['employer_id' => $employer['id']]);

    $tokenValueEventMock = $this->createMock(\Civi\Token\Event\TokenValueEvent::class);
    $tokenProcessorMock = $this->createMock(\Civi\Token\TokenProcessor::class);

    $tokenValueEventMock->method('getTokenProcessor')->willReturn($tokenProcessorMock);
    $tokenProcessorMock->method('getContextValues')->willReturn([$contact['id']]);

    $country = Country::get(FALSE)->addWhere('id', '=', $employer['address']['country_id'])->execute()->first();
    $res = $contactTokenSubscriber->prefetch($tokenValueEventMock);

    $this->assertArrayHasKey("employer_inline_address", $res);
    $address = explode(", ", $res["employer_inline_address"]);

    // Ensure the order is as expected.
    $this->assertEquals($address[0], $employer["address"]["street_address"]);
    $this->assertEquals($address[1], $employer["address"]["supplemental_address_1"]);
    $this->assertEquals($address[2], $employer["address"]["city"]);
    $this->assertEquals($address[3], $country["name"]);
  }

}
