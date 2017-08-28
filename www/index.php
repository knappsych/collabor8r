<?php
	include '/homepages/41/d92908607/htdocs/collabor8r/protected/functions/private.php';
	$time=time();
	$message='';
	$admin='';
	if(($sessionid=session_id())===''){
		session_start();//start the session
		$_SESSION['StartTime']=$time;
		$_SESSION['LastActivity']=$time;
		$_SESSION['StartIP']=$_SERVER['REMOTE_ADDR'];
		//$sessionid=session_id();
	}
	elseif(!isset($_SESSION['LastActivity']) || (($lastactivity=$_SESSION['LastActivity'])<($time-3600)) || $_SESSION['StartIP']!=$_SERVER['REMOTE_ADDR']){
		if(isset($_SESSION['ID']) && isset($_SESSION['StartTime'])){//it's a logged in user who's went past the time
			$userid=$_SESSION['ID'];
			$sessionstart=$_SESSION['StartTime'];
			$oldsessionid=$sessionid;
			$message='logout';
		}
		$_SESSION=array();
		$sessionid=session_regenerate_id();
		$_SESSION['StartTime']=$time;
		$_SESSION['LastActivity']=$time;
		$_SESSION['StartIP']=$_SERVER['REMOTE_ADDR'];
	}
	else $_SESSION['LastActivity']=$time;
	
	if(isadmin()){
		$admin=TRUE;
		ini_set('display_errors',TRUE);//show errors for developer account only
		include '/homepages/41/d92908607/htdocs/collabor8r/protected/sandy/r8rfuncs.php';
	}
	else include '/homepages/41/d92908607/htdocs/collabor8r/protected/functions/r8rfuncs.php';
	
	$response=getresponselinkinfo();//figure out what to do from the link
	giveheader($response['title'],$response['canonicalurl']);//give the header with the title and canonical url
	if($message=='logout'){
		$db=usedb();
		$userid=mysqli_real_escape_string($db, $userid);
		$sessionstart=mysqli_real_escape_string($db, $sessionstart);
		$oldsessionid=mysqli_real_escape_string($db, $oldsessionid);
		$lastactivity=mysqli_real_escape_string($db, $lastactivity);
		$sql=<<<SQL
			UPDATE sessions
			SET EndTime = $lastactivity
			WHERE UserID=$userid AND StartTime=$sessionstart AND ID='$oldsessionid'
SQL;
		if(!$results=mysqli_query($db, $sql)){
			mysqli_close($db);
			emailadminerror('mysqlerror', array('location'=>"indexendofsessionupdate", 'query'=>$sql));
		}
		elseif(!mysqli_affected_rows($db)===1){
			mysqli_close($db);
			emailadminerror('mysqlerror', array('location'=>"indexendofsessionaffected$rows", 'query'=>$sql));
		}
		else mysqli_close($db);
		$message='<p class="caution">For your safety, you have been logged off for inactivity.</p>';
	}		
	if(!(($adtype=$response['advertisement'])===FALSE)) $message=advert($adtype).$message;
	if($admin===TRUE){//allow ad to view debugging information or test functions
		$debugging='';
		echo $message.$response['body'].$debugging;
	}
	else echo $message.$response['body'];//present the body text
	//if(!$response['advertisement']===FALSE) echo advert('test');
	givefooter();//give the footer
?>