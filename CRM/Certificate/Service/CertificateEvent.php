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

    $eventTypeCondition = $this->eventTypesCondition($values['event_type_ids'] ?? []);
    $linkedToCondition = $this->linkedToCondition($values['linked_to']);
    $statusesCondition = $this->statusesCondition($values['statuses']);

    $query = $query->where($linkedToCondition);

    if (empty($values['participant_type_id']) && empty($values['statuses']) && empty($values['event_type_ids'])) {
      return;
    }

    // This is to avoid event entity from having multiple certificate configuration.
    $nonEmptyConditions = array_filter([
      $values['participant_type_id'],
      $values['statuses'],
      $values['event_type_ids'] ?? [],
    ]);

    $conjuction = count($nonEmptyConditions) > 1 ? ' AND ' : ' OR ';
    $query = $query->where(implode($conjuction, [$statusesCondition, $participantTypeCondition, $eventTypeCondition]));
  }

  /**
   * {@inheritDoc}
   */
  protected function storeExtraValues(&$result, $values) {
    $participantTypeId = $values['participant_type_id'];
    $eventTypeIds = $values['event_type_ids'] ?? [];
    $certificateId = $result['certificate']->id;
    $result['eventAttribute'] = CertificateEventAttribute::assignCertificateEventAttribute($certificateId, $participantTypeId, $eventTypeIds);
  }

  /**
   * Build event type condition for duplicate configuration detection.
   *
   * @param array $eventTypeIds
   *
   * @return string
   */
  private function eventTypesCondition(array $eventTypeIds) {
    if (empty($eventTypeIds)) {
      // Only block when existing configuration also has no event type filter.
      return '(cert_event_attr.event_type_ids IS NULL OR cert_event_attr.event_type_ids = "")';
    }

    $eventTypeIds = array_values(array_unique(array_map('intval', $eventTypeIds)));
    sort($eventTypeIds);

    $expectedCount = count($eventTypeIds);
    $tokensCountExpr = 'LENGTH(cert_event_attr.event_type_ids) - LENGTH(REPLACE(cert_event_attr.event_type_ids, ",", "")) + 1';
    $allIdsPresentCondition = implode(' AND ', array_map(function ($eventTypeId) {
      return sprintf('FIND_IN_SET(%s, cert_event_attr.event_type_ids)', $eventTypeId);
    }, $eventTypeIds));

    return sprintf(
      '(%s IS NOT NULL AND %s AND %s = %d)',
      'cert_event_attr.event_type_ids',
      $allIdsPresentCondition,
      $tokensCountExpr,
      $expectedCount
    );
  }

}
