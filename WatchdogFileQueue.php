<?php

/**
 * @file
 * Class WatchdogFileQueue.
 */

/**
 * Class WatchdogFileQueue.
 */
class WatchdogFileQueue {

  private $fileName = "watchdog.log";
  private $fullFileName;
  private $handle;
  private $watchdogTable = 'watchdog';
  private $watchdogFunctionName = 'dblog_watchdog';
  private $delimiter = "<<<---WATCHDOG-QUEUE-DELIMITER-2npBE--->>>\n";

  /**
   * Class constructor.
   *
   * Supply a Watchdog Entry to log it right away. Call log() function to log
   * additional entries. Entries will be logged using Drupal Watchdog if
   * available; or stored in (file) queue to be logged later otherwise.
   *
   * @param \WatchdogEntry $entry
   *   Watchdog entry to be logged right away.
   */
  public function __construct(WatchdogEntry $entry = NULL) {
    // Prepare full path to the file we are going to use.
    $this->fullFileName = sys_get_temp_dir() . '/' . $this->fileName;
    $this->getFileHandle();

    // If an Entry was provided, try to log it.
    if (isset($entry)) {
      $this->log($entry);
    }

    // Check if we already have some entries in queue and attempt to write them.
    $this->writeQueue();
  }

  /**
   * Attempts to log single Entry.
   *
   * If Drupal 'watchdog' is available - i.e. both table and watchdog function
   * exists - regular watchdog() is called.
   * Otherwise the entry is saved to a temporary file, waiting for watchdog to
   * appear and to be saved to database then.
   *
   * @param \WatchdogEntry $entry
   *   Watchdog entry to be logged.
   */
  public function log(WatchdogEntry $entry) {
    if ($this->watchdogReady()) {
      // Log with watchdog as usually.
      $this->watchdog($entry);
      // Watchdog is available, so check if there are already some entries in
      // the queue and try to write them to DB.
      $this->writeQueue();
    }
    else {
      // Watchdog is not ready - add entry to the queue.
      $this->addToQueue($entry);
    }
  }

  /**
   * Empties queue file without writing it's content to DB.
   */
  public function emptyQueueFile() {
    $this->getFileHandle();
    ftruncate($this->handle, 0);
    fclose($this->handle);
  }

  /**
   * Opens specified temporary file and sets $this->handle to it.
   */
  private function getFileHandle() {
    if (file_exists($this->fullFileName)) {
      $this->handle = fopen($this->fullFileName, "r+");
    }
    else {
      $this->handle = fopen($this->fullFileName, "w+");
    }
  }

  /**
   * Determines whether the Watchdog is already prepared and we can use it.
   *
   * @return bool
   *   Returns TRUE is watchdog is ready.
   */
  private function watchdogReady() {
    $ready = FALSE;
    if (db_table_exists($this->watchdogTable) && function_exists($this->watchdogFunctionName)) {
      $ready = TRUE;
    }
    return $ready;
  }

  /**
   * Adds provided $entry at the end of the queue file.
   *
   * Entries are separated by new line at the end of each entry.
   *
   * @param \WatchdogEntry $entry
   *   Watchdog entry to be added to the queue.
   */
  private function addToQueue(WatchdogEntry $entry) {
    $entry->setMessage("[QUEUED] " . $entry->getMessage());
    // Making sure our file is open and ready.
    $this->getFileHandle();
    fseek($this->handle, 0, SEEK_END);
    // Don't forget to add newline at the end, so we can read entries
    // one by one.
    fwrite($this->handle, serialize($entry) . $this->delimiter);
    fclose($this->handle);
    $this->handle = NULL;
  }

  /**
   * Writes queue if Entries stored in temporary file to DB.
   *
   * @return bool
   *   Returns TRUE if writing process was successful.
   */
  private function writeQueue() {
    $success = FALSE;

    // Final check if Drupal Watchdog is really ready.
    if ($this->watchdogReady()) {
      $this->getFileHandle();
      // Seek back at the beginning of file.
      fseek($this->handle, 0, SEEK_SET);

      $contents = file_get_contents($this->fullFileName);
      if (($contents !== FALSE) && (count($contents) > 0)) {
        $data_array = explode($this->delimiter, $contents);
        if (count($data_array) > 0) {
          foreach ($data_array as $serialised) {
            $e = unserialize($serialised);
            if ($e instanceof WatchdogEntry) {
              $this->watchdog($e);
            }
          }
        }
      }
      ftruncate($this->handle, 0);
      fclose($this->handle);
      $this->handle = NULL;

      $success = TRUE;
    }

    return $success;
  }

  private function watchdog(WatchdogEntry $entry) {
    if ($this->isValid($entry)) {
      global $user, $base_root;

      // The user object may not exist in all conditions,
      // so 0 is substituted if needed.
      $user_uid = isset($user->uid) ? $user->uid : 0;

      $entry_array = array(
        'type' => $entry->getType(),
        'message' => $entry->getMessage(),
        'variables' => $entry->getVariables(),
        'severity' => $entry->getSeverity(),
        'link' => '',
        'user' => $user,
        'uid' => $user_uid,
        'request_uri' => $base_root . request_uri(),
        'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
        'ip' => ip_address(),
        'timestamp' => $entry->getTimestamp(),
      );

      dblog_watchdog($entry_array);
    }
  }

  public function isValid(WatchdogEntry $entry) {
    $valid = TRUE;
    try {
      if (FALSE == isset($entry)) {
        throw new Exception();
      }
      if (FALSE == ($entry instanceof WatchdogEntry)) {
        throw new Exception();
      }
      if (empty($entry->getMessage())) {
        throw new Exception();
      }
    }
    catch (Exception $e) {
      $valid = FALSE;
    }

    return $valid;
  }

  /**
   * Class destructor.
   *
   * In destructor we:
   * 1) Make sure we close the file.
   * 2) Perform last attempt to write queue to DB.
   */
  public function __destruct() {
    // Make last attempt to write queue to DB.
    $this->getFileHandle();
    $this->writeQueue();
    // Make sure we close the file properly.
    $this->getFileHandle();
    fclose($this->handle);
  }

}
