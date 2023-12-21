<?php

use Civi\Api4\Membership;

class CRM_Certificate_Service_CertificateMembership extends CRM_Certificate_Service_Certificate {

  public function getMembershipDates(int $certificateId, int $contactId): array {
    $startDate = NULL;
    $endDate = NULL;

    $query = CRM_Utils_SQL_Select::from(CRM_Certificate_DAO_CompuCertificate::getTableName() . ' ccc')
      ->select(['GROUP_CONCAT(DISTINCT cet.entity_type_id) as entityTypes', 'GROUP_CONCAT(DISTINCT cs.status_id) as statuses'])
      ->join('cet', 'LEFT JOIN `' . CRM_Certificate_DAO_CompuCertificateEntityType::getTableName() . '` cet ON (cet.certificate_id = ccc.id)')
      ->join('cs', 'LEFT JOIN `' . CRM_Certificate_DAO_CompuCertificateStatus::getTableName() . '` cs ON (cs.certificate_id = ccc.id)')
      ->where('ccc.id = @certificateId', ['certificateId' => $certificateId])
      ->groupBy('ccc.id');

    $certificates = $query->execute()->fetchAll();

    $query = Membership::get(FALSE)
      ->addSelect('start_date', 'end_date')
      ->addWhere('contact_id', '=', $contactId)
      ->addClause('OR', ['start_date', 'IS NULL'], ['start_date', '<=', 'now'])
      ->addClause('OR', ['end_date', 'IS NULL'], ['end_date', '>', 'now']);

    if (!empty($certificates[0]['entityTypes'])) {
      $query->addWhere('membership_type_id', 'IN', explode(',', $certificates[0]['entityTypes']));
    }
    if (!empty($certificates[0]['statuses'])) {
      $query->addWhere('status_id', 'IN', explode(',', $certificates[0]['statuses']));
    }

    $memberships = $query->execute()->getArrayCopy();

    foreach ($memberships as $membership) {
      $startDate = $startDate === NULL || strtotime($membership['start_date']) < strtotime($startDate) ?
        $membership['start_date'] : $startDate;
      $endDate = $endDate === NULL || strtotime($membership['end_date']) > strtotime($endDate) ?
        $membership['end_date'] : $endDate;
    }

    return ['startDate' => $startDate, 'endDate' => $endDate];
  }

}
