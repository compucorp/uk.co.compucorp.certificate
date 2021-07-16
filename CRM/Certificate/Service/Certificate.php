<?php

class CRM_Certificate_Service_Certificate {

  /**
   * Stores a certificate configuration
   *  
   * @param array $values
   *    Configuration data
   * 
   * @return array
   *    New Certificate configuration values
   */
  public function store($values) {
    $result = NULL;

    if ($this->configurationExist($values)) {
      throw new CRM_Certificate_Exception_ConfigurationExistException();
    }

    CRM_Core_Transaction::create()->run(function ($tx) use (&$result, $values) {
      if (!empty($values['id'])) {
        $params['id'] = $values['id'];
      }
      $params['name'] = $values['name'];
      $params['entity'] = $values['type'];
      $params['template_id'] = $values['message_template_id'];
      $statuses = (array)$values['statuses'];
      $entityTypes = (array)$values['linked_to'];

      $result['certificate'] = CRM_Certificate_BAO_CompuCertificate::create($params);
      $result['statuses'] = CRM_Certificate_BAO_CompuCertificateStatus::assignCertificateEntityStatuses($result['certificate'], $statuses);
      $result['entityTypes'] = CRM_Certificate_BAO_CompuCertificateEntityType::assignCertificateEntityTypes($result['certificate'], $entityTypes);
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
    $entityTypes = sprintf('(%s)', implode(',', (array)$values['linked_to']));
    $statuses = sprintf('(%s)', implode(',', (array)$values['statuses']));

    $query = CRM_Utils_SQL_Select::from(CRM_Certificate_DAO_CompuCertificate::$_tableName . ' ccc')
      ->select('ccc.id')
      ->join('cet', 'INNER JOIN `' . CRM_Certificate_DAO_CompuCertificateEntityType::$_tableName . '` cet ON (cet.certificate_id = ccc.id)')
      ->join('cs', 'INNER JOIN `' . CRM_Certificate_DAO_CompuCertificateStatus::$_tableName . '` cs ON (cs.certificate_id = ccc.id)')
      ->where('ccc.entity = @entity', ['entity' => $values['type']])
      ->where("cet.entity_type_id in $entityTypes AND cs.status_id in $statuses");

    if (!empty($values['id'])) {
      $query = $query->where('ccc.id <> ' . $values['id']);
    }

    $certificates = $query->execute()->fetchAll();

    return !empty($certificates);
  }
}
