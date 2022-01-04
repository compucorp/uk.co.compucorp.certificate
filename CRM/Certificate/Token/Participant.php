<?php

use Civi\Token\Event\TokenValueEvent;

/**
 * Class CRM_Certificate_Token_Participant
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
   * @inheritDoc
   */
  public static function entityTokens() {
    $participantTokens = CRM_Core_SelectValues::participantTokens();

    // we clean up the array because they are keyed in the format {participant.field}
    $filtered_keys = preg_replace(['/(participant\.)/', '/(\W*)/'], '', array_keys($participantTokens));
    $tokens = array_combine($filtered_keys, array_values($participantTokens));

    return $tokens;
  }

  /**
   * To perform a bulk lookup before rendering tokens
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
          'event_id' => $entityTypeId,
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
    // Convert date fields to human readable format (2022-12-01 12:12:00 -> 1st December 2022 12:12 PM).
    array_walk($participant, function(&$v, $k) {
      $dateFields = [
        "event_end_date",
        "event_start_date",
        "participant_register_date",
      ];

      if (in_array($k, $dateFields)) {
        $v = CRM_Utils_Date::customFormat($v);
      }

      if (is_array($v)) {
        // eg. role_id for participant can be an array.
        $v = implode(',', $v);
      }
    });

    foreach ($this->activeTokens as $value) {
      $resolvedTokens[$value] = CRM_Utils_Array::value($value, $participant, '');
    }

  }

}
