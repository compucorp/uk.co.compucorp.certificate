<?php

/**
 * Test events entity class
 *
 * @group headless
 */
class CRM_Certificate_Entity_EventTest extends BaseHeadlessTest {

  /**
   * Test the appropraite types are returned
   *  i.e. only active types are returned.
   */
  public function testGetTypesReturnsActiveOnes() {
    $inactiveType = CRM_Certificate_Test_Fabricator_Event::fabricate(['is_active' => 0])['id'];
    $activeType = CRM_Certificate_Test_Fabricator_Event::fabricate(['is_active' => 1])['id'];

    $eventEntity = new CRM_Certificate_Entity_Event();
    $types = $eventEntity->getTypes();

    $this->assertTrue(is_array($types));
    $this->assertTrue(!empty(array_diff([$inactiveType], $types)));
    $this->assertTrue(empty(array_diff([$activeType], $types)));
  }

  /**
   * Test the appropraite statuses are returned
   *  i.e. only active statuses are returned.
   */
  public function testGetStatusesReturnsActiveOnes() {
    $inactiveStatus = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate(['is_active' => 0])['id'];
    $activeStatus = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate(['is_active' => 1])['id'];

    $caseEntity = new CRM_Certificate_Entity_Event();
    $statuses = $caseEntity->getStatuses();

    $this->assertTrue(is_array($statuses));
    $this->assertTrue(!empty(array_diff([$inactiveStatus], $statuses)));
    $this->assertTrue(empty(array_diff([$activeStatus], $statuses)));
  }

}
