<?php

$jid = $_POST["jid"];
$sets = $_POST["sets"]-1;

$con = WA::getMessageableByJid($jid);
$msgs = $con->getMessages();

$arr = [];

$me = [];
for($h = 0; $h < 24; $h++) {
  for($m = 0; $m < 6; $m++) {
    $me[str_pad($h, 2, "0", STR_PAD_LEFT).":".str_pad($m, 2, "0", STR_PAD_LEFT)] = 0;
  }
}
$them = $me;

$total = 0;
foreach($msgs as $msg) {
  if($msg->isMe() && $options["separate"]) {
    $arr = &$me;
  } else {
    $arr = &$them;
  }
  $total++;
  $ts = date("H", $msg->getTimestamp()->getTimestamp()).":".str_pad(intval(date("i", $msg->getTimestamp()->getTimestamp()) / 10), 2, "0", STR_PAD_LEFT);
  $arr[$ts]++;
}

Utils::addDataset($data["datasets"], $con->getDisplayName(), COLORS[$sets % count(COLORS)][0][0], COLORS[$sets % count(COLORS)][0][1], $them);
if ($options["separate"]) {
  Utils::addDataset($data["datasets"], ME, COLORS[$sets % count(COLORS)][1][0], COLORS[$sets % count(COLORS)][1][1], $me);
}

$data["date"] = MsgStore::getDate()->format("c");