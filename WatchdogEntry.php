<?php

/**
 * @file
 * File WatchdogEntry - POPO class.
 */

/**
 * Class WatchdogEntry.
 */
class WatchdogEntry {
  private $type;
  private $message;
  private $variables;
  private $severity;
  private $timestamp;

  /**
   * Class constructor.
   *
   * @param string $type
   *   Same as watchdog 'type' parameter.
   * @param string $message
   *   Same as watchdog 'message' parameter.
   * @param array $variables
   *   Same as watchdog 'variables' parameter.
   * @param int $severity
   *   Same as watchdog 'severity' parameter.
   */
  public function __construct($type, $message, array $variables = array(), $severity = WATCHDOG_NOTICE) {
    $this->type = $type;
    $this->message = $message;
    $this->variables = $variables;
    $this->severity = $severity;
    $this->timestamp = time();

    if (is_null($this->variables)) {
      $this->variables = array();
    }
  }

  /**
   * Type getter.
   *
   * @return string
   *   Returns type.
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Type setter.
   *
   * @param string $type
   *   Same as watchdog 'type' parameter.
   */
  public function setType($type) {
    $this->type = $type;
  }

  /**
   * Message getter.
   *
   * @return string
   *   Returns message.
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * Message setter.
   *
   * @param string $message
   *   Same as watchdog 'message' parameter.
   */
  public function setMessage($message) {
    $this->message = $message;
  }

  /**
   * Variables getter.
   *
   * @return array
   *   Returns variables.
   */
  public function getVariables() {
    return $this->variables;
  }

  /**
   * Variables setter.
   *
   * @param array $variables
   *   Same as watchdog 'variables' parameter.
   */
  public function setVariables(array $variables) {
    $this->variables = $variables;
  }

  /**
   * Severity getter.
   *
   * @return int
   *   Returns severity.
   */
  public function getSeverity() {
    return $this->severity;
  }

  /**
   * Severity setter.
   *
   * @param int $severity
   *   Same as watchdog 'severity' parameter.
   */
  public function setSeverity($severity) {
    $this->severity = $severity;
  }

  /**
   * Timestamp getter.
   *
   * @return int
   *   Returns timestamp.
   */
  public function getTimestamp() {
    return $this->timestamp;
  }

}
