<?php
foreach (WA::getContacts() as $con) {
  $data[$con->getJid()] = [
    "jid" => $con->getJid(),
    "displayName" => $con->getDisplayName(),
    "info" => $con->getNum(),
    "type" => "group"
  ];
}
foreach (WA::getGroups() as $grp) {
  $data[$grp->getJid()] = [
    "jid" => $grp->getJid(),
    "displayName" => $grp->getDisplayName(),
    "info" => $grp->getDescription(),
    "type" => "contact"
  ];
}
$data = array_values($data);
