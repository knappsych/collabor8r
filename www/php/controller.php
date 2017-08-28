<?php
include '/homepages/41/d92908607/htdocs/collabor8r/protected/functions/private.php';
session_start();//start the session
$_SESSION['LastActivity']=time();
if(isadmin()){
	ini_set('display_errors',TRUE);//show errors for developer account only
	include '/homepages/41/d92908607/htdocs/collabor8r/protected/sandy/r8rfuncs.php';
}
else include '/homepages/41/d92908607/htdocs/collabor8r/protected/functions/r8rfuncs.php';	//include everything we might need
$userid=isloggedin();//check if user is logged in some forms should only be used by logged in users and vice versa
if($_SERVER['REQUEST_METHOD'] == "POST"){//only logged in users should perform actions
  $action=stripslashes($_POST["action"]);
  $postarr=array();
  
  //make sure only logged in users perform logged in actions and vice versa
  $loginreq=loginforaction($action);
  if($loginreq==="na" || ($loginreq && $userid) || (!$loginreq && !$userid)){
    //get the contents we need
    $postarr=fillpostarray($action, $_POST);
    //var_dump($postarr);
    if($problem=problemwithpost($action, $postarr)){//check for any problems
      messageforproblemwithpost($problem, $postarr);//present appropriate message for problem
      solutionforproblemwithpost($action, $postarr);//try to appropriately solve the problem
    }else{//We have everything we need try to do the action
      $processcode=processpost($action,$postarr,$userid);
    }
  }
  else{
    if($userid) echo "Sorry, you must be logged out to perform that action.<br>\n";
    else echo "Sorry, you must be logged in to perform that action.<br>\n";
  }
}else{//there was no POST
	give404(); //uncomment this line to regain the blog form, I commented it out to check some other code
}

?>