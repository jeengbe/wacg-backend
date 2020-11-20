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
    "separate" => true,
    "start" => null,
    "end" => null
  ];

  // $options["start"] = strtotime("10. Nov. 2020");

  $options = array_merge($options, json_decode($_POST["options"] ?? "{}", true));

  if(isset($_GET["url"])) {
    $url = explode("/", $_GET["url"]);
    $inc = null;
    switch($url[0]) {
      case "contacts":
        $inc = "contacts.php";
        break;
      default:
        if(preg_match("/^[a-zA-Z0-9_-]+$/", $url[0]) === 1) {
          $inc = "charts/{$url[0]}.php";
        }
        if(substr($url[0], -4) == "Week") {
          date_default_timezone_set("UTC");
        } else {
          date_default_timezone_set("CET");
        }
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