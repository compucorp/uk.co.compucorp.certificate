<?php

use Civi\Api4\Event;
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
    $eventTokens = [
      'event_type' => ts('Event Type'),
      'title' => ts('Event Title'),
      'id' => ts('Event ID'),
      'start_date' => ts('Event Start Date'),
      'end_date' => ts('Event End Date'),
      'summary' => ts('Event Summary'),
      'description' => ts('Event Description'),
      'location' => ts('Event Location'),
      'info_url' => ts('Event Info URL'),
      'registration_url' => ts('Event Registration URL'),
      'contact_email' => ts('Event Contact Email'),
      'contact_phone' => ts('Event Contact Phone'),
    ];

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
        $participant = civicrm_api3('Participant', 'getsingle', [
          'id' => $entityTypeId,
          'contact_id' => $contactId,
        ]);

        $resolvedTokens = $this->getEventTokens($participant['event_id']);
      }
    }
    catch (Exception $e) {
      CRM_Core_Session::setStatus('Error resolving tokens');
    }

    return $resolvedTokens;
  }

  /**
   * Retrieves Event tokens value.
   *
   * @param int $eventId
   *
   * @return array
   */
  private function getEventTokens($eventId) {
    $event = Event::get(FALSE)->addWhere('id', '=', $eventId)
      ->setJoin([
        ['LocBlock AS loc_block', FALSE],
        ['Email AS email', FALSE, NULL, ['loc_block.email_id', '=', 'email.id']],
        ['Phone AS phone', FALSE, NULL, ['loc_block.phone_id', '=', 'phone.id']],
        ['Address AS address', FALSE, NULL, ['loc_block.address_id', '=', 'address.id']],
      ])
      ->setSelect([
        'event_type_id',
        'title',
        'id',
        'start_date',
        'end_date',
        'summary',
        'description',
        'loc_block_id',
        'address.street_address',
        'address.city',
        'address.state_province_id:label',
        'address.postal_code',
        'email.email',
        'phone.phone',
        'custom.*',
      ])->execute()->first();

    $tokens['location']['text/plain'] = \CRM_Utils_Address::format([
      'street_address' => $event['address.street_address'],
      'city' => $event['address.city'],
      'state_province' => $event['address.state_province_id:label'],
      'postal_code' => $event['address.postal_code'],

    ]);
    $tokens['info_url']['text/html'] = \CRM_Utils_System::href('civicrm/event/info', 'reset=1&id=' . $eventId, TRUE, NULL, TRUE);
    $tokens['registration_url']['text/html'] = \CRM_Utils_System::href('civicrm/event/register', 'reset=1&id=' . $eventId, TRUE, NULL, TRUE);
    $tokens['start_date']['text/html'] = !empty($event['start_date']) ? CRM_Utils_Date::customFormat($event['start_date']) : '';
    $tokens['end_date']['text/html'] = !empty($event['end_date']) ? CRM_Utils_Date::customFormat($event['end_date']) : '';
    $tokens['event_type']['text/html'] = CRM_Core_PseudoConstant::getLabel('CRM_Event_BAO_Event', 'event_type_id', $event['event_type_id']);
    $tokens['contact_phone']['text/html'] = $event['phone.phone'] ?? '';
    $tokens['contact_email']['text/html'] = $event['email.email'] ?? '';

    foreach (array_keys($this->entityTokens()) as $field) {
      if (!isset($tokens[$field])) {
        if ($id = \CRM_Core_BAO_CustomField::getKeyID($field)) {
          $name = CRM_Core_BAO_CustomField::getNameFromID($id);
          $customField = $name[$id]['group_name'] . '.' . $name[$id]['field_name'];
          $tokens[$field]['text/html'] = CRM_Core_BAO_CustomField::displayValue((string) $event[$customField], $id, $event['id']);
        }
        else {
          $tokens[$field]['text/html'] = $event[$field] ?? '';
        }
      }
    }

    return $tokens;
  }

}
