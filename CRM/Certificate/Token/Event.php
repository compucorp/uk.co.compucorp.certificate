<?php

use Civi\Token\Event\TokenValueEvent;

/**
 * Class CRM_Certificate_Token_Event
 *
 * Generate "certificate_event.*" tokens.
 *
 * This class defines the event tokens (standard fields and custom fields)
 * that are supported by a certificate
 */
class CRM_Certificate_Token_Event extends CRM_Certificate_Token_AbstractCertificateToken {

  const TOKEN = 'certificate event';

  public function __construct($tokenNames = []) {
    $this->tokenNames = $tokenNames;
  }

  /**
   * @inheritDoc
   */
  public static function entityTokens() {
    $eventCustomFields = CRM_Utils_Token::getCustomFieldTokens('Event');
    $eventTokens = CRM_Core_SelectValues::eventTokens();

    // we clean up the array because they are keyed in the format {event.field}
    $filtered_keys = preg_replace(['/(event\.)/', '/(\W*)/'], '', array_keys($eventTokens));
    $eventTokens = array_combine($filtered_keys, array_values($eventTokens));
    $tokens = array_merge($eventCustomFields, $eventTokens);

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
        $result = civicrm_api3('Event', 'getsingle', [
          'id' => $entityTypeId,
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
   * Resolve the value of event fields in the token event.
   *
   * @param array $event
   * @param array &$resolvedTokens
   */
  private function resolveFields($event, &$resolvedTokens) {
    // Convert date fields to human readable format (2022-12-01 12:12:00 -> 1st December 2022 12:12 PM).
    array_walk($event, function(&$v, $k) {
      $dateFields = [
        "end_date",
        "start_date",
        "event_end_date",
        "event_start_date",
        "registeration_end_date",
        "registeration_start_date",
      ];

      if (in_array($k, $dateFields)) {
        $v = CRM_Utils_Date::customFormat($v);
      }
    });

    foreach ($this->activeTokens as $value) {
      $resolvedTokens[$value] = CRM_Utils_Array::value($value, $event, '');
    }
  }

}
