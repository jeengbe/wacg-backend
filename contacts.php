<?php
  foreach(WA::getContacts() as $con) {
    $data[$con->getJid()] = [
      "jid" => $con->getJid(),
      "displayName" => $con->getDisplayName(),
      "number" => $con->getNum()
    ];
  }
  $data = array_values($data);
?>