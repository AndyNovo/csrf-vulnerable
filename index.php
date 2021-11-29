<?php
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST');
  header("Access-Control-Allow-Headers: X-Requested-With");
  ini_set('session.cookie_samesite', 'None');
  session_start();
  
  function sanitize_html_string($string)
  {
    $pattern[0] = '/\&/';
    $pattern[1] = '/</';
    $pattern[2] = "/>/";
    $pattern[3] = '/\n/';
    $pattern[4] = '/"/';
    $pattern[5] = "/'/";
    $pattern[6] = "/%/";
    $pattern[7] = '/\(/';
    $pattern[8] = '/\)/';
    $pattern[9] = '/\+/';
    $pattern[10] = '/-/';
    $replacement[0] = '&amp;';
    $replacement[1] = '&lt;';
    $replacement[2] = '&gt;';
    $replacement[3] = '<br>';
    $replacement[4] = '&quot;';
    $replacement[5] = '&#39;';
    $replacement[6] = '&#37;';
    $replacement[7] = '&#40;';
    $replacement[8] = '&#41;';
    $replacement[9] = '&#43;';
    $replacement[10] = '&#45;';
    return preg_replace($pattern, $replacement, $string);
  }

  function add_message($message){
    $dbhandle = new PDO("sqlite:chat.db") or die("Failed to open DB");
    if (!$dbhandle) die ($error);
    $statement = $dbhandle->prepare("insert into messages ('username','message') values (:username,:message)");
    $statement->bindParam(":username", $_SESSION["username"]);
    $statement->bindParam(":message", $message);
    $statement->execute();
  };
  
  function render_chat(){
    $dbhandle = new PDO("sqlite:chat.db") or die("Failed to open DB");
    if (!$dbhandle) die ($error);
    $statement = $dbhandle->prepare("select username, message from messages order by id DESC limit 0, 100");
    $statement->execute();
    $messages = $statement->fetchAll(PDO::FETCH_ASSOC);
    $template = file_get_contents("chat.html");
    $message_template = file_get_contents("message.html");
    $message_rows = "";
    foreach($messages as $message){
      $message_rows .= str_replace("USERNAME", sanitize_html_string($message["username"]), 
                            str_replace("MESSAGEHERE", sanitize_html_string($message["message"]), $message_template));
    }
    echo str_replace("MESSAGESHERE", $message_rows, 
        str_replace("MYUSERNAME",sanitize_html_string($_SESSION["username"]), $template));
  };
  
  function render_login($message = ""){
    $template = file_get_contents("login.html");
    echo str_replace("MESSAGEHERE", sanitize_html_string($message), $template);
  };

  function login($username, $pwd){
    $dbhandle = new PDO("sqlite:chat.db") or die("Failed to open DB");
    if (!$dbhandle) die ($error);
    $statement = $dbhandle->prepare("Select * from users where username=:username and password=:password");
    $statement->bindParam(":username", $username);
    $statement->bindParam(":password", $pwd);
    $statement->execute();
    $results = $statement->fetch(PDO::FETCH_ASSOC);
    if (isset($results["username"])){
      $_SESSION["username"] = $results["username"];
      $_SESSION["logged_in"] = "1";
      render_chat();
    } else {
      render_login("Failed authentication");
    }
  };
  
  function logout(){
    session_start();
    unset($_SESSION["username"]);
    unset($_SESSION["logged_in"]);
  };
  
  function register($username, $pwd){
    $dbhandle = new PDO("sqlite:chat.db") or die("Failed to open DB");
    if (!$dbhandle) die ($error);
    $statement = $dbhandle->prepare("insert into users values (:username,:password)");
    $statement->bindParam(":username", $username);
    $statement->bindParam(":password", $pwd);
    $statement->execute();
    $_SESSION["username"] = $username;
    $_SESSION["logged_in"] = "1";
  };
  if (isset($_SESSION["logged_in"])){
    if ($_SESSION["logged_in"] == "1"){
      if (isset($_REQUEST["logout"])){
          logout();
          render_login();
      } else if (isset($_REQUEST["message"])){
          add_message($_REQUEST["message"]);
          render_chat();
      } else {
        render_chat();
      }
    }
  } else {
    if (isset($_REQUEST["login"])){
        login($_REQUEST["username"], $_REQUEST["password"]);
    } else if (isset($_REQUEST["register"])) {
        register($_REQUEST["username"], $_REQUEST["password"]);
        render_chat();
    } else {
        render_login();
    }
  }
?>
