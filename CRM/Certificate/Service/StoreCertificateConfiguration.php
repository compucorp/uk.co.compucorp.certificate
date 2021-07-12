<?php

use Symfony\Component\VarDumper\VarDumper;

class CRM_Certificate_Service_StoreCertificateConfiguration {

  /**
   * @var array
   */
  public $values;

  /**
   * @param array $values
   *  Configuration values
   */
  public function __construct($values) {
    $this->values = $values;
  }

  /**
   * Create a new certificate configuration
   * 
   * @return array
   *  New Certificate configuration values
   */
  public function store() {
    $result = NULL;
    $values = $this->values;

    if ($this->configurationExist($values)) {
      throw new CRM_Certificate_Exception_ConfigurationExistException();
    }

    CRM_Core_Transaction::create()->run(function ($tx) use (&$result, $values) {
      $params['name'] = $values['certificate_name'];
      $params['entity'] = $values['certificate_type'];
      $params['template_id'] = $values['certificate_msg_template'];
      $statuses = explode(',', $values['certificate_status']);
      $entityTypes = explode(',', $values['certificate_linked_to']);

      $result['certificate'] = CRM_Certificate_BAO_CompuCertificate::create($params);
      $result['statuses'] = CRM_Certificate_BAO_CompuCertificateStatus::createStatuses($result['certificate']->id, $statuses);
      $result['entityTypes'] = CRM_Certificate_BAO_CompuCertificateEntityType::createEntityTypes($result['certificate']->id, $entityTypes);
    });

    return $result;
  }

  /**
   * Checks if a configuration exists that
   * satisfy the new configuration to be created,
   * this is to avoid having more than one entity certificate
   * configured for the same status or entity type
   * 
   * @return bool
   *  true config exist,
   *  false config doesnt exist
   */
  public function configurationExist($values) {
    $entityTypes = sprintf('(%s)', $values['certificate_linked_to']);
    $statuses = sprintf('(%s)', $values['certificate_status']);

    $certificates = CRM_Utils_SQL_Select::from(CRM_Certificate_DAO_CompuCertificate::$_tableName . ' ccc')
      ->select('ccc.id')
      ->join('cet', 'INNER JOIN `' . CRM_Certificate_DAO_CompuCertificateEntityType::$_tableName . '` cet ON (cet.certificate_id = ccc.id)')
      ->join('cs', 'INNER JOIN `' . CRM_Certificate_DAO_CompuCertificateStatus::$_tableName . '` cs ON (cs.certificate_id = ccc.id)')
      ->where('ccc.entity = @entity', ['entity' => $values['certificate_type']])
      ->where("cet.entity_type_id in $entityTypes AND cs.status_id in $statuses")
      ->execute()
      ->fetchAll();

    return !empty($certificates);
  }
}
