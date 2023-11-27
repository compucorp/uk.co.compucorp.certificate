<?php

use Civi\Api4\Relationship;
use Civi\Api4\Membership;

class CRM_Certificate_Service_CertificateAccessChecker {

  public int $contactId;

  public \CRM_Certificate_BAO_CompuCertificate $certificate;

  /**
   * Constructs a new instance of the class.
   *
   * @param int $contactId
   * @param \CRM_Certificate_BAO_CompuCertificate $certificate
   */
  public function __construct($contactId, $certificate) {
    $this->contactId = $contactId;
    $this->certificate = $certificate;
  }

  /**
   * @return bool
   */
  public function check() {
    return $this->checkMembershipDates() && ($this->hasViewPermission() || $this->hasViewPermissionByRelationship());
  }

  /**
   * Performs access level check to ensure user has access to contact certificate
   *
   * The user would be granted access to the certificate if any of the condition below is true
   * - if the contact id is the same as the current logged in user
   * - if a checksum is provided in the URL and it is valid for the contact id
   * - Lastly, the user is not logged in and no checksum is provided if the user has
   *   the express permission to view the contact
   *
   * - if all fails return false.
   *
   * @return int - contact id
   */
  private function hasViewPermission() {
    $userChecksum = CRM_Utils_Request::retrieve('cs', 'String');

    $isLoggedInUser = CRM_Core_Session::getLoggedInContactID() == $this->contactId;
    $hasViewPermission = CRM_Contact_BAO_Contact_Permission::allow($this->contactId, CRM_Core_Permission::VIEW);
    $checksumValid = FALSE;

    if ($userChecksum && $this->contactId) {
      $checksumValid = CRM_Contact_BAO_Contact_Utils::validChecksum($this->contactId, $userChecksum);
    }

    if ($checksumValid || $isLoggedInUser || $hasViewPermission) {
      return $this->contactId;
    }

    return FALSE;
  }

  /**
   * Checks user has an active relationship with the certificate owner.
   *
   * The 'relationship' is of the relationship_type defined on the certificate configuration.
   */
  private function hasViewPermissionByRelationship() {
    $allowedRelationshipTypeIds = $this->certificate->getRelationshipTypes('relationship_type_id');
    if (!empty($allowedRelationshipTypeIds) && !empty(CRM_Core_Session::getLoggedInContactID())) {
      $relationships = Relationship::get()
        ->addWhere('contact_id_a', '=', CRM_Core_Session::getLoggedInContactID())
        ->addWhere('contact_id_b', '=', $this->contactId)
        ->addWhere('relationship_type_id', 'IN', $allowedRelationshipTypeIds)
        ->setCurrent(TRUE)
        ->setCheckPermissions(FALSE)
        ->execute();

      if (count($relationships)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  private function checkMembershipDates(): bool {
    $membershipDates = $this->getMembershipDates();
    $membershipStartDate = $membershipDates['start_date'];
    $membershipEndDate = $membershipDates['end_date'];
    $certificateStartDate = $this->certificate->min_valid_from_date ?? $membershipEndDate;
    $certificateEndDate = $this->certificate->max_valid_through_date ?? $membershipStartDate;

    return !empty($membershipStartDate) && !empty($membershipEndDate) && strtotime($membershipStartDate) <= strtotime($certificateEndDate) &&
      strtotime($membershipEndDate) >= strtotime($certificateStartDate);
  }

  private function getMembershipDates(): array {
    $startDate = NULL;
    $endDate = NULL;

    $memberships = Membership::get(FALSE)
      ->addSelect('start_date', 'end_date')
      ->addWhere('contact_id', '=', $this->contactId)
      ->addWhere('status_id', '=', 2)
      ->execute()
      ->getArrayCopy();

    foreach ($memberships as $membership) {
      $startDate = $startDate === NULL || strtotime($membership['start_date']) < strtotime($startDate) ?
        $membership['start_date'] : $startDate;
      $endDate = $endDate === NULL || strtotime($membership['end_date']) > strtotime($endDate) ?
        $membership['end_date'] : $endDate;
    }

    return ['start_date' => $startDate, 'end_date' => $endDate];
  }

}
