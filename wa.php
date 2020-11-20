<?php
class WA {
  /** @var SQLite3 */
  protected static $db;

  function __construct() {
    self::$db = new SQLite3(__DIR__ . "/databases/wa.db");
    self::$db->busyTimeout(10000);
  }

  static function destruct() {
    self::$db->close();
  }

  /**
   * @param int $messages Messages to load, -1 for all
   * @return Contact[]
   */
  static function getContacts($messages = 0) {
    $r = [];
    $sql = self::$db->query("SELECT con._id as id, con.jid as jid, con.status as status, con.status_timestamp as statusTimestamp, number as num, con.display_name as displayName FROM wa_contacts as con WHERE number IS NOT NULL order by displayName");
    while ($row = $sql->fetchArray(SQLITE3_ASSOC)) {
      $r[] = new Contact($row, $messages);
    };
    return $r;
  }

  /**
   * @param string[] $jids
   * @param int $messages Messages to load, -1 for all
   * @return Contact[]
   */
  static function getContactsByJids($jids, $messages = -1) {
    $r = [];
    foreach($jids as $jid) {
      $r[] = self::getContactByJid($jid, $messages);
    }
    return $r;
  }

  /**
   * @param string $jid
   * @param int $messages Messages to load, -1 for all
   * @return Contact
   */
  static function getContactByJid($jid, $messages = -1) {
    $sql = self::$db->query("SELECT con._id as id, con.jid as jid, con.status as status, con.status_timestamp as statusTimestamp, number as num, con.display_name as displayName FROM wa_contacts as con WHERE number IS NOT NULL AND jid = '$jid'");
    if ($row = $sql->fetchArray()) {
      return new Contact($row, $messages);
    }
    throw new Exception("Jid not found");
  }

  /**
   * @param int $messages Messages to load, -1 for all
   * @return Group[]
   */
  static function getGroups($messages = 0) {
    $r = [];
    $sql = self::$db->query("SELECT grp._id as id, grp.jid as jid, grp.display_name as displayName, grpd.description as description FROM wa_contacts as grp LEFT JOIN wa_group_descriptions as grpd ON grp.jid = grpd.jid WHERE number IS NULL AND displayName IS NOT NULL order by displayName");
    while ($row = $sql->fetchArray(SQLITE3_ASSOC)) {
      $r[] = new Group($row, $messages);
    };
    return $r;
  }

  /**
   * @param string[] $jids
   * @param int $messages Messages to load, -1 for all
   * @return Group[]
   */
  static function getGroupsByJids($jids, $messages = -1) {
    $r = [];
    foreach ($jids as $jid) {
      $r[] = self::getGroupByJid($jid, $messages);
    }
    return $r;
  }

  /**
   * @param string $jid
   * @param int $messages Messages to load, -1 for all
   * @return Group
   */
  static function getGroupByJid($jid, $messages = -1) {
    $sql = self::$db->query("SELECT grp._id as id, grp.jid as jid, grp.display_name as displayName, grpd.description as description FROM wa_contacts as grp LEFT JOIN wa_group_descriptions as grpd ON grp.jid = grpd.jid WHERE number IS NULL AND displayName IS NOT NULL AND jid = '$jid'");
    if ($row = $sql->fetchArray()) {
      return new Group($row, $messages);
    }
    throw new Exception("Jid not found");
  }

  /**
   * @param string[] $jids
   * @param int $messages Messages to load, -1 for all
   * @return RawMessageable[]
   */
  static function getMessageablesByJids($jids, $messages = -1) {
    $r = [];
    foreach ($jids as $jid) {
      $r[] = self::getMessageableByJid($jid, $messages);
    }
    return $r;
  }

  /**
   * @param string $jid
   * @param int $messages Messages to load, -1 for all
   * @return RawMessageable
   */
  static function getMessageableByJid($jid, $messages = -1) {
    $sql = self::$db->query("SELECT msg._id as id, msg.jid as jid, msg.display_name as displayName FROM wa_contacts as msg WHERE jid = '$jid'");
    if ($row = $sql->fetchArray()) {
      return new RawMessageable($row, $messages);
    }
    throw new Exception("Jid not found");
  }

  /**
   * @return SQLite3
   */
  static function getDB() {
    return self::$db;
  }

  /**
   * @return DateTime
   */
  static function getDate() {
    return (new DateTime())->setTimestamp(filemtime(__DIR__ . "/databases/wa.db"));
  }
}

class MsgStore {
  /** @var SQLite3 */
  protected static $db;

  function __construct() {
    self::$db = new SQLite3(__DIR__ . "/databases/msgstore.db");
    self::$db->busyTimeout(10000);
  }

  static function destruct() {
    self::$db->close();
  }

  /**
   * @param string $jid
   * @param int $messages Messages to load, -1 for all
   * @return Message[]
   */
  static function getMessagesWithJid($jid, $messages) {
    global $options;
    $r = [];
    $limit = $timeinterval = "";

    if($messages != -1) {
      $limit = " LIMIT $messages";
    }
    if($options["start"] !== null || $options["end"] !== null) {
      $timeinterval = " AND ";
      if($options["start"] !== null) {
        $timeinterval .= "timestamp > " . ($options["start"] * 1000);
        if($options["end"] !== null) {
          $timeinterval .= " AND ";
        }
      }
      if($options["end"] !== null) {
        $timeinterval .= "timestamp < " . ($options["end"] * 1000);
      }
    }

    $sql = self::$db->query("SELECT msg.timestamp as timestamp, msg.receipt_device_timestamp as receivedTimestamp, msg.read_device_timestamp as readTimestamp, data, key_from_me as me FROM messages as msg WHERE key_remote_jid = '$jid' AND msg.status NOT IN (6)$timeinterval ORDER BY msg._id ASC$limit");

    while ($row = $sql->fetchArray()) {
      $r[] = new Message($row);
    }
    return $r;
  }

  /**
   * @return SQLite3
   */
  static  function getDB() {
    return self::$db;
  }

  /**
   * @return DateTime
   */
  static function getDate() {
    return (new DateTime())->setTimestamp(filemtime(__DIR__ . "/databases/msgstore.db"));
  }
}

abstract class Messageable {
  /** @var string */
  protected $jid;
  /** @var string */
  protected $displayName;
  /** @var Message[] */
  protected $messages;

  /**
   * @param array $row Corresponding row in wa.db:wa_contacts
   * @param int $messages Messages to load, -1 for all
   */
  function __construct($row, $messages = -1) {
    $this->jid              = $row["jid"];
    $this->displayName      = $row["displayName"];
    $this->loadMessages($messages);
  }

  protected function loadMessages($messages) {
    $this->messages = MsgStore::getMessagesWithJid($this->jid, $messages);
  }

  /**
   * @return string
   */
  function getJid() {
    return $this->jid;
  }

  /**
   * @return string
   */
  function getDisplayName() {
    return $this->displayName;
  }
  /**
   * @return Message[]
   */
  function getMessages() {
    return $this->messages;
  }
}

class RawMessageable extends Messageable {}

class Contact extends Messageable {
  /** @var string */
  protected $status;
  /** @var DateTime */
  protected $statusTimestamp;
  /** @var string */
  protected $num;

  function __construct($row, $messages = -1) {
    parent::__construct($row, $messages);
    $this->status           = $row["status"];
    $this->statusTimestamp  = new DateTime();
    $this->statusTimestamp->setTimestamp($row["statusTimestamp"] / 1000);
    $this->num              = $row["num"];
  }

  /**
   * @return string
   */
  function getStatus() {
    return $this->status;
  }
  /**
   * @return DateTime
   */
  function getStatusTimestamp() {
    return $this->statusTimestamp;
  }
  /**
   * @return string
   */
  function getNum() {
    return $this->num;
  }
}

class Group extends Messageable {
  /** @var string */
  protected $description;

  function __construct($row, $messages = -1) {
    parent::__construct($row, $messages);
    $this->description = $row["description"];
  }

  /**
   * @return string
   */
  function getDescription() {
    return $this->description;
  }
}

class Message {
  /** @var DateTime */
  protected $timestamp;
  /** @var DateTime */
  protected $received;
  /** @var DateTime */
  protected $read;
  /** @var boolean */
  protected $me;
  /** @var string */
  protected $data;

  function __construct($row) {
    $this->timestamp    = new DateTime();
    $this->timestamp    ->setTimestamp($row["timestamp"] / 1000);
    $this->received     = new DateTime();
    if($row["receivedTimestamp"] == -1) {
      $this->received     ->setTimestamp($row["readTimestamp"] / 1000);
    } else {
      $this->received     ->setTimestamp($row["receivedTimestamp"] / 1000);
    }
    $this->read         = new DateTime();
    $this->read         ->setTimestamp($row["readTimestamp"] / 1000);
    $this->me           = (bool) $row["me"];
    $this->data         = $row["data"];
  }

  /**
   * @return DateTime
   */
  function getTimestamp() {
    return $this->timestamp;
  }

  /**
   * @return DateTime
   */
  function getReceived() {
    return $this->received;
  }

  /**
   * @return DateTime
   */
  function getRead() {
    return $this->read;
  }

  /**
   * @return boolean
   */
  function isMe() {
    return $this->me;
  }

  /**
   * @return string
   */
  function getData() {
    return $this->data;
  }
}
