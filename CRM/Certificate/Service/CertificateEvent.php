<?php

use CRM_Certificate_BAO_CompuCertificateEventAttribute as CertificateEventAttribute;

class CRM_Certificate_Service_CertificateEvent extends CRM_Certificate_Service_Certificate {

  /**
   * {@inheritDoc}
  */
  protected function addOptionsCondition(&$query, $values) {
    $query->join('cert_event_attr', 'LEFT JOIN `' . CertificateEventAttribute::getTableName() . '` cert_event_attr ON (cert_event_attr.certificate_id = ccc.id)');

    $linkedToCondition = $this->overlapLinkedToCondition($values['linked_to']);
    $participantTypeCondition = $this->participantTypeCondition($values['participant_type_id']);
    $statusesCondition = $this->statusesOverlapCondition($values['statuses']);
    $eventTypeCondition = $this->eventTypesCondition($values['event_type_ids'] ?? []);

    // This is to avoid event entity from having multiple certificate configuration.
    $query = $query->where(implode(' AND ', [
      $linkedToCondition,
      $participantTypeCondition,
      $statusesCondition,
      $eventTypeCondition,
    ]));
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
    $eventTypeIds = CRM_Certificate_BAO_CompuCertificateEventAttribute::sanitizeEventTypeIds($eventTypeIds);

    if (empty($eventTypeIds)) {
      // Only block when existing configuration also has no event type filter.
      return '(cert_event_attr.event_type_ids IS NULL OR cert_event_attr.event_type_ids = "")';
    }

    $normalizedIds = implode(',', $eventTypeIds);

    return sprintf('cert_event_attr.event_type_ids = "%s"', $normalizedIds);
  }

  /**
   * Build participant type overlap condition.
   *
   * @param array|int|null $participantTypeIds
   *
   * @return string
   */
  private function participantTypeCondition($participantTypeIds) {
    if (empty($participantTypeIds)) {
      return '1';
    }

    $attrValues = sprintf('(%s)', implode(',', (array) $participantTypeIds));

    return "(cert_event_attr.participant_type_id IS NULL OR cert_event_attr.participant_type_id IN $attrValues)";
  }

  /**
   * Build linked to overlap condition.
   *
   * @param array|int|null $linkedTo
   *
   * @return string
   */
  private function overlapLinkedToCondition($linkedTo) {
    if (empty($linkedTo)) {
      return '1';
    }

    $entityTypes = sprintf('(%s)', implode(',', (array) $linkedTo));

    return "(cet.entity_type_id IS NULL OR cet.entity_type_id in $entityTypes)";
  }

  /**
   * Build statuses overlap condition.
   *
   * @param array|int|null $statuses
   *
   * @return string
   */
  private function statusesOverlapCondition($statuses) {
    if (empty($statuses)) {
      return '1';
    }

    $statuses = sprintf('(%s)', implode(',', (array) $statuses));

    return "(cs.status_id IS NULL OR cs.status_id in $statuses)";
  }

}
