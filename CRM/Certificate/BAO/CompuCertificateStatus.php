<?php
use CRM_Certificate_ExtensionUtil as E;

class CRM_Certificate_BAO_CompuCertificateStatus extends CRM_Certificate_DAO_CompuCertificateStatus {

  /**
   * Create a new CompuCertificateStatus based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Certificate_DAO_CompuCertificateStatus|NULL
   * 
   */
  public static function create($params) {
    $className = 'CRM_Certificate_DAO_CompuCertificateStatus';
    $entityName = 'CompuCertificateStatus';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Creates mulitple CompuCertificateStatus and attach them to
   * a certificate
   * 
   * @param int $CompuCertificateId
   *    Id of the certificate instance
   * 
   * @param array $statuses
   *    array of status ids 
   */
  public static function createStatuses($CompuCertificateId, $statuses) {
    $result = array();

    if (!is_array($statuses)) {
      throw new API_Exception('Statuses paramter must be an array');
    }

    //remove previously synced statuses if any
    $statusBAO = new CRM_Certificate_BAO_CompuCertificateStatus();
    $statusBAO->whereAdd("certificate_id = $CompuCertificateId");
    $statusBAO->delete(true);

    foreach ($statuses as $statusId) {
      $statusDAO = self::create([
        'certificate_id' => $CompuCertificateId,
        'status_id' => $statusId
      ]);

      $result[] = $statusDAO->toArray();
    }
    return $result;
  }

}
