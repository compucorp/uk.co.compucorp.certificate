<?php

use CRM_Certificate_Enum_CertificateType as CertificateType;
use CRM_Certificate_Test_Fabricator_CompuCertificate as CompuCertificateFabricator;

trait CRM_Certificate_Test_Helper_Membership {

  private function createMembership($params = []) {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $membershipStatus = CRM_Certificate_Test_Fabricator_MembershipStatus::fabricate();
    $membershipType = CRM_Certificate_Test_Fabricator_MembershipType::fabricate(['is_active' => 1]);

    $params = array_merge([
      'contact_id' => $contact['id'],
      'membership_type_id' => $membershipType["id"],
      'status_id'  => $membershipStatus["id"],
    ], $params);

    $membership = CRM_Certificate_Test_Fabricator_Membership::fabricate($params);

    $membership = civicrm_api3('Membership', 'getsingle', [
      'id' => $membership['id'],
    ]);

    $membership["contact"] = $contact;

    return $membership;
  }

  private function createMembershipCertificate($params = []) {
    $membershipType = CRM_Certificate_Test_Fabricator_MembershipType::fabricate(['is_active' => 1]);
    $membershipStatus = CRM_Certificate_Test_Fabricator_MembershipStatus::fabricate(['is_active' => 1]);

    $values = array_merge([
      'type' => CertificateType::MEMBERSHIPS,
      'linked_to' => $membershipType['id'],
      'statuses' => $membershipStatus['id'],
      'start_date' => date('Y-m-d'),
      'relationship_types' => $params['relationship_types'] ?? [],
      'end_date' => date('Y-m-d', strtotime(date('Y-m-d') . " 10 days")),
      'min_valid_from_date' => date("Y-m-d"),
      'max_valid_through_date' => date("Y-m-d", strtotime(date("Y-m-d") . " + 30 days")),
    ], $params);

    return CompuCertificateFabricator::fabricate(CertificateType::MEMBERSHIPS, $values);
  }

}
