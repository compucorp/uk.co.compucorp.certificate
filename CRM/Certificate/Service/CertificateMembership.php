<?php

use Civi\Api4\Membership;

class CRM_Certificate_Service_CertificateMembership extends CRM_Certificate_Service_Certificate {

  public function getMembershipDates(int $certificateId, int $contactId): array {
    $startDate = NULL;
    $endDate = NULL;
    $memberships = $this->getValidMembershipsForCertificate($certificateId, $contactId);

    foreach ($memberships as $membership) {
      $startDate = $startDate === NULL || strtotime($membership['start_date']) < strtotime($startDate) ?
        $membership['start_date'] : $startDate;
      $endDate = $endDate === NULL || strtotime($membership['end_date']) > strtotime($endDate) ?
        $membership['end_date'] : $endDate;
    }

    return ['startDate' => $startDate, 'endDate' => $endDate];
  }

  public function getMembershipRenewalDate(int $certificateId, int $contactId): string {
    $renewalDate = NULL;
    $memberships = $this->getValidMembershipsForCertificate($certificateId, $contactId);

    foreach ($memberships as $membership) {
      $renewalTimestamp = strtotime($membership['end_date'] . " -1 year 1 day");
      $membershipStartTimestamp = !empty($membership['start_date']) ? strtotime($membership['start_date']) : '';
      $membershipDate = $renewalTimestamp > $membershipStartTimestamp ? $renewalTimestamp : $membershipStartTimestamp;
      $renewalDate = $renewalDate === NULL || $membershipDate < $renewalDate ? $membershipDate : $renewalDate;
    }

    return $renewalDate ? CRM_Utils_Date::customFormat(date('Y-m-d', $renewalDate), '%e/%b/%Y') : '';
  }

  private function getValidMembershipsForCertificate(int $certificateId, int $contactId): array {
    $query = CRM_Utils_SQL_Select::from(CRM_Certificate_DAO_CompuCertificate::getTableName() . ' ccc')
      ->select(['GROUP_CONCAT(DISTINCT cet.entity_type_id) as entityTypes', 'GROUP_CONCAT(DISTINCT cs.status_id) as statuses'])
      ->join('cet', 'LEFT JOIN `' . CRM_Certificate_DAO_CompuCertificateEntityType::getTableName() . '` cet ON (cet.certificate_id = ccc.id)')
      ->join('cs', 'LEFT JOIN `' . CRM_Certificate_DAO_CompuCertificateStatus::getTableName() . '` cs ON (cs.certificate_id = ccc.id)')
      ->where('ccc.id = @certificateId', ['certificateId' => $certificateId])
      ->groupBy('ccc.id');

    $certificates = $query->execute()->fetchAll();

    $query = Membership::get(FALSE)
      ->addSelect('start_date', 'end_date')
      ->addWhere('contact_id', '=', $contactId);

    if (!empty($certificates[0]['entityTypes'])) {
      $query->addWhere('membership_type_id', 'IN', explode(',', $certificates[0]['entityTypes']));
    }
    if (!empty($certificates[0]['statuses'])) {
      $query->addWhere('status_id', 'IN', explode(',', $certificates[0]['statuses']));
    }

    return $query->execute()->getArrayCopy();
  }

}
