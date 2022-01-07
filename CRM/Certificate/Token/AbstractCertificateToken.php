<?php

use Civi\Token\TokenRow;
use Civi\Token\Event\TokenValueEvent;
use Civi\Token\AbstractTokenSubscriber;

abstract class CRM_Certificate_Token_AbstractCertificateToken extends AbstractTokenSubscriber {

  const TOKEN = 'certificate';

  public static function getSubscribedEvents() {
    return [
      'civi.token.list' => 'registerTokens',
      'civi.token.eval' => 'evaluateTokens',
    ];
  }

  /**
   * Determine whether this token-handler should be used with
   * the given processor.
   *
   * @param \Civi\Token\TokenProcessor $processor
   * @return bool
   */
  public function checkActive(\Civi\Token\TokenProcessor $processor) {
    return !empty(array_intersect([static::TOKEN], $processor->context["hookTokenCategories"]));
  }

  /**
   * Add entity name as a prefix to token names
   *
   * @param array $names
   * @return array
   */
  public static function prefixTokenNames($names) {
    $prefixed = [];
    $prefix = CRM_Utils_String::munge(static::TOKEN);
    array_walk(
      $names,
      function ($value, $key) use ($prefix, &$prefixed) {
        $prefixed[$prefix . "." . $key] = $value;
      }
    );
    return $prefixed;
  }

  /**
   * @return array
   */
  public static function prefixedEntityTokens() {
    return static::prefixTokenNames(static::entityTokens());
  }

  /**
   * @inheritDoc
   */
  public function getActiveTokens(TokenValueEvent $e) {
    $messageTokens = $e->getTokenProcessor()->getMessageTokens();
    $prefix = CRM_Utils_String::munge(static::TOKEN);

    if (!isset($messageTokens[$prefix])) {
      return FALSE;
    }

    return array_intersect($messageTokens[$prefix], array_keys(static::entityTokens()));
  }

  /**
   * Given a token event object, it returns all the tokens that
   * are to be resolved by the suscriber instance
   *
   * @param \Civi\Token\Event\TokenValueEvent $e
   * @return array
   */
  protected function getTokenEventfields(TokenValueEvent $e) {
    $messageTokens = $e->getTokenProcessor()->getMessageTokens();
    $prefix = CRM_Utils_String::munge(static::TOKEN);

    return $messageTokens[$prefix] ?? [];
  }

  /**
   * Evaluate the content of a single token.
   *
   * @param \Civi\Token\TokenRow $row
   *   The record for which we want token values.
   * @param string $entity
   *   The name of the token entity.
   * @param string $field
   *   The name of the token field.
   * @param mixed $prefetch
   *   Any data that was returned by the prefetch().
   */
  public function evaluateToken(TokenRow $row, $entity, $field, $prefetch = NULL) {
    $value = CRM_Utils_Array::value($field, $prefetch);
    $prefix = CRM_Utils_String::munge(static::TOKEN);
    if (is_array($value)) {
      foreach ($value as $format => $data) {
        $row->format($format)->tokens($prefix, $field, $data);
      }
      return;
    }

    if ($value) {
      $row->tokens($prefix, $field, $value);
    }
  }

  /**
   * Specifies the tokens defined by the subscriber instance
   *
   * @return array
   *   [token_name => token_label]
   *
   */
  abstract public static function entityTokens();

}
