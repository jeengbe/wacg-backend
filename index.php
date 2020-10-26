<?php
header("Access-Control-Allow-Origin: *");
?>
<?php
  include __DIR__."/colors.php";
  include __DIR__."/utils.php";
  include __DIR__."/wa.php";
  new WA();
  new MsgStore();

  const ME = "Jesper";
  // Separate them and me
  $options = [
    "separate" => true
  ];

  $options = array_merge(json_decode($_POST["options"] ?? "{}", true), $options);

  if(isset($_GET["url"])) {
    $url = explode("/", $_GET["url"]);
    $inc = null;
    switch($url[0]) {
      case "contacts":
        $inc = "contacts.php";
        break;
      case "msgsPerWeek":
        $inc = "charts/msgsPerWeek.php";
        break;
      case "msgTimes":
        $inc = "charts/msgTimes.php";
        break;
    }
    if($inc !== null) {
      (function($inc) use ($options) {
        /** @var string $inc */
        $data = [];
        include __DIR__."/$inc";
        echo json_encode($data);
      })($inc);
    }
  }

  WA::destruct();
  MsgStore::destruct();
?>