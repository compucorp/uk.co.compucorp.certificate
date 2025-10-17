<?php

use CRM_Certificate_BAO_CompuCertificateEventAttribute as CertificateEventAttribute;

class CRM_Certificate_Service_CertificateEvent extends CRM_Certificate_Service_Certificate {

  /**
   * {@inheritDoc}
   */
  protected function addOptionsCondition(&$query, $values) {
    $query->join('cert_event_attr', 'LEFT JOIN `' . CertificateEventAttribute::getTableName() . '` cert_event_attr ON (cert_event_attr.certificate_id = ccc.id)');

    $participantTypeCondition = "cert_event_attr.participant_type_id IS NULL";

    if (!empty($values['participant_type_id'])) {
      $attrValues = sprintf('(%s)', implode(',', (array) $values['participant_type_id']));
      $participantTypeCondition = "($participantTypeCondition OR cert_event_attr.participant_type_id IN $attrValues)";
    }

    $linkedToCondition = $this->linkedToCondition($values['linked_to']);
    $statusesCondition = $this->statusesCondition($values['statuses']);
    $eventTypesCondition = $this->eventTypesCondition($values['event_type_ids'] ?? []);

    $query = $query->where($linkedToCondition);

    if (empty($values['participant_type_id']) && empty($values['statuses']) && empty($values['event_type_ids'])) {
      return;
    }

    // This is to avoid event entity from having multiple certificate configuration.
    $filters = [
      !empty($values['statuses']),
      !empty($values['participant_type_id']),
      !empty($values['event_type_ids']),
    ];
    $conjuction = array_sum($filters) > 1 ? ' AND ' : ' OR ';
    $query = $query->where(implode($conjuction, [$statusesCondition, $participantTypeCondition, $eventTypesCondition]));
  }

  /**
   * Builds the SQL condition for filtering by event types.
   *
   * @param array $eventTypeIds
   *
   * @return string
   */
  protected function eventTypesCondition($eventTypeIds) {
    $column = 'ccc.event_type_ids';
    $clauses = ["{$column} IS NULL", "{$column} = ''"];

    foreach (array_filter((array) $eventTypeIds) as $eventTypeId) {
      $eventTypeId = (int) $eventTypeId;
      if (!$eventTypeId) {
        continue;
      }
      $separator = CRM_Core_DAO::VALUE_SEPARATOR;
      $pattern = '%' . $separator . $eventTypeId . $separator . '%';
      $clauses[] = "{$column} LIKE " . CRM_Utils_Type::escape($pattern, 'String');
    }

    return '(' . implode(' OR ', $clauses) . ')';
  }

  /**
   * {@inheritDoc}
   */
  protected function storeExtraValues(&$result, $values) {
    $participantTypeId = $values['participant_type_id'];
    $certificateId = $result['certificate']->id;
    $result['eventAttribute'] = CertificateEventAttribute::assignCertificateEventAttribute($certificateId, $participantTypeId);
  }

}
