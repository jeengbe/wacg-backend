<?php

$jid = $_POST["jid"];
$sets = $_POST["sets"]-1;

$con = WA::getMessageableByJid($jid);
$msgs = $con->getMessages();

$start = Utils::getMonday($msgs[0]->getTimestamp()->getTimestamp());
$end = Utils::getMonday(time() + 7*24*60*60);

$time = $start;
$me = [];
while($time <= $end) {
  $me[Utils::getMonday($time)] = [];
  $time += 7 * 24 * 60 * 60;
}

$them = $me;


foreach($msgs as $msg) {
  if($msg->isMe() && $options["separate"]) {
    $arr = &$me;
  } else {
    $arr = &$them;
  }
  $ts = Utils::getMonday($msg->getTimestamp()->getTimestamp());
  $arr[$ts][] = strlen($msg->getData());
}

foreach ($me as $ts => $v) {
  $me[$ts] = [
    "y" => count($v) == 0 ? 0 : round(array_sum($v) / count($v), 3),
    "x" => $ts * 1000
  ];
}

foreach ($them as $ts => $v) {
  $them[$ts] = [
    "y" => count($v) == 0 ? 0 : round(array_sum($v) / count($v), 3),
    "x" => $ts*1000
  ];
}

Utils::addDataset($data["datasets"], $con->getDisplayName(), COLORS[$sets % (count(COLORS) - 1)][0][0], COLORS[$sets % (count(COLORS) - 1)][0][1], $them);
if ($options["separate"]) {
  Utils::addDataset($data["datasets"], ME, COLORS[$sets % (count(COLORS) - 1)][1][0], COLORS[$sets % (count(COLORS) - 1)][1][1], $me);
}

$data["date"] = MsgStore::getDate()->format("c");