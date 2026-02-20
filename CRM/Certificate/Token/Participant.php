<?php

use Civi\Token\Event\TokenValueEvent;

/**
 * Class CRM_Certificate_Token_Participant.
 *
 * Generate "certificate_participant.*" tokens.
 *
 * This class defines the participant tokens (standard fields and custom fields)
 * that are supported by a certificate
 */
class CRM_Certificate_Token_Participant extends CRM_Certificate_Token_AbstractCertificateToken {

  const TOKEN = 'certificate participant';

  public function __construct($tokenNames = []) {
    $this->tokenNames = $tokenNames;
  }

  /**
   * {@inheritDoc}
   */
  public static function entityTokens() {
    $participantFields = CRM_Event_BAO_Participant::exportableFields();
    $tokens = [];

    // Filter out unused fields from token list.
    array_walk($participantFields, function ($v, $k) use (&$tokens) {
      if (!in_array($k, [
        'contact_id',
        'display_name',
        'event_id',
        'event_title',
        'event_start_date',
        'event_end_date',
        'default_role_id',
        'participant_id',
        'participant_fee_level',
        'participant_fee_amount',
        'participant_fee_currency',
        'event_type',
        'participant_status',
        'participant_role',
        'participant_register_date',
        'participant_source',
        'participant_note',
        'id',
      ])) {
        return;
      }
      $tokens[$k] = ts($v['title']);
    });

    $tokens = array_merge(CRM_Utils_Token::getCustomFieldTokens('Participant'), $tokens);

    return $tokens;
  }

  /**
   * To perform a bulk lookup before rendering tokens.
   *
   * @param \Civi\Token\Event\TokenValueEvent $e
   *
   * @return mixed
   */
  public function prefetch(TokenValueEvent $e) {
    $entityTypeId = $e->getTokenProcessor()->getContextValues('entityId');
    $contactId = $e->getTokenProcessor()->getContextValues('contactId');

    $resolvedTokens = [];

    try {
      if (is_array($entityTypeId)) {
        $entityTypeId = $entityTypeId[0];
        $contactId = $contactId[0];
        $result = civicrm_api3('Participant', 'getsingle', [
          'id' => $entityTypeId,
          'contact_id' => $contactId,
        ]);

        if (!empty($result['is_error'])) {
          return $resolvedTokens;
        }

        $this->resolveFields($result, $resolvedTokens);
      }
    }
    catch (Exception $e) {
      CRM_Core_Session::setStatus('Error resolving tokens');
    }

    return $resolvedTokens;
  }

  /**
   * Resolve the value of participant fields in the token event.
   *
   * @param array $participant
   * @param array &$resolvedTokens
   */
  private function resolveFields($participant, &$resolvedTokens) {
    // Keep a copy before array_walk so custom field option lookups receive
    // the unmodified value (array or VALUE_SEPARATOR string).
    $originalParticipant = $participant;

    // Convert date fields to human readable format (2022-12-01 12:12:00 -> 1st December 2022 12:12 PM).
    array_walk($participant, function(&$v, $k) {
      $dateFields = [
        "event_end_date",
        "event_start_date",
        "participant_register_date",
      ];

      if (in_array($k, $dateFields)) {
        $v = !empty($v) ? new \DateTime($v) : '';
      }

      if (is_array($v)) {
        // eg. role_id for participant can be an array.
        $v = implode(',', $v);
      }
    });

    foreach ($this->activeTokens as $value) {
      if ($fieldId = CRM_Core_BAO_CustomField::getKeyID($value)) {
        $originalValue = CRM_Utils_Array::value($value, $originalParticipant, '');
        $resolvedTokens[$value] = $this->getCustomFieldDisplayValue(
          $fieldId, $originalValue
        );
      }
      else {
        $resolvedTokens[$value] = CRM_Utils_Array::value($value, $participant, '');
      }
    }

  }

  /**
   * Get display labels for a custom field, one per line.
   *
   * @param int $fieldId
   *   Custom field ID.
   * @param mixed $value
   *   Raw value (array or VALUE_SEPARATOR-delimited string).
   *
   * @return string
   *   Labels joined by <br> for option fields; raw value otherwise.
   */
  private function getCustomFieldDisplayValue($fieldId, $value) {
    if (empty($value)) {
      return '';
    }

    $field = CRM_Core_BAO_CustomField::getField($fieldId);
    if (empty($field['option_group_id'])) {
      return is_array($value) ? implode('<br>', $value) : (string) $value;
    }

    if (!is_array($value)) {
      $value = CRM_Utils_Array::explodePadded($value);
    }

    $options = CRM_Core_OptionGroup::valuesByID($field['option_group_id']);
    $labels = array_filter(array_map(function ($v) use ($options) {
      return $options[trim($v)] ?? NULL;
    }, $value));

    return implode('<br>', $labels);
  }

}
