<?php

use Civi\Api4\Relationship;

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
    return $this->hasViewPermission() || $this->hasViewPermissionByRelationship();
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

}
