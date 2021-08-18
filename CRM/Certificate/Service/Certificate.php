<?php

class CRM_Certificate_Service_Certificate {

  /**
   * Stores a certificate configuration
   *
   * @param array $values
   *    Configuration data
   *
   * @return array
   *   New Certificate configuration values
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
      $statuses = (array) $values['statuses'];
      $entityTypes = (array) $values['linked_to'];

      $result['certificate'] = CRM_Certificate_BAO_CompuCertificate::create($params);

      if (!empty($statuses)) {
        $result['statuses'] = CRM_Certificate_BAO_CompuCertificateStatus::assignCertificateEntityStatuses($result['certificate'], $statuses);
      }
      if (!empty($entityTypes)) {
        $result['entityTypes'] = CRM_Certificate_BAO_CompuCertificateEntityType::assignCertificateEntityTypes($result['certificate'], $entityTypes);
      }
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
   *   true config exist,
   *   false config doesnt exist
   */
  public function configurationExist($values) {
    $optionsCondition = [];

    $query = CRM_Utils_SQL_Select::from(CRM_Certificate_DAO_CompuCertificate::$_tableName . ' ccc')
      ->select('ccc.id')
      ->join('cet', 'LEFT JOIN `' . CRM_Certificate_DAO_CompuCertificateEntityType::$_tableName . '` cet ON (cet.certificate_id = ccc.id)')
      ->join('cs', 'LEFT JOIN `' . CRM_Certificate_DAO_CompuCertificateStatus::$_tableName . '` cs ON (cs.certificate_id = ccc.id)')
      ->where('ccc.entity = @entity', ['entity' => $values['type']]);

    $this->linkedToCondition($optionsCondition, $values['linked_to']);
    $this->statusesCondition($optionsCondition, $values['statuses']);

    if (!empty($optionsCondition)) {
      $query = $query->where(implode(' OR ', $optionsCondition));
    }

    if (!empty($values['id'])) {
      $query = $query->where('ccc.id <> ' . $values['id']);
    }

    $certificates = $query->execute()->fetchAll();

    return !empty($certificates);
  }

  /**
   * Appends sql query condition for linked_to,
   * only if the linked_to array contains values.
   *
   * @param array &$optionsCondition
   *  The array to append sql query to.
   * @param array $linkedTo
   *  The array containing ids of an entity type
   *
   */
  private function linkedToCondition(&$optionsCondition, $linkedTo) {
    if (empty($linkedTo)) {
      $optionsCondition[] = "cet.entity_type_id IS NULL";
      return;
    }

    $entityTypes = sprintf('(%s)', implode(',', (array) $linkedTo));
    $optionsCondition[] = "cet.entity_type_id in $entityTypes";
  }

  /**
   * Appends sql query condition for statuses,
   * only if the statuses array contains values.
   *
   * @param array &$optionsCondition
   *  The array to append sql query to.
   * @param array $statuses
   *  The array containing ids of statuses
   *
   */
  private function statusesCondition(&$optionsCondition, $statuses) {
    if (empty($statuses)) {
      $optionsCondition[] = "cs.status_id IS NULL";
      return;
    }

    $statuses = sprintf('(%s)', implode(',', (array) $statuses));
    $optionsCondition[] = "cs.status_id in $statuses";
  }

}
