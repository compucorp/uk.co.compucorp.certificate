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

      $result['statuses'] = CRM_Certificate_BAO_CompuCertificateStatus::assignCertificateEntityStatuses($result['certificate'], $statuses);
      $result['entityTypes'] = CRM_Certificate_BAO_CompuCertificateEntityType::assignCertificateEntityTypes($result['certificate'], $entityTypes);

      $this->storeExtraValues($result, $values);
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
    $query = CRM_Utils_SQL_Select::from(CRM_Certificate_DAO_CompuCertificate::getTableName() . ' ccc')
      ->select('ccc.id')
      ->join('cet', 'LEFT JOIN `' . CRM_Certificate_DAO_CompuCertificateEntityType::getTableName() . '` cet ON (cet.certificate_id = ccc.id)')
      ->join('cs', 'LEFT JOIN `' . CRM_Certificate_DAO_CompuCertificateStatus::getTableName() . '` cs ON (cs.certificate_id = ccc.id)')
      ->where('ccc.entity = @entity', ['entity' => $values['type']]);

    $this->addOptionsCondition($query, $values);

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
   * @param array $linkedTo
   *  The array containing ids of an entity type
   *
   */
  protected function linkedToCondition($linkedTo) {
    if (empty($linkedTo)) {
      return "cet.entity_type_id IS NULL";
    }

    $entityTypes = sprintf('(%s)', implode(',', (array) $linkedTo));
    return "(cet.entity_type_id IS NULL OR cet.entity_type_id in $entityTypes)";
  }

  /**
   * Appends sql query condition for statuses,
   * only if the statuses array contains values.
   *
   * @param array $statuses
   *  The array containing ids of statuses
   *
   */
  protected function statusesCondition($statuses) {
    if (empty($statuses)) {
      return "cs.status_id IS NULL";
    }

    $statuses = sprintf('(%s)', implode(',', (array) $statuses));
    return "(cs.status_id IS NULL OR cs.status_id in $statuses)";
  }

  /**
   * Checks that a certificate name already exists or not.
   *
   * @param string $name
   *  The certificate name to check.
   * @param array $exclude
   *  Array of certificate ids to exclude from the check.
   *
   * @return bool
   *   true if the certificate name exists,
   *   false otherwise.
   */
  public function certificateNameExist($name, $exclude = []) {
    $query = CRM_Utils_SQL_Select::from(CRM_Certificate_DAO_CompuCertificate::getTableName() . ' ccc')
      ->where('ccc.name = @name', ['name' => $name]);

    if (!empty($exclude)) {
      $excludedIds = sprintf('(%s)', implode(',', (array) $exclude));
      $query->where('ccc.id not in ' . $excludedIds);
    }

    $certificateWithName = $query->execute()->fetchAll();

    return !empty($certificateWithName);
  }

  /**
   * Adds required options condition.
   * Entity extending this class can override this,
   * if it needs to add an extra condition.
   *
   * @param CRM_Utils_SQL_Select $query
   *  The query object
   * @param array $values
   *  An Array of certificate values.
   *
   */
  protected function addOptionsCondition(&$query, $values) {
    if (empty($values['linked_to']) && empty($values['statuses'])) {
      return;
    }

    $optionsCondition[] = $this->linkedToCondition($values['linked_to']);
    $optionsCondition[] = $this->statusesCondition($values['statuses']);

    // This is to avoid an entity having multiple certificate configuration,
    // i.e. in a case where a configuration that has linked_to 'all' and statuses for a specific status,
    // and the user attempts to create another configuration with linked_to for a specific type and statuses for 'all',
    // then a ConfigurationExistException would be thrown.
    $conjuction = empty($values['linked_to']) || empty($values['statuses']) ? ' OR ' : ' AND ';
    $query = $query->where(implode($conjuction, $optionsCondition));
  }

  /**
   * Stores extra values that are peculiar to an entity.
   *
   * @param array &$result
   *  The array to append result to.
   * @param array $values
   *  An Array of certificate values.
   */
  protected function storeExtraValues(&$result, $values) {
  }

}
