<?php
#stories have contenttype = 0
#comments have contenttype = 1

function advert($type){
	//storieshead
	//storiesmid
	//searchhead
	//searchmid
	//searchend
	//storyhead
	//storymid (between comments)
		//subtypes
			//widesky
			//leader
			//mrec
			//lrec
	$client=googleadclient();
	if($type=='storieshead' || $type=='searchhead'){
		return <<<END
<div class="vertban">
<script type="text/javascript"><!--
google_ad_client = "$client";
/* widesky */
google_ad_slot = "0722690803";
google_ad_width = 160;
google_ad_height = 600;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
</div>
END;
	}
	elseif($type=='false' || $type=='FALSE' || $type=='none')return '';
	else return <<<END
<div class="horban">
	<script type="text/javascript"><!--
		google_ad_client = "ca-pub-3925489771614158";
		/* leader */
		google_ad_slot = "3707635821";
		google_ad_width = 728;
		google_ad_height = 90;
		//-->
	</script>
	<script type="text/javascript"
		src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
	</script>
</div>
END;
}

function altwrapper($ctype, $contentstowrap, $parentdiv, $userid, $cnsfw, $nsfwok, $chide, $show, $closediv=TRUE){
	//ctype is content type story/comment/comment group (note comment groups are not nsfw but comments in the group might be)
		//comment groups can also not be hidden from a feed, but individual comments can
	//contents to wrap are the contents we're wrapping
	//parentdiv is the div for the contents that will hold the output
	//userid is the users id 0 if not logged in
	//cnsfw identifies the contents as SFW = 0 or NSFW = 1
	//nsfwok identifies whether or not the user wants NSFW contents visible by default (1) or not (0)
	//chide is whether or not the user has chosen to hide the contents (1) or not (0) from their feed
	//show indicates whether or not we're viewing hidden contents ('show') or not (0)

	$nsfw=($cnsfw)? '<span class="caution">NSFW</span>' : '';
	if($ctype=='comment')$ctype="this $nsfw comment and any replies to it";
	elseif($ctype=='commentgroup')$ctype='all comments on this story';
	elseif($ctype=='story')$ctype="this $nsfw story";
	else $ctype='this';
	if(!$userid){//not logged in so hide nsfw
		if($cnsfw){//it's nsfw
			$altclass='';
			$altmessage="$nsfw ".button('hide', "$parentdiv.showt", 'Show');
			$contclass='nodisplay';
		}
		else{//content is SFW allow them to temporarily hide it
			$altclass='nodisplay';
			$altmessage="You've hidden $ctype: ".button('hide', "$parentdiv.showt", 'Show');
			$contclass='';
		}
	}
	else{//we have a logged in user
		if($show && (!$cnsfw || $nsfwok)){//user has chosen to show hidden contents so present option to read the story and add to their feed.
			$altclass='nodisplay';
			$altmessage="You've hidden $ctype: ".button('hide', "$parentdiv.showt", 'Show');
			$contclass='';
		}
		elseif($chide){//not looking for hidden contents but story has been marked hidden (relevant for classes in which the stories always appear)
			$altclass='';
			$altmessage="You've hidden $ctype: ".button('hide', "$parentdiv.showt", 'Show');
			$contclass='nodisplay';
		}
		elseif($cnsfw){//content is NSFW and user hasn't requested to automatically view NSFW contents
			$altmessage="$nsfw ".button('hide', "$parentdiv.showt", 'Show').' '.createlink('options', array('username'=>$_SESSION['UserName']), 'Change Preferences');
			if($nsfwok){
				$altclass='nodisplay';
				$contclass='';
			}
			else{
				$altclass='';
				$contclass='nodisplay';
			}
		}
		else{//the content is not hidden, put in the default message to hide if we later hide the story and display the altdiv
			$altclass='nodisplay';
			$altmessage="You've hidden $ctype: ".button('hide', "$parentdiv.showt", 'Show');
			$contclass='';
		}
	}
	$closediv=($closediv)? '</div>' : '';
	$altclass=($altclass!='')? "class=\"$altclass\"" : '';
	$contclass=($contclass!='')? "class=\"$contclass\"" : '';
		//for debugging // ctype is $ctype cnsfw is $cnsfw nsfwok is $nsfwok chide is $chide show is $show userid is $userid 
	return<<<SQL
\n<div id="$parentdiv.alt"$altclass>$altmessage</div>
<div id="$parentdiv.contents"$contclass>$contentstowrap $closediv
SQL;
}

function badwords(){
	return array(
		"ass"
		,"asshole"
		,"asshat"
		,"cock"
		,"cocksucker"
		,"cunt"
		,"damn"
		,"dick"
		,"dickhead"
		,"dipshit"
		,"dip-shit"
		,"fuck"
		,"fucked"
		,"fucker"
		,"fuckface"
		,"fucking"
		,"fuckyou"
		,"fuck-you"
		,"goddammit"
		,"god-dammit"
		,"god-damm-it"
		,"goddamnit"
		,"god-damnit"
		,"god-damn-it"
		,"motherfucker"
		,"mother-fucker"
		,"mother fucker"
		,"pussy"
		,"shit"
		,"shithead"
	);
}

function button($action, $divid, $message, $extraclass="", $buttonid=""){
	$bid=($buttonid=="")? "" : "id=\"$buttonid\"";
	return "<span $bid class=\"clickable_span participate $extraclass\" onclick=\"controller('$action', '$divid')\">$message</span>";
}

function checkcontentid($from, $identifier){
	//returns an array including the mysql escaped requesters id (if any) and mysql safe and verified identifier 
	$userid=isloggedin();
	$sql='';
	if($from=='class'){
		$sql1=<<<SQL
			SELECT classes.ID, classes.OwnerID, CM.UserID, CM.ID AS MID
			FROM classes
			LEFT JOIN (
				SELECT ID, UserID, ClassID
				FROM classmembers
				WHERE ClassID = 
SQL;
		$sql2=' AND UserID = ';
		$sql=<<<SQL
			) AS CM ON CM.ClassID = classes.ID
			WHERE classes.ID = 
SQL;
	}
	elseif($from=='following'){
		$sql1=<<<SQL
			SELECT FollowerID AS ID
			FROM users
			LEFT JOIN following ON following.FollowerID = users.ID
			WHERE users.UserName = "
SQL;
		$sql='"';
	}
	elseif($from=='user' || $from=='self' || $from=='feed' || $from=='classes'){
		$sql1=<<<SQL
			SELECT ID
			FROM users
			WHERE UserName = "
SQL;
		$sql='"';
	}
	elseif($from=='storylink'){
		$sql1=<<<SQL
			SELECT ID
			FROM stories
			WHERE stories.InternalLink = "
SQL;
		$sql='"';
	}
	elseif($from=='storyid') $sql='SELECT ID FROM stories WHERE stories.ID = ';
	elseif($from=='comment') $sql='SELECT ID FROM comments WHERE comments.ID = ';
	elseif($from=='submissions' || $from=='frontpage') return array('ID'=>$userid, 'user'=>$userid);
	
	//we should have part of a query here, if not something went wrong
	if($sql=='')return FALSE;
	//if here, we're ok to check
	$db=usedb();
	$userid=mysqli_real_escape_string($db, $userid);
	$identifier=mysqli_real_escape_string($db, $identifier);
	if($from=='class')$sql=$sql1.$identifier.$sql2.$userid.$sql.$identifier.' LIMIT 1';
	elseif($from=='user' || $from=='self' || $from=='feed' || $from=='following' || $from=='classes' || $from=='storylink')$sql=$sql1.$identifier.$sql.' LIMIT 1';
	else $sql=$sql.$identifier.' LIMIT 1';
	//echo "<p>The sql in checkcontentid is $sql</p>";
	$result=mysqli_query($db, $sql);
	mysqli_close($db);
	$rows = ($result)? mysqli_num_rows($result) : 0;
	if($rows){
		$assoc = mysqli_fetch_assoc($result);
		$retarr=array('ID'=>$assoc['ID'],'user'=>$userid);
		if($from=='class'){
			if($assoc['OwnerID']==$userid)$retarr['role']='instructor';
			elseif($assoc['UserID']==$userid){
				$retarr['role']='student';
				if($from=='class')$retarr['MID']=$assoc['MID'];//returning the members id so we'll only get their stories
			}
			else return FALSE;
		}
		return $retarr;
	}else return FALSE;
}

function closediv($type, $divid="", $rid=""){//when presenting a special message at the end of div call closediv
	switch($type){
		case "sumcsinstructing":
			$message="\n<div id=\"startclass\">".button("class", "class.teaching.form", "Start A Class!")."</div>";
			break;
		case "sumcstaking":
			$message="\n<div id=\"startclass\">".button("class", "class.taking.form", "Join A Class!")."</div>";
			break;
		default:
			$message="";
			break;
	}
	return "$message</div>";
}

function closedivs($numbertoclose){
	return str_repeat("</div>", $numbertoclose);
}

function contentjoiner($what, $from, $contenttojoin, $rid){
	//first create the select portion
	$sql='SELECT ids.*';
	//everything is connected to a story
	if($what=='all' || $what=='stories' || ($what=='tags' && $from!='story' && $from!='comment') || ($what=='comments' && $from!='story' && $from!='comment')){
		$sql=<<<SQL
			$sql, stories.CreationTime AS SCreation, stories.LastActionTime AS SAction, stories.URL,
			stories.Title, stories.InternalLink,	stories.TheText AS SText, stories.Extra,
			stories.Privacy AS SPrivacy, stories.NSFW AS SNSFW, sauth.DisplayName AS SDName, sauth.UserName AS SUName
SQL;
		if($rid){
			$sql.=', svotes.Sig AS SSig';
			if($_SESSION['IsFollowing'])$sql.=', sfollow.following AS SF';
		}//end rid
	}//end all stories tags....
	//do the comments if we need to 
	if($what=='all' || $what=='comments'){
		$sql=<<<SQL
			$sql, comments.CreationTime AS CCreation, comments.LastActionTime AS CAction, commenttext.TheText AS CText,
			comments.Privacy AS CPrivacy, comments.NSFW AS CNSFW, cauth.DisplayName AS CDName, cauth.UserName AS CUName 
SQL;
		if($rid){
			$sql.=', cvotes.Sig AS CSig';
			if($_SESSION['IsFollowing'])$sql.=', cfollow.Following AS CF';
		}//end rid
	}//end all comments

	//We've got the initial select statememt now lets get the from information
	$sql.=" FROM ($contenttojoin) AS ids ";

	if($what=='all' || $what=='stories' || ($what=='tags' && $from!='story' && $from!='comment') || ($what=='comments' && $from!='story' && $from!='comment')){
		$sql.=<<<SQL
			LEFT JOIN stories ON stories.ID = ids.SID
			LEFT JOIN users AS sauth ON sauth.ID = stories.UserID 
SQL;
		if($rid){
			$sql.=<<<SQL
				LEFT JOIN (SELECT *
				FROM votes
				WHERE votes.UserID = $rid AND ContentType=0) AS svotes ON svotes.ContentID = stories.ID 
SQL;
			if($_SESSION['IsFollowing']){
				$sql.=<<<SQL
					LEFT JOIN (
						SELECT FollowedID, 1 AS Following
						FROM following
						WHERE FollowerID = $rid
					) AS sfollow ON sfollow.FollowedID = stories.UserID 
SQL;
			}//end $_SESSION
		}//end rid
	}//end all stories
	if($what=='all' || $what=='comments'){
		$sql.=<<<SQL
			LEFT JOIN comments ON comments.ID = ids.CID
			LEFT JOIN users AS cauth on cauth.ID = comments.UserID
			LEFT JOIN commenttext ON commenttext.ID = comments.ID 
SQL;
		if($rid){
			$sql.=<<<SQL
				LEFT JOIN (
					SELECT *
					FROM votes
					WHERE votes.UserID = $rid AND ContentType=1) AS cvotes ON cvotes.ContentID = comments.ID 
SQL;
			if($_SESSION['IsFollowing']){
				$sql.=<<<SQL
					LEFT JOIN (
						SELECT FollowedID, 1 AS Following
						FROM following
						WHERE FollowerID = $rid) AS cfollow ON cfollow.FollowedID = comments.UserID 
SQL;
			}//end $_SESSION
		}//end rid
	}//end all comments

	return $sql;
}

function createform($type, $params=""){
	if($type=='grade'){
		$divid=$params["divid"];
		$score=$params["score"];
		$message=($score=="")? "SUBMIT" : "UPDATE";
		$message=button("grade", $divid, $message, "blacktext gradeaction", "$divid.button");
		return <<<END
<form onsubmit="return controller('grade', '$divid')">
	<input id="$divid.score" type="text" size="1" maxlength="3" value="$score" />
</form>
$message
END;
	}
	elseif($type=='comment' && $rid=isloggedin()){
		$divid=$params;
		$theform="<span id=\"$divid.commentform.error\" class=\"nodisp\"></span>";
		$theform.="<form><textarea id=\"$divid.comment\" cols=\"50\" rows=\"2\"></textarea>";
		$theform.="<p><input id=\"$divid.nsfw\" type=\"checkbox\" value=\"nsfw\"/> Not Safe For Work? Check only if your link contains highly offensive or pornographic materials.</p>";
		//provide option to submit anonymously
		$checked=($_SESSION['ShowStories']==0)? 'checked': '';
		$theform.="<p><input id=\"$divid.anon\" type=\"checkbox\" value=\"anonymous\" $checked/> Optional: Submit Anonymously.</p>";
		
		//if student provide options for submitting to class
		if($_SESSION['IsStudent']==1){
			if(!$contents=getcontents("classesattendingoptions", "self", $_SESSION['UserName'])){
				$db=usedb();
				$result=mysqli_query($db, $sql="UPDATE users SET IsStudent = 0 WHERE ID = $rid");
				mysqli_close($db);
				if(!$result) emailadminerror('msqlerror', array('location'=>"commentformclassoptions", 'query'=>$sql));
				$_SESSION['IsStudent']=0;
				$theform.="<input id=\"$divid.forclass\" type=\"hidden\" value=\"0\" />";
			}
			else $theform.="<select id=\"$divid.forclass\" class=\"inline\" size=\"1\">$contents</select> Optional: Submit For Class.<br>";
		}
		else $theform.="<input id=\"$divid.forclass\" type=\"hidden\" value=\"0\" />";
		//turing test
		$theform.="<span class=\"isaccessible\"><input id=\"$divid.isaccessible\" type=\"checkbox\">Check this box if you are not using software designed to assist with a visual impairment and CSS is activated.<br></span>";
		$theform.="<input class=\"nodisplay\" id=\"$divid.submit\" type=\"submit\" value=\"Submit Comment\" class=\"submit\"/>";
		return $theform."</form>".button("commentsubmit", $divid, "SUBMIT COMMENT");
	}
	elseif($type=='tags' && $rid=isloggedin()){
		$divid=$params;
		return<<<END
<form id="$divid.form" class="inline" action="#" method="post" onSubmit="return controller('tag', '$divid.adds');">
	<input id="$divid.thetags" type="text" size="27" maxlength="60" value="separate, tags, with, commas" />
	<input class="nodisplay" id="$divid.submit" type="submit" value="Add The Tags!" class="submit"/>
</form>
END;
	}
	elseif($type=='submitstory' && $rid=isloggedin()){
		$title=$params['title'];
		$summary=$params['summary'];
		$titlebad=$summarybad=FALSE;
		$_SESSION['rule']=$params['rule'];
		$_SESSION['storyextra']=$params['extra'];
		if(regexchecker($title, 'taboo') || (count(array_intersect(explode(" ", strtolower($title)), badwords()))>0))$titlebad=TRUE;
		if(regexchecker($summary, 'taboo') || (count(array_intersect(explode(" ", strtolower($summary)), badwords()))>0))$summarybad=TRUE;
		if($titlebad || $summarybad){
			$theform="<span id=\"storysubmitform.error\" class=\"caution\">The ";
			if($titlebad){
				$theform.='title ';
				if($summarybad)$theform.='and the summary ';
			}
			elseif($summarybad)$theform.='summary ';
			$theform.='contain profanity, please remove the profanity before submitting this story.  Thank you.</span>';
		}
		else $theform="<span id=\"storysubmitform.error\" class=\"nodisplay\"></span>";
		$theform.=<<<END
<form id="storysubmitform" class="inline" action="#" method="post" onSubmit="return controller('story', 'storysubmitform.save');">
END;
		if($title==''){
			$theform.=<<<END
<p>We were unable to extract a title from this story automatically, please submit one.</p>
<input id="storysubmitform.title" type="text" size="75" maxlength="120"/>
END;
		}
		else{
			$theform.=<<<END
<p><b>$title</b><br>
<input class="nodisplay" id="storysubmitform.title" type="text" size="75" maxlength="120" value="$title"/>
<span id="storysubmitform.title.button" class="clickable_span participate" onclick="return controller('edit', 'storysubmitform.title.show');">EDIT TITLE</span></p>
END;
		}
		if($summary=='' || $summary=='...'){
			$theform.=<<<END
<p>We were unable to extract a summary from this story automatically, please submit one.</p>
<textarea id="storysubmitform.summary" cols="60" rows="7"></textarea>
END;
		}
		else{
			$theform.=<<<END
<p><b>$summary</b><br>
<textarea class="nodisplay" id="storysubmitform.summary" cols="60" rows="7">$summary</textarea>
<span id="storysubmitform.summary.button" class="clickable_span participate" onclick="return controller('edit', 'storysubmitform.summary.show');">EDIT SUMMARY</span></p>
END;
		}
		$theform.="<p><input id=\"storysubmitform.nsfw\" type=\"checkbox\" value=\"nsfw\"/> Please, check this if the story or the site linked to the story is Not Safe For Work.</p>";
		//provide option to submit anonymously
		$checked=($_SESSION['ShowStories']==0)? "checked": "";
		$theform.="<p><input id=\"storysubmitform.anon\" type=\"checkbox\" value=\"anonymous\" $checked/> Optional: Submit Anonymously.</p>";
		
		//if student provide options for submitting to class
		if($_SESSION['IsStudent']==1){
			if(!$contents=getcontents("classesattendingoptions", "self", $_SESSION['UserName'])){
				$db=usedb();
				$result=mysqli_query($db, $sql="UPDATE users SET IsStudent = 0 WHERE ID = $rid");
				mysqli_close($db);
				if(!$result) emailadminerror('msqlerror', array('location'=>"commentformclassoptions", 'query'=>$sql));
				$_SESSION['IsStudent']=0;
				$theform.="<input id=\"storysubmitform.forclass\" type=\"hidden\" value=\"0\" />";
			}
			else $theform.="<select id=\"storysubmitform.forclass\" class=\"inline\" size=\"1\">$contents</select>Optional: Submit For Class.<br>";
		}
		else $theform.="<input id=\"storysubmitform.forclass\" type=\"hidden\" value=\"0\" />";
		
		//turing test
		$theform.="<span class=\"isaccessible\"><input id=\"storysubmitform.isaccessible\" type=\"checkbox\">Check this box if you are not using software designed to assist with a visual impairment and CSS is activated.<br></span>";
		$theform.="<input id=\"storysubmitform.url\" type=\"hidden\" value=\"".$params['canonicalurl']."\" />";
		$theform.="<input id=\"storysubmitform.rule\" type=\"hidden\" value=\"".$params['rule']."\" />";
		$theform.="<input class=\"nodisplay\" id=\"storysubmitform.submit\" type=\"submit\" value=\"Submit Story\" class=\"submit\"/>";
		return $theform.button("story", 'storysubmitform.save', "SUBMIT STORY")."</form>";
	}
	elseif($type=='submitforclass'){
		$cid=$params['cid'];
		$ctype=$params['ctype'];
		$classops=$params['classops'];
		$existinglink=$params['existinglink'];
		$divid="$ctype.$cid.author.submitforclass";//form
		$theform="<span id=\"$divid.error\" class=\"";
		$theform.=($existinglink)? 'caution' : 'nodisp';
		$theform.="\">";
		$theform.=($existinglink)? "Warning, if you change the class you submit this for, you will lose any points already associated with this submission.<br><br>" : '';
		$theform.="</span>\n<form id=\"$divid\">\n\t";
		if($classops===0){//app logic should stop us from this being true, but it's here just in case
			$theform.="<p>".button('class', "class.taking.form", 'Want to submit for a class, join one!')."</p>\n\t<input id=\"$divid.forclass\" type=\"hidden\" value=\"0\" />\n\t";
		}else $theform.="<select id=\"$divid.options\" class=\"inline\" size=\"1\">$classops</select> Optional: Submit For Class.<br><br>\n\t";
		$theform.="<span class=\"isaccessible\"><input id=\"$divid.isaccessible\" type=\"checkbox\"> Check this box if you are not using software designed to assist with a visual impairment and CSS is activated.<br></span>\n";
		return $theform."</form>\n\t".button("submitforclass", "$divid.save", "SUBMIT CHANGES");
	}
	elseif($type=='url'){
		return<<<END
				<form class="pnav" id="urlform" action="#" method="post" onSubmit="return controller('story', 'pupcontents.form')">
					<input id="urlform.url" type="text" size="40" maxlength="500" value="Submit a link..." onclick="clearonclick(this, 'Submit a link...')" onblur="restoreonblur(this, 'Submit a link...')"/>
					<input class="nodisplay" id="urlform.submit" type="submit" value="Submit This URL!" class="submit"/>
				</form>	
END;
	}
	elseif($type=='class'){
		if($params=='teaching'){
			$button=button('class', 'classform.open', 'START THIS CLASS');
			return<<<END
	All fields are required.<br><br>
	<span id="classform.error" class="nodisp"></span>
	<form id="classform" action="#" method="post" onSubmit="return controller('class','classform.open');">
		<input id="classform.name" type="text" size="30" maxlength="50" value="" /> Class Name<br><br>
		<input id="classform.password1" type="password" size="20" maxlength="30" value="" /> Password <br><br>
		<input id="classform.password2" type="password" size="20" maxlength="30" value="" /> Password Again <br><br>
		<input id="classform.visibility" type="checkbox" value="visible" checked="checked"> Would you like this class to appear on your classes page? <br><br>
		$button<br>
		<input class="nodisplay" id="classform.submit" type="submit" value="Start This Class!" class="submit"/>
	</form>	
END;
		}
		else{//they're wanting to take a class
			$classid=(isvalididnum($params))? $params : "";
			$challenge=havechallenge();
			$button=button('class', 'classform.join', 'JOIN THIS CLASS');
		return<<<END
	All fields are required.<br><br>
	<span id="classform.error" class="nodisp"></span>
	<form id="classform" action="#" method="post" onSubmit="return controller('class','classform.join');">
		<input id="classform.classid" type="text" size="10" maxlength="30" value="$classid" /> Class ID<br><br>
		<input id="classform.password" type="password" size="10" maxlength="30" value="" /> Password<br><br>
		<input id="classform.displayname" type="text" size="30" maxlength="50" value="" /> Name your instructor will see (e.g. Lastname, Firstname).<br><br>
		<input id="classform.challenge" type="hidden" value="$challenge" />
		$button<br>
		<input class="nodisplay" id="classform.submit" type="submit" value="Join This Class!" class="submit"/>
	</form>
END;
		}
	}
	elseif($type=='login'){
		$button=button('login', 'login.open', 'LOG IN');
		$funame=button('login', 'login.funm', 'User Name');
		$fpword=button('login', 'login.fpwd', 'Password');
		$regis=button('login', 'login.rfrm', 'Register');
		$challenge=havechallenge();
		return <<<END
	<span id="login.error" class="nodisp"></span>
	<form id="login.form" action="#" method="post" onSubmit="return controller('login','login.open');">
		<input id="login.username" type="text" size="10" maxlength="30" value="" /> Username*<br><br>
		<input id="login.password" type="password" size="10" maxlength="30" value="" /> Password*<br><br>
		<input id="login.challenge" type="hidden" value="$challenge" />
		$button<br>
		<input class="nodisplay" id="login.submit" type="submit" value="Log In" class="submit"/>
	</form>
	<br>
	Forgot your $funame or your $fpword?  Would you like to $regis for an account?
END;
	}
	elseif($type=='register'){
		$button=button('login', 'login.join', 'REGISTER');
		$toslink=createlink('tos', '', 'Terms of Service', '', 'new');
		return<<<END
	Fields marked with an asterisk are required.<br><br>
	Only registered users can submit, comment on, or tag stories.<br><br>
	<span id="register.error" class="nodisp"></span>
	<form id="register.form" action="#" method="post" onSubmit="return controller('login','login.register');">
		<input id="register.username" type="text" size="20" maxlength="30" value="$username" /> User Name*<br><br>
		<input id="register.password1" type="password" size="20" maxlength="30" value="" /> Password* <br><br>
		<input id="register.password2" type="password" size="20" maxlength="30" value="" /> Password Again* <br><br>
		<input id="register.email" type="text" size="20" maxlength="60" value="$email" /> Email Address* <br><br>
		<input id="register.displayname" type="text" size="30" maxlength="60" value="$firstname" /> Displayed Name* (e.g. Ms. Awesome R. Collabor8r Jr.)<br><br>
		<input id="register.affiliation" type="text" size="20" maxlength="60" value="$affiliation" /> University or Other Affiliation<br><br>
		<p><input id="register.anon" type="checkbox" value="anonymous"/> Default to submitting stories anonymously.</p>
		<p><input id="register.nsfw" type="checkbox" value="nsfw" checked="checked"/> Filter NSFW stories and comments.</p>
		<p><input id="register.agree" type="checkbox" value="agree"/>* I agree to the $toslink.</p>
		$button<br>
		<input class="nodisplay" id="register.submit" type="submit" value="Register" class="submit"/>
	</form>	
END;
	}
	elseif($type=='efrm'){
		$etype=$params['etype'];
		if($etype=='newuser'){
			$uname=$params['username'];
			$button=button('login', 'login.efrm', 'COMPLETE REGISTRATION');
			return<<<END
	You're close to completing the registration process.
	<p>Please check your email for the authentication code and enter it below.</p>
	<span id="efrm.error" class="nodisp"></span>
	<form id="efrm.form" action="#" method="post" onSubmit="return controller('login','login.efrm');">
		<input id="efrm.type" type="hidden" value="$etype" />
		<input id="efrm.username" type="hidden" value="$uname" />
		<input id="efrm.code" type="text" size="10" maxlength="10" value="" /> Authentication Code*<br><br>
		$button<br>
		<input class="nodisplay" id="efrm.submit" type="submit" value="Complete Registration!" class="submit"/>
	</form>	
END;
		}//end errortype 'newuser'
	}//end type efrm
	elseif($type=='sendusername'){
		$button=button('login', 'sendusername.sunm', 'SSQL ME MY USER NAME');
		return <<<END
	<span id="sendusername.error" class="nodisp"></span>
	<form id="sendusername.form" action="#" method="post" onSubmit="return controller('login','sendusername.sunm');">
		<input id="sendusername.email" type="text" size="20" maxlength="60" value="$email" /> Email Address* <br><br>
		$button<br>
		<input class="nodisplay" id="sendusername.submit" type="submit" value="Send Me My User Name" class="submit"/>
	</form>
END;
	}
	elseif($type=='sendpassword'){
		$button=button('login', 'sendpassword.spwd', 'RESET MY PASSWORD');
		return <<<END
	<span id="sendpassword.error" class="nodisp"></span>
	<form id="sendpassword.form" action="#" method="post" onSubmit="return controller('login','sendpassword.spwd');">
		<input id="sendpassword.username" type="text" size="10" maxlength="30" value="" /> Username*<br><br>
		<input id="sendpassword.email" type="text" size="20" maxlength="60" value="$email" /> Email Address* <br><br>
		$button<br>
		<input class="nodisplay" id="sendpassword.submit" type="submit" value="Reset My Password" class="submit"/>
	</form>
END;
	}
	elseif($type=='options'){
		if(!isloggedin()) return 'You must be logged in to change your options';
		$challenge=havechallenge();
		$uname=htmlspecialchars($_SESSION['UserName']);
		$dname=htmlspecialchars($_SESSION['DisplayName']);
		$email=htmlspecialchars($_SESSION['Email']);
		$affiliation=htmlspecialchars($_SESSION['Affiliation']);
		$showstories=$_SESSION['ShowStories'];
		$nsfw=$_SESSION['NSFW'];
		$ubut=button('options', 'options.unm', 'Change User Name');
		$showunmform=button('options', 'options.unf', 'Change User Name');
		$dbut=button('options', 'options.dnm', 'Change Display Name');
		$showpwdform=button('options', 'options.pwf', 'Change Password');
		$pbut=button('options', 'options.pwd', 'Change Password');
		$ebut=button('options', 'options.eml', 'Change E-mail');
		$eokbut=button('options', 'options.eok', 'Confirm E-mail Address');
		$showemailform=button('options', 'options.emf', 'Change E-mail');
		$abut=button('options', 'options.aff', 'Change Affiliation');
		$sbut=button('options', 'options.sho', 'Change');
		$nbut=button('options', 'options.sfw', 'Change');
		$shochecked=($showstories)? 'checked="checked"' : '';
		$nsfwchecked=($nsfw)? '' : 'checked="checked"';
		return<<<END
	<span id="options.error" class="nodisplay"></span>
	<form id="options.form" action="#" method="post" onSubmit="return false;">
		<input id="options.challenge" type="hidden" value="$challenge" />
	</form>
	<form id="options.usernamechange.form" class="nodisplay" onSubmit="return controller('options', 'options.unm');">
		<input id="options.username" type="text" size="30" maxlength="30" value="" /> Desired User Name*<br><br>
		<input id="options.usernamepassword" type="password" size="30" maxlength="60" value="" /> Current Password*<br>$ubut
		<input class="nodisplay" id="options.usernamechange.form.submit" type="submit" value="Change User Name" class="submit"/>
	</form>
	<span id="options.usernameshow.span">
		$showunmform
	</span>
	<br><br>
	<form id="options.passwordchange.form" class="nodisplay" onSubmit="return controller('options', 'options.pwd');">
		<input id="options.password" type="password" size="30" maxlength="60" value="" /> Current Password*<br><br>
		<input id="options.password1" type="password" size="30" maxlength="60" value="" /> New Password*<br><br>
		<input id="options.password2" type="password" size="30" maxlength="60" value="" /> Re-enter The New Password*<br>$pbut
		<input class="nodisplay" id="options.passwordchange.form.submit" type="submit" value="Change Password" class="submit"/>
	</form>
	<span id="options.passwordshow.span">
		$showpwdform
	</span>
	<br><br>
	<form id="options.displayname.form" onSubmit="return controller('options', 'options.dnm');">
		<input id="options.displayname" type="text" size="30" maxlength="60" value="$dname" /> $dbut
		<input class="nodisplay" id="options.displayname.form.submit" type="submit" value="Change Display Name" class="submit"/>
	</form>
	<br>
	<div id="options.emailchange.div" class="nodisplay">
		<form id="options.emailchange.form" class="nodisplay" onSubmit="return controller('options', 'options.eml');">
			<input id="options.email" type="text" size="30" maxlength="60" value="" /> New Email Address*<br><br>
			<input id="options.emailpassword" type="password" size="30" maxlength="60" value="" /> Current Password*<br>  $ebut
			<input class="nodisplay" id="options.emailchange.form.submit" type="submit" value="Change E-mail" class="submit"/>
		</form>
		<form id="options.emailconfirm.form" class="nodisplay" onSubmit="return controller('options', 'options.eok');">
			<input id="options.emailconfirm" type="text" size="30" maxlength="30" value="" /> $eokbut
			<input class="nodisplay" id="options.emailconfirm.form.submit" type="submit" value="Confirm E-mail Address" class="submit"/>
		</form>
	</div>
	<div id="options.emailshow.div">
		$showemailform
	</div>
	<br>
	<form id="options.affiliation.form" onSubmit="return controller('options', 'options.aff');">
		<input id="options.affiliation" type="text" size="30" maxlength="60" value="$affiliation" /> $abut
		<input class="nodisplay" id="options.affiliation.form.submit" type="submit" value="Change Affiliation" class="submit"/>
	</form>
	<br>
	<p><input id="options.anon" type="checkbox" value="anonymous" $shochecked/> Default to submitting stories anonymously. $sbut</p>
	<p><input id="options.nsfw" type="checkbox" value="nsfw" $nsfwchecked/> Filter NSFW stories and comments. $nbut</p>
	<input class="nodisplay" id="sendpassword.submit" type="submit" value="" class="submit"/>
END;
	//There was a </form> tag before the END; statement. I removed it because I couldn't find the original form tag
	}
	elseif($type=='report'){
		$ctype=($params['ctype']===0)? 'story' : 'comment';
		if($params['nsfw']==='1'){
			$nsfw='nsfw';
			$nsfwop="The $ctype is inappropriately marked NSFW.";
		}
		elseif($params['nsfw']==='0'){
			$nsfw='sfw';
			$nsfwop="The $ctype should be marked NSFW.";
		}
		else{//it's unknown there was a problem getting the data
			$nsfw='usfw';
			$nsfwop="The NSFW/SFW rating for the $ctype should be changed.";
		}
		$objectid=$params['objectid'];
		$button=button('email', "$objectid.rsnd", 'REPORT');
		return<<<END
		<span id="email.rfrm.error" class="nodisplay"></span>
		<form id="email.rfrm.form" onSubmit="return controller('email', '$objectid.rsnd');">
			Why are you reporting this $ctype?<br>
			<input id="email.rfrm.reason.$nsfw" name="email.rfrm.reason" type="radio" value="$nsfw" /> $nsfwop<br>
			<input id="email.rfrm.reason.spam" name="email.rfrm.reason" type="radio" value="spam" /> The $ctype is SPAM<br>
			<input id="email.rfrm.reason.porn" name="email.rfrm.reason" type="radio" value="porn" /> The $ctype is pornographic or contains links to pornography.<br>
			<span class="bumpright caution">Please make sure to provide relevant details if you are reporting this $ctype for one of the following reasons.</span><br>
			<input id="email.rfrm.reason.dupe" name="email.rfrm.reason" type="radio" value="dupe" /> The $ctype is a duplicate.<br>
			<input id="email.rfrm.reason.abuse" name="email.rfrm.reason" type="radio" value="abuse" /> The $ctype is abusive, harassing, hateful, inciting violence...<br>
			<input id="email.rfrm.reason.threat" name="email.rfrm.reason" type="radio" value="threat" /> The $ctype links to malware, phishing, or other harmful sites.<br>
			<input id="email.rfrm.reason.other" name="email.rfrm.reason" type="radio" value="other" /> Other:<br><br>Details:<br>
			<textarea id="email.rfrm.details" cols="60" rows="4" class="bumpright"/></textarea><br>
			<input class="nodisplay" id="email.rfrm.submit" type="submit" value="Report" class="submit"/>
			$button<br>
		</form>
END;
	}
	elseif($type=='search'){
		$button=button('search', 'search.find', 'SEARCH');
		$userid=isloggedin();
		$sfor=(isset($params['sfor']))? $params['sfor'] : '';
		$cmine=(isset($params['cmine']))? $params['cmine'] : '';
		$sby=(isset($params['sby']))? $params['sby'] : '';
		$tall=(isset($params['tall']))? $params['tall'] : '';
		$tany=(isset($params['tany']))? $params['tany'] : '';
		$tnone=(isset($params['tnone']))? $params['tnone'] : '';
		$tmine=(isset($params['tmine']))? $params['tmine'] : '';
		$chide=(isset($params['chide']))? $params['chide'] : '';
		$loggeddivclass=($userid)? 'bumpright' : 'nodisplay';
		
		$storiesselected=$commentsselected=$contentsselected=$tagsselected=$tmineselected='';
		
		if($sfor=='comments'){
			$commentsselected='checked';
			$sbydivclass='nodisplay';
		}
		else{//default to stories
			$storiesselected='checked';
			$sbydivclass='block';
		}
		
		$cmine=($cmine=='true')? 'checked' : '';
		$chide=($chide=='true')? 'checked' : '';
		$tmine=($tmine=='true')? 'checked' : '';
		
		$sbymytagsdivclass='nodisplay';
		if($sfor=='stories' && $sby=='tags'){
			$tagsselected='checked';
			if($userid){
				$sbymytagsdivclass='inline';
			}
		}else{//default to selecting contents
			$contentsselected='checked';
		}
		if($userid){
			$contentsaction="onclick=\"return changeCSS('tminediv', 'display', 'none');\"";
			$tagsaction="onclick=\"return changeCSS('tminediv', 'display', 'block');\"";
		}
		else $contentsaction=$tagsaction='';
		$storiesaction="onclick=\"return changeCSS('sbychoice', 'display', 'block');\"";
		$commentsaction="onclick=\"return changeCSS('sbychoice', 'display', 'none');\"";
		return <<<END
	<span id="searchform.error" class="nodisplay"></span>
	<form id="searchform" action="http://collabor8r.com/search.php" method="get" onSubmit="return controller('search', 'search.find');">
		What do you want to find? <br>
		<input type="radio" name="sfor" value="stories"$storiesaction $storiesselected> Stories <br>
		<input type="radio" name="sfor" value="comments"$commentsaction $commentsselected> Comments <br>
		<div class="verticalspacer $loggeddivclass">
			<input id="cmine" name="cmine" type="checkbox" $cmine> Restrict the search to contents that I've authored.<br>
			<input id="chide" name="chide" type="checkbox" $chide> Restrict the search to contents that I've hidden.<br>
		</div>
		<div id="sbychoice" class="$sbydivclass"><br>What would you like to search by?<br>
			<input type="radio" name="sby" value="contents"$contentsaction $contentsselected> Contents<br>
			<input type="radio" name="sby" value="tags"$tagsaction $tagsselected> Tags
			<div id="tminediv" class="verticalspacer bumpright $sbymytagsdivclass">
				<input id="tmine" name="tmine" type="checkbox" $tmine> Restrict the search to my tags.
			</div>
		</div><br>
		Find contents that <br>
			<div class="verticalspacer"><input id="tall" name="tall" type="text" size="30" maxlength="80" value="$tall" /> contain all of the previous.<br></div>
			<div class="verticalspacer"><input id="tany" name="tany" type="text" size="30" maxlength="80" value="$tany" /> may or may not contain any of the previous.<br></div>
			<div class="verticalspacer"><input id="tnone" name="tnone" type="text" size="30" maxlength="80" value="$tnone" /> does not contain any of the previous.<br></div>
			<input id="submitsearchform" type="submit" class="nodisplay" value="Search!" />
		$button<br>
	</form>
END;
	}
	
	else return FALSE;
}

function createlink($type, $params, $text, $class="", $target=""){
	$class=($class=="")? "link" : $class;
	$target=($target=="new")? "target=\"_blank\"" : "";
	if($type!='haveurl')return "<a href=\"".createurl($type, $params)."\" class=\"$class\" $target>$text</a>";
	return "<a href=\"$params\" class=\"$class\" $target>$text</a>";
}

function createurl($type, $params=array()){
	//set the page to the passed value only if it's set and greater than 1
	$page=(isset($params["page"]))? (($params["page"]>1)? "page".$params["page"]."/" : ""): "";
	//set the hidden type to hidden only if the passed value is set and equal to show
	$hidden=(isset($params['hidden']) && $params['hidden']=='show')? 'hidden/' : '';
	$base="http://collabor8r.com/";
	switch($type){
		case "user":
			return $base."users/".$params["username"]."/".$hidden.$page;
		case "classes":
			return $base."classes/";
		case "classessummary":
			return $base."users/".$params["username"]."/classes/";
		case "classesall":
			return $base."users/".$params["username"]."/classes/all/".$hidden.$page;
		case "classesstories":
			return $base."users/".$params["username"]."/classes/stories/".$hidden.$page;
		case "classescomments":
			return $base."users/".$params["username"]."/classes/comments/".$hidden.$page;
		case "classsummary":
			return $base."users/".$params["username"]."/classes/".$params["classid"]."/";
		case "classall":
			return $base."users/".$params["username"]."/classes/".$params["classid"]."/all/".$hidden.$page;
		case "classstories":
			return $base."users/".$params["username"]."/classes/".$params["classid"]."/stories/".$hidden.$page;
		case "classcomments":
			return $base."users/".$params["username"]."/classes/".$params["classid"]."/comments/".$hidden.$page;
		case "classmember":
			return $base."users/".$params["username"]."/classes/".$params["classid"]."/classmember/".$id["classmemberid"]."/".$hidden.$page;
		case "feedall":
			return $base."users/".$params["username"]."/feed/".$hidden.$page;
		case "feedstories":
			return $base."users/".$params["username"]."/feed/stories/".$hidden.$page;
		case "feedcomments":
			return $base."users/".$params["username"]."/feed/comments/".$hidden.$page;
		case "followingall":
			return $base."users/".$params["username"]."/following/".$hidden.$page;
		case "followingstories":
			return $base."users/".$params["username"]."/following/stories/".$hidden.$page;
		case "followingcomments":
			return $base."users/".$params["username"]."/following/comments/".$hidden.$page;
		case "users":
			return $base.'users/';
		case "userall":
			return $base."users/".$params["username"]."/all/".$hidden.$page;
		case "userstories":
			return $base."users/".$params["username"]."/stories/".$hidden.$page;
		case "usercomments":
			return $base."users/".$params["username"]."/comments/".$hidden.$page;
		case "external":
			return $params["link"];
		case "storylink":
			return $base."stories/".$params["link"]."/";
		case "options":
			return $base."users/".$params["username"]."/options/";
		case "submissions":
			return $base.$hidden.$page;
		case "tos":
			return $base."terms/";
		case "404":
			return $base."404/";
		case "403":
			return $base."403/";
		case "info":
			return $base.$params["type"]."/";
		case 'search':
			return $base.'search/';
		case 'base':
			return $base;
		default:
			return $base."404/";
	}//end switch type
}

function emailadminerror($errortype, $params){
	$to=emailaddress("error");
	$subject="Collabor8r: Error $errortype";
	$header="From: Collabor8r Error Detection <error@collabor8r.com>";
	$text="error:\t$errortype";
	if(!is_array($params))$text.="\n $params";
	else foreach($params as $key=>$value)$text.="\n$key:\t$value";
	mail($to,$subject,$text,$header);	
}

function emailpasswordresetcode($username, $email){//This will email a user with a given emailaddress thier username if they have an account
	$to=$email;
	$errortype = 98;
	$errorcode = generateerrorcode($username,$email);
	$db=usedb();
	//make sure the user provided input is safe
	$username=mysqli_real_escape_string($db, $username);
	$email=mysqli_real_escape_string($db, $email);
	$errorcode=mysqli_real_escape_string($db, $errorcode);
	$sql=<<<SQL
		UPDATE users
		SET users.PassWord=MD5('$errorcode'), users.ErrorType=$errortype, users.ErrorCode='$errorcode'
		WHERE (users.UserName='$username' AND users.Email='$email')
SQL;
	mysqli_query($db, $sql);
	$rows=mysqli_affected_rows($db);
	if(!$rows){
		mysqli_close($db);
		return 0;
	}else{//the update was successful so we can try to get the display name and sent the email
		$sql=<<<SQL
			SELECT users.DisplayName
			FROM users
			WHERE (users.UserName = '$username')
SQL;
		$result = mysqli_query($db, $sql);
		$rows = mysqli_num_rows($result);
		mysqli_close($db);
		if(!$rows){
			return 0;
		}else{	//if we have an author with that id
			//get the information in the result
			$assoc = mysqli_fetch_assoc($result);
			$displayname = $assoc['DisplayName'];
			//Send the email
			$subject="Collabor8r Password Reset";
			$helpaddress=emailaddress("help");
			$header="From: Collabor8r User Services <$helpaddress>";
			$text = <<<TEXT
Dear $displayname,
As per your request, we have reset your password.
Temporary Password: $errorcode
Upon logging in with this password, you will need to change your password to regain access to all of our services.
Thank you for your continued patronage.  If you ever have suggestions about our services, please don't hesitate to contact us.
The CollaboR8R Team.
If you did not request this email, please forward this it to fraud@collabor8r.com.
TEXT;
			mail($to,$subject,$text,$header);
			return 1;
		}
	}
	//if we get past the point, the email didn't get sent so return 0
	return 0;
}

function emailsendauthentication($displayname, $email, $uname, $code){
	$to=$email;
	$subject="Collabor8r Account Authentication";
	$header="From: Collabor8r Authentication <authentic8r@collabor8r.com>";

	$text = <<<TEXT
Dear $displayname,

Thank you for registering with collabor8r.com.  Your username is $uname.
Before you can begin using all of the services available on collabor8r.com, you must authenticate your account.
To do so, please login and enter the following code where prompted.
Authentication Code: $code

Thanks for your interest.  We hope you find our services useful and enjoyable.

The Collabor8r Team.

If you did not request this account, please forward this email to fraud@collabor8r.com.
TEXT;
	mail($to,$subject,$text,$header);	
}

function emailstorysubmissionerror($title,$summary,$url){
	$to=emailaddress("error");
	$subject="Collabor8r Error Submitting Story";
	$header="From: Collabor8r Error Detection <$to>";

	$text = <<<TEXT
There was a problem with a recent user submitted story.
The url of the story was $url.
The automatically selected title of the story was $title.
The automatically selected summary of the story was $summary.
TEXT;
	mail($to,$subject,$text,$header);	
}

function emailuser($type, $email, $params=''){
	$to=$email;
	$helpaddress=emailaddress("help");
	if($type=='password'){
		$subject="Your Collabor8r Password Has Been Reset";
		$header="From: Collabor8r User Services <$helpaddress>";
		$pword=$params['pword'];
		$uname=$params['uname'];
		$text = <<<END
Dear $uname,	
		
Your password has been reset to: $pword

We suggest that you change your password when you next visit collabor8r.com.

END;
	}
	elseif($type=='username'){
		$subject="Your Collabor8r User Name";
		$header="From: Collabor8r User Services <$helpaddress>";
		$dname=$params['dname'];
		$uname=$params['uname'];
		$text = <<<END
Dear $dname,	
		
Your user name is: $uname

END;
	}
	elseif($type=='verifyemail'){
		$subject="Collabor8r Verify Email";
		$header="From: Collabor8r User Services <help@collabor8r.com>";
		$dname=$params['dname'];
		$ccode=$params['ccode'];
		$text = <<<END
Dear $dname,	
		
The confirmation code is to verify this email address as $dname is: $ccode

If you are not $dname, OR

END;
	}
	else return FALSE;
	$text.=<<<END

If you did not request this email, please contact us immediately.

Sincerely,

The Collabor8r Team
END;
	mail($to,$subject,$text,$header);	
}

function emailusername($email){//This will email a user with a given emailaddress thier username if they have an account
	$to=$email;
	$db=usedb();
	$helpaddress=emailaddress("help");
	//make sure the user provided input is safe
	//This should be coming from the server and not the user, but it's better to be extra safe in case someone hacks in
	$email=mysqli_real_escape_string($db, $email);
	$sql=<<<SQL
		SELECT users.UserName, users.DisplayName
		FROM users
		WHERE (users.Email = '$email')
SQL;
	$result = mysqli_query($db, $sql);
	$rows = mysqli_num_rows($result);
	mysqli_close($db);
	if(!$rows)return 0;
	//if we have an author with that id
	else{
		//get the information in the result
		$assoc = mysqli_fetch_assoc($result);
		$username = $assoc['UserName'];
		$displayname = $assoc['DisplayName'];
		$subject="Collabor8r Username";
		$header="From: Collabor8r User Services <$helpaddress>";
		$text = <<<TEXT
Dear $displayname,
Your username is $username.
Thank you for your continued patronage.  If you ever have suggestions about our services, please don't hesitate to contact us.
The CollaboR8R Team.
If you did not request this email, please forward this it to fraud@collabor8r.com.
TEXT;
		mail($to,$subject,$text,$header);
		return 1;
	}//if we get past the point, the email didn't get sent so return 0
	return 0;
}

function fillpostarray($action){
	$temparr=array();
	//get the contents of the post
	@extract($_POST);
	//echo "success the contents of the post were ";
	//var_dump($_POST);
	if($action=='class'){
		$changeobjectid=stripslashes($changeobjectid);
		$temparr['req_changeobjectid']=$changeobjectid;
		$changeobjectid=explode('.', $changeobjectid);//e.g. class.id.form, class.id.student.teaching.drop, class.id.student.name.drop, class.taking.form
		$length=count($changeobjectid);
		$optype=$changeobjectid[$length-1];
		$temparr['req_optype']=$optype;//ops: frmo, frmj, open, drop, join
		if($optype=='drop'){
			$temparr['req_classid']=$changeobjectid[1];//id is a classid (e.g. 4), 'taking' for students, 'teaching' for instructors
			$temparr['req_what']=$changeobjectid[$length-3];//class or student
			$temparr['req_whatid']=$changeobjectid[$length-2];//same as req_classid or a student name
		}
		elseif($optype=='form'){
			$temparr['req_classid']=$changeobjectid[$length-2];//id is a classid (e.g. 4), 'taking' for students, 'teaching' for instructors
		}
		elseif($optype=='open'){
			$temparr['req_classname']=stripslashes($classname);
			$temparr['req_password1']=stripslashes($password1);
			$temparr['req_password2']=stripslashes($password2);
			$temparr['req_visibility']=stripslashes($visibility);
		}
		elseif($optype=='join'){
			$temparr['req_classid']=stripslashes($classid);
			$temparr['req_userresponse']=stripslashes($userresponse);
			$temparr['req_displayname']=stripslashes($displayname);
			$temparr['req_changeobjectid']=stripslashes($changeobjectid);
		}
	}
	elseif($action=='login'){
		$changeobjectid=stripslashes($changeobjectid);
		$temparr['req_changeobjectid']=$changeobjectid;
		$changeobjectid=explode('.', $changeobjectid);//e.g. class.id.form, class.id.student.teaching.drop, class.id.student.name.drop, class.taking.form
		$length=count($changeobjectid);
		$optype=$changeobjectid[$length-1];
		$temparr['req_optype']=$optype;//ops: open, shut, rfrm, lfrm, efrm, join
		if($optype=='open'){//login
			$temparr['req_uname']=strtolower(stripslashes($username));
			$temparr['req_userresponse']=stripslashes($userresponse);
		}
		elseif($optype=='shut' || $optype=='lfrm' || $optype=='rfrm' || $optype=='funm' || $optype=='fpwd'){//logout, get login form: don't need to do anything
		}
		elseif($optype=='efrm'){//deal with error
			$temparr['req_uname']=strtolower(stripslashes($username));
			$temparr['req_etype']=stripslashes($etype);
			$temparr['req_code']=stripslashes($code);
		}
		elseif($optype=='join'){//submit registration form
			$temparr['req_uname']=strtolower(stripslashes($username));
			$temparr['req_pword']=stripslashes($pword);
			$temparr['req_email']=stripslashes($email);
			$temparr['req_dname']=stripslashes($displayname);
			$temparr['req_affil']=stripslashes($affiliation);
			$temparr['req_anon']=stripslashes($anon);
			$temparr['req_nsfw']=stripslashes($nsfw);
			$temparr['req_agree']=stripslashes($agree);
		}
		elseif($optype=='sunm'){
			$temparr['req_email']=stripslashes($email);
		}
		elseif($optype=='spwd'){
			$temparr['req_uname']=strtolower(stripslashes($username));
			$temparr['req_email']=stripslashes($email);
		}
	}
	elseif($action=='commentsshow'){
		$changeobjectid=stripslashes($changeobjectid);
		$temparr['req_changeobjectid']=$changeobjectid;
		$changeobjectid=explode('.', $changeobjectid); //e.g. comment.12.votes
		$temparr['req_contenttype']=$changeobjectid[0];
		$temparr['req_storyid']=$changeobjectid[1];
	}
	elseif($action=='commentform'){
		$changeobjectid=stripslashes($changeobjectid);
		$temparr['req_parentdivid']=str_replace('.reply', '', $changeobjectid);
		$changeobjectid=explode('.', $temparr['req_parentdivid']); //e.g. story.12.comments or story.12.comment.3
		$temparr['req_contenttype']=$changeobjectid[0];
		$temparr['req_storyid']=$changeobjectid[1];
		if(($temparr['req_length']=count($changeobjectid))==4)$temparr['req_commentid']=$changeobjectid[3];//we have comment information also
	}
	elseif($action=='commentsubmit'){
		$changeobjectid=stripslashes($changeobjectid);
		$temparr['req_changeobjectid']=$changeobjectid;
		$changeobjectid=explode('.', $changeobjectid); //e.g. story.12.comments or story.12.comment.3
		$temparr['req_contenttype']=$changeobjectid[0];
		$temparr['req_storyid']=$changeobjectid[1];
		if(($temparr['req_length']=count($changeobjectid))==4)$temparr['req_commentid']=$changeobjectid[3];//we have comment information also
		$temparr['req_comment']=stripslashes($comment);
		$temparr['forclass']=$forclass;
		$temparr['req_anon']=$anon;
		$temparr['req_nsfw']=$nsfw;
		$temparr['req_access']=$access;
	}
	elseif($action=='authanon'){
		$changeobjectid=stripslashes($changeobjectid);
		$temparr['req_changeobjectid']=$changeobjectid;
		$changeobjectid=explode('.', $changeobjectid);//e.g. story.12.author.show or story.12.comment.3.author.hide
		$length=count($changeobjectid);
		$temparr['req_contenttype']=$changeobjectid[$length-4];
		$temparr['req_contentid']=$changeobjectid[$length-3];
		$temparr['req_optype']=$changeobjectid[$length-1];
	}
	elseif($action=='follow'){
		$changeobjectid=stripslashes($changeobjectid);
		$temparr['req_changeobjectid']=$changeobjectid;
		$changeobjectid=explode('.', $changeobjectid);//e.g. story.12.author.start or story.12.comment.3.author.cease
		$length=count($changeobjectid);
		$temparr['req_contenttype']=$changeobjectid[$length-4];
		$temparr['req_contentid']=$changeobjectid[$length-3];
		$temparr['req_optype']=$changeobjectid[$length-1];
	}
	elseif($action=='grade'){
		$changeobjectid=stripslashes($changeobjectid);
		$temparr['req_changeobjectid']=$changeobjectid;
		$changeobjectid=explode('.', $changeobjectid);//e.g. story.43.grade.12 or story.43.comment.13.grade.4
		$length=count($changeobjectid);
		$temparr['req_contenttype']=$changeobjectid[$length-4];
		$temparr['req_contentid']=$changeobjectid[$length-3];
		$temparr['req_gid']=$changeobjectid[$length-1];
		$temparr['req_score']=stripslashes($score);
	}
	elseif($action=='hide'){
		$changeobjectid=stripslashes($changeobjectid);
		$temparr['req_changeobjectid']=$changeobjectid;
		$changeobjectid=explode('.', $changeobjectid);//e.g. story.43.hidep or story.43.comment.13.showp
		$length=count($changeobjectid);
		$temparr['req_contenttype']=$changeobjectid[$length-3];
		$temparr['req_contentid']=$changeobjectid[$length-2];
		$temparr['req_optype']=$changeobjectid[$length-1];
	}
	elseif($action=='tag'){
		$changeobjectid=stripslashes($changeobjectid);
		$temparr['req_changeobjectid']=$changeobjectid;
		$changeobjectid=explode('.', $changeobjectid);//e.g. story.43.tags.grab or story.43.tags.2.adds
		$length=count($changeobjectid);
		$optype=$changeobjectid[$length-1];
		$temparr['req_optype']=$optype;
		if($optype=='grab' || $optype=='adds')$temparr['req_storyid']=$changeobjectid[$length-3];//getting 1 storyid e.g. story.43.tags.grab 
		else $temparr['req_storyid']=$changeobjectid[$length-4];//getting 1 storyid e.g. story.43.tags.2.adds
		if($optype=='adds')$temparr['req_tags']=stripslashes($thetags);//getting tags
		elseif($optype=='add1' || $optype=='rem1')$temparr['req_tags']=$changeobjectid[$length-2];//getting 1 tagid
	}
	elseif($action=='story'){
		$changeobjectid=stripslashes($changeobjectid);
		$temparr['req_changeobjectid']=$changeobjectid;
		$changeobjectid=explode('.', $changeobjectid);//e.g. story.43.tags.grab or story.43.tags.2.adds
		$length=count($changeobjectid);
		$optype=$changeobjectid[$length-1];
		$temparr['req_optype']=$optype;
		if($optype=='save'){
			$temparr['req_title']=stripslashes($title);
			$temparr['req_summary']=stripslashes($summary);
			$temparr['req_nsfw']=stripslashes($nsfw);
			$temparr['req_anon']=stripslashes($anon);
			$temparr['req_forclass']=stripslashes($forclass);
			$temparr['req_access']=stripslashes($access);
			$temparr['req_url']=stripslashes($url);
			$temparr['req_rule']=stripslashes($rule);
		}
		if($optype=='form'){
			$temparr['req_url']=stripslashes($url);
		}
	}
	elseif($action=='vote'){
		$changeobjectid=stripslashes($changeobjectid);
		$temparr['req_changeobjectid']=$changeobjectid;
		$changeobjectid=explode('.', $changeobjectid); //e.g. story.2.votes.sig.del story.2.comment.12.votes.sig.sig
		$length=count($changeobjectid);
		$optype=$changeobjectid[$length-1];
		if($optype=='log') return false;
		$temparr['req_optype']=$optype;
		$temparr['req_contenttype']=$changeobjectid[$length-5];
		$temparr['req_contentid']=$changeobjectid[$length-4];
	}
	elseif($action=='options'){
		$optype=$temparr['req_optype']=stripslashes($optype);//unm dnm pwd eml eok aff sho sfw
		if($optype=='unm'){
			$temparr['req_uname']=stripslashes($username);
			$temparr['req_userresponse']=stripslashes($userresponse);
		}
		elseif($optype=='dnm'){
			$temparr['req_dname']=stripslashes($dname);
		}
		elseif($optype=='pwd'){
			$temparr['req_userresponse']=stripslashes($userresponse);
			$temparr['req_newpword']=stripslashes($newpword);
		}
		elseif($optype=='eml' || $optype=='eok'){
			$temparr['req_email']=stripslashes($email);
			$temparr['req_userresponse']=stripslashes($userresponse);
			if($optype=='eok')$temparr['req_confirmation']=stripslashes($confirmation);
		}
		elseif($optype=='aff'){
			$temparr['req_affiliation']=stripslashes($affiliation);
		}
		elseif($optype=='sho'){
			$temparr['req_checked']=stripslashes($anon);
		}
		elseif($optype=='sfw'){
			$temparr['req_checked']=stripslashes($nsfw);
		}
		//echo 'failure'.var_export($temparr, TRUE);
	}
	elseif($action=='email'){
		$changeobjectid=stripslashes($changeobjectid);
		$temparr['req_changeobjectid']=$changeobjectid;
		$optype=$temparr['req_optype']=stripslashes($optype);//rfrm cfrm rsnd csnd
		if($optype=='rfrm' || $optype=='rsnd'){
			$changeobjectid=explode('.', $changeobjectid); //e.g. story.12.comment.13.rfrm
			$length=count($changeobjectid);
			$temparr['req_contenttype']=$changeobjectid[$length-3];
			$temparr['req_contentid']=$changeobjectid[$length-2];
			if($optype=='rsnd'){
				$temparr['reason']=stripslashes($reason);//not required
				$temparr['details']=stripslashes($details);//not required
			}
		}
		if($optype=='csnd'){
			$temparr['req_name']=(isset($name))? stripslashes($name) : ((isset($_SESSION['UserName']))? $_SESSION['UserName'] : '');
			$temparr['req_email']=(isset($email))? stripslashes($email) : ((isset($_SESSION['Email']))? $_SESSION['Email'] : '');
			$temparr['req_details']=stripslashes($details);
		}
	}
	elseif($action=='search'){
		$temparr['req_optype']=stripslashes($optype);
	}
	elseif($action=='submitforclass'){
		$changeobjectid=stripslashes($changeobjectid);
		$temparr['req_changeobjectid']=$changeobjectid;
		$changeobjectid=explode('.', $changeobjectid); //e.g. story.12.author.submitforclass.form or story.12.comment.3.author.submitforclass.save
		$length=count($changeobjectid);
		$temparr['req_contenttype']=$changeobjectid[$length-5];
		$temparr['req_contentid']=$changeobjectid[$length-4];
		$temparr['req_optype']=$changeobjectid[$length-1];
		if($temparr['req_optype']=='save'){
			$temparr['req_submitfor']=stripslashes($submitfor);
		}
	}
	elseif($action=='changeclassmembername'){
		$changeobjectid=stripslashes($changeobjectid);
		$temparr['req_changeobjectid']=$changeobjectid;
		$changeobjectid=explode('.', $changeobjectid); //e.g. class.12.stud.4.form or class.12.stud.4.save should have save only
		$length=count($changeobjectid);
		$temparr['req_classid']=$changeobjectid[$length-4];
		$temparr['req_classmemberid']=$changeobjectid[$length-2];
		$temparr['req_optype']=$changeobjectid[$length-1];
		if($temparr['req_optype']=='save'){
			$temparr['req_newname']=stripslashes($newname);
		}
	}
	//echo 'failure'.var_export($temparr, TRUE);
	//echo '<br>the temp array is:';
	//var_dump($temparr);
	return $temparr;
}

function formatcontent($assoc, $what, $from, $rid, $classcheck, $extra=''){
	//used to format a single contenttype
	//what is simplified so don't just pass in what from formatcontents
	if($what=='story'){
		$sdivid='story.'.$assoc['SID'];
		$stitle=htmlspecialchars($assoc['Title']);
		$surl=$assoc['URL'];
		$slink=$assoc['InternalLink'];
		$stext=htmlspecialchars($assoc['SText']);
		$snsfw=$assoc['SNSFW'];
		$shide=(isset($assoc['SHide']))? $assoc['SHide'] : 0;
		$sextra=(isset($assoc['Extra']))? $assoc['Extra'] : '';
		
		$bodyclass='contentbody';
		//author and grade information
		$gradechunk="";
		//set the default parameters and change them if we need to
		$authparams=array("divid"=>$sdivid, "adname"=>htmlspecialchars($assoc["SDName"]), "auname"=>$assoc["SUName"], "priv"=>$assoc["SPrivacy"], "time"=>timeago($assoc["SCreation"]));//timeago from r8rfuncs.contents
		if($rid){
			//get author relationship
			if($_SESSION['UserName']==$assoc["SUName"]) $authparams["arel"]="self";
			elseif($assoc["SF"]) $authparams["arel"]="following";
			else $authparams["arel"]=FALSE;
			
			//get class relationship
			if($classcheck && !is_null($assoc["SCID"])){//we have class info about the story
				$scownerid=$assoc["SCOwnerID"];
				if($scownerid==$rid || $assoc["SCMUID"]==$rid){//then we can see the slink info
					$bodyclass='contentbodywithgrade';
					//grade
					$grade=$assoc["Sgrade"];
					$divid="$sdivid.grade.".$assoc["SlinkID"];
					$class=(is_null($grade))? "grade gradeneed" : "grade";
					$params=array("divid"=>$divid, "grade"=>$grade);
					$gradechunk="\n".opendiv("grade", $divid, $class).formatcontent($params, "grade", $from, $rid, $scownerid, $extra).closedivs(1);
					
					//author
					$authparams["cname"]=htmlspecialchars($assoc["SCName"]);
					$authparams["cid"]=$assoc["SCID"];
					$authparams["crel"]=($scownerid==$rid)? "instructor" : "student";
				}//end check to see if submission was by user or for users class
				else $authparams["crel"]=FALSE;
			}//end check to see if user has or is in classes
			else $authparams["crel"]=FALSE;
		}
		else{
			$authparams["arel"]=FALSE;
			$authparams["crel"]=FALSE;
		}
		$authorchunk=formatcontent($authparams, "author", $from, $rid, $classcheck, $extra);
		$authorchunk="\n<div id=\"$sdivid.author\" class=\"author\">$authorchunk";
		
		//title and body
		$titlechunk=($snsfw)? '<span class="caution">NSFW:</span> ' : '';
		$titlechunk="\n<div id=\"$sdivid.title\" class=\"contenttitle\">$titlechunk".createlink("external", array("link"=>$surl), $stitle, "", "new")."</div>";
		if($from=='story') $bodychunk="\n<div id=\"$sdivid.body\" class=\"$bodyclass\">$stext</div>";
		else $bodychunk="\n<div id=\"$sdivid.body\" class=\"$bodyclass\">".createlink("storylink", array("link"=>$slink), $stext, "covertlink")."</div>";
		
		//add option to show tags if appropriate
		if($from=="story")$authorchunk.=" ".button("tag", "$sdivid.tags.show", "Tags", "nodisplay", "$sdivid.tags.tagbutton");
		else $authorchunk.=" ".button("tag", "$sdivid.tags.grab", "Tags", "", "$sdivid.tags.tagbutton");
		//add options related to hiding stories
		$authorchunk.=" ".button("hide", "$sdivid.hidet", "Hide", "", "$sdivid.hidet");
		if($rid){//user is logged in so we can do even more things
			//hide or restore story to feed
			if($shide) $authorchunk.=' '.button("hide", "$sdivid.showp", "Add-Back", '', "$sdivid.hidep");
			else $authorchunk.=' '.button("hide", "$sdivid.hidep", "Remove", '', "$sdivid.hidep");
			$authorchunk.=' '.button('email', "$sdivid.rfrm", "Report");
		}
		if($sextra) $authorchunk.=' '.button("hide", "$sdivid.extras.showt", "Show-Extras", '', "$sdivid.extras.alt");
		$authorchunk.="</div>";
		
		//votes
		$params=array("divid"=>"$sdivid", "sig"=>$assoc["SSig"]);
		$votechunk="\n<div id=\"$sdivid.votes\" class=\"votes\">".formatcontent($params, "vote", $from, $rid, $classcheck, $extra)."</div>";
		
		//extra
		if($sextra){
			$sextra=explode('.', $sextra);
			$hideextra=button("hide", "$sdivid.extras.hidet", "Hide-Extras", '', "$sdivid.extras.hidep");
			if($sextra[0]=='youtube')$sextra="\n<div id=\"$sdivid.extras.contents\" class=\"nodisplay\">$hideextra<br><iframe width=\"420\" height=\"315\" src=\"http://www.youtube.com/embed/".$sextra[1]."\" frameborder=\"0\" allowfullscreen></iframe></div>";
		}
		//$test=var_export($assoc, TRUE);
		return $titlechunk.$votechunk.$gradechunk.$bodychunk.$sextra.$authorchunk;
	}//end what == story
	elseif($what=="comment"){
	//get the basic comment information
		$cdivid="story.".$assoc["SID"].".comment.".$assoc["CID"];
		$ctext=htmlspecialchars($assoc["CText"]);
		$cnsfw=$assoc["CNSFW"];
		$chide=(isset($assoc["CHide"]))? $assoc["CHide"] : 0;
		
		//the body	 no title cause comment
		$bodychunk.="\n<div id=\"$cdivid.body\" class=\"contentbodycomment\">";
		$bodychunk.=($cnsfw)? "<span class=\"caution\">NSFW:</span> " : "";
		$bodychunk.="$ctext</div>";
		
		//author and grade information
		$gradechunk="";
		//set the default parameters and change them if we need to
		$authparams=array("divid"=>$cdivid, "adname"=>htmlspecialchars($assoc["CDName"]), "auname"=>$assoc["CUName"], "priv"=>$assoc["CPrivacy"], "time"=>timeago($assoc["CCreation"]));//timeago from r8rfuncs.contents
		if($rid){
			//get author relationship
			if($_SESSION['UserName']==$assoc["CUName"]) $authparams["arel"]="self";
			elseif($assoc["CF"]) $authparams["arel"]="following";
			else $authparams["arel"]=FALSE;
			
			//get class relationship
			if($classcheck && !is_null($assoc["CCID"])){//we have class info about the story
				$ccownerid=$assoc["CCOwnerID"];
				if($ccownerid==$rid || $assoc["CCMUID"]==$rid){//then we can see the slink info
					//grade
					$grade=$assoc["Cgrade"];
					$divid="$cdivid.grade.".$assoc["ClinkID"];
					$class=(is_null($grade))? "grade gradeneed" : "grade";
					$params=array("divid"=>$divid, "grade"=>$grade);
					$gradechunk="\n".opendiv("grade", $divid, $class).formatcontent($params, "grade", $from, $rid, $ccownerid, $extra).closedivs(1);
					
					//author
					$authparams["cname"]=htmlspecialchars($assoc["CCName"]);
					$authparams["cid"]=$assoc["CCID"];
					$authparams["crel"]=($scownerid==$rid)? "instructor" : "student";
				}//end check to see if submission was by user or for users class
				else $authparams["crel"]=FALSE;
			}//end check to see if user has or is in classes
			else $authparams["crel"]=FALSE;
		}
		else{
			$authparams["arel"]=FALSE;
			$authparams["crel"]=FALSE;
		}
		$authorchunk=formatcontent($authparams, "author", $from, $rid, $classcheck, $extra);
		$authorchunk="\n<div id=\"$cdivid.author\" class=\"author\">$authorchunk";
		//add options related to hiding comments
		$authorchunk.=" ".button("hide", "$cdivid.hidet", "Hide", "", "$cdivid.hidet");
		if($rid){//user is logged in so we can do even more things
			//hide or restore story to feed
			if($chide) $authorchunk.=" ".button("hide", "$cdivid.showp", "Add-Back-To-Feed", "", "$cdivid.hidep");
			else $authorchunk.=" ".button("hide", "$cdivid.hidep", "Remove-From-Feed", "", "$cdivid.hidep");
			$authorchunk.=' '.button('email', "$cdivid.rfrm", "Report");
		}
		$authorchunk.="</div>";
		
		//votes
		$params=array("divid"=>"$cdivid", "sig"=>$assoc["CSig"]);
		$votechunk="\n<div id=\"$cdivid.votes\" class=\"votes\">".formatcontent($params, "vote", $from, $rid, $classcheck, $extra)."</div>";
		
		return $votechunk.$gradechunk.$bodychunk.$authorchunk;
	}//end what == comment
	elseif($what=="tag"){//for tags we're actually passing the mysql result so we'll need to cycle through
		$mysqlresult=$assoc;
		$sid="";
		$tags=array();
		$tids=array();
		$size=array();
		$owner=array();
		$row=0;
		while($assoc=mysqli_fetch_assoc($mysqlresult)){
			if(!$row)$sid=$assoc["SID"];
			$tags[$row]=htmlspecialchars($assoc["TheText"]);
			$tids[$row]=$assoc["TID"];
			$size[$row]=$assoc["NTs"];
			if($rid)$owner[$row]=(!is_null($assoc["IsOwner"]))? 1 : 0;
			else $owner[$row]=0;
			$row++;
		}
		$nrows=$row;
		$tarr=array_count_values($size);
		if(count($tarr)>1){//we have multiple comments on one tag so get rid of any unowned tags with a single vote
			$ttags=array();
			$ttids=array();
			$tsize=array();
			$towner=array();
			$row=0;
			for($i=0; $i<$nrows; $i++){
				if($size[$i]>1 || $owner[$i]==1){
					$ttags[$row]=$tags[$i];
					$ttids[$row]=$tids[$i];
					$tsize[$row]=$size[$i];
					$towner[$row++]=$owner[$i];
				}
			}
			$nrows=$row;
			//echo "<p>we now have $nrows tags</p>";
			$tags=$ttags;
			$tids=$ttids;
			$size=$tsize;
			$owner=$towner;
		}

		//figure out how many tags we'll include
		$ntagsmax=20;
		if($nrows>$ntagsmax){//we need to do some filtering to reduce the number of tags 
			$tarr=array_count_values($owner);
			$nbyother=(isset($tarr["0"]))? $tarr["0"] : 0;
			$nbyowner=(isset($tarr["1"]))? $tarr["1"] : 0;
			//echo "<p>nbyother is $nbyother, nbyowner is $nbyowner, row is $row</p>";
			
			if($nbyowner>=$ntagsmax){//get rid of any tags not owned by the owner
				$ttags=array();
				$ttids=array();
				$tsize=array();
				$towner=array();
				$row=0;
				for($i=0; $i<$nrows; $i++){
					if($owner[$i]==1){//we have row owned by the owner
						$ttags[$row]=$tags[$i];
						$ttids[$row]=$tids[$i];
						$tsize[$row]=$size[$i];
						$towner[$row++]=$owner[$i];
					}
				}
				$nrows=$row;
				//echo "<p>we now have $nrows tags and it should have been $nbyowner which was the number by owner</p>";
				$tags=$ttags;
				$tids=$ttids;
				$size=$tsize;
				$owner=$towner;
			}
			else{//the owner doesn't have more than twenty tags so we need to get rid of some unowned tags
				$tarr=array();
				$row=0;
				for($i=0; $i<$nrows; $i++){//get the non owner tags
					if($owner[$i]==0) $tarr[$row++]=$size[$i];//we have row not owned by the owner
				}
				$trows=$row;
				//echo "<p>We had $trow rows and it should have been $nbyother which was the number by others</p>";
				$tarr=ksort(array_count_values($tarr));//get the number tags we have with key votes
				while($nbyother>$ntagsmax-$nbyowner){//pop the above array until we have less than the desired tags
					$minsize=array_pop(array_keys($tarr));
					$nmin=array_pop($tarr);
					$nbyother=$nbyother-$nmin;
				}
				$nmintokeep=$ntagsmax-$nbyowner-$nbyother;
				if($nmintokeep>0){//we have less than desired, so lets get a random nmintokeep unowned tags with minsize to keep
					$tarr=sort(array_slice(shuffle(range(0, $nmin-1)), 0, $nmintokeep));
					$nfound=$row=0;
					for($i=0; $i<$nrows; $i++){//go through all rows keeping changing some unowned minsizes to smaller
						if($size[$i]==$minsize || $owner[$i]==0){
							if($nfound++==$tarr[$row])$row++;
							else $size[$i]=0; //this will get rid of it later because it will be beneath the minimum size
						}
					}//end for
					$trows=$row;
					//echo "<p>We had $trow rows and it should have been $nmintokeep which was the number of minimum size ones to keep</p>";
				}//end if nmintokeep>0
				else{//we're golden get the minsize from the last element
					$minsize=array_pop(array_keys($tarr));
				}
				//keep everything owned or with size greater than or equal to minsize
				$ttags=array();
				$ttids=array();
				$tsize=array();
				$towner=array();
				$row=0;
				for($i=0; $i<$nrows; $i++){
					if($owner[$i]==1 || $size[$i]>=$minsize){//we have row owned by the owner or above the minsize
						$ttags[$row]=$tags[$i];
						$ttids[$row]=$tids[$i];
						$tsize[$row]=$size[$i];
						$towner[$row++]=$owner[$i];
					}
				}
				$nrows=$row;
				//echo "<p>we now have $nrows tags and it should have been $ntagsmax which was the number we wanted in total</p>";
				$tags=$ttags;
				$tids=$ttids;
				$size=$tsize;
				$owner=$towner;
			}//end getting rid of some unowned tags
		}//got all the tags we want now do some relative sizing
		 //figure out how big our tags will be
		$relativesizes=FALSE;
		$tarr=array_count_values($size);
		$tarr=count($tarr);
		$maxts=max($size);
		if(count($tarr)==1){
			foreach($size as &$value)$value=1;
			unset($value);
		}
		elseif($tarr==2){
			for($i=0; $i<$nrows; $i++){
				if($size[$i]==$maxts)$size[$i]=1.25;
				else $size[$i]=.75;
			}
		}
		else{//we have more than three values, let's do some fancier processing
			$scale=pow($maxrts,2/3);
			for($i=0; $i<$nrows; $i++)$size[$i]=sprintf("%.2f", (log($size[$i],$scale)+.5));
		}
		//whoo, hoo!	We've potentially restricted the number of tags and we've sized them appropriately, let's print the suckers!
		$tagstoreturn="";
		for($i=0; $i<$nrows; $i++){
			$thetag=$tags[$i];
			$divid="story.$sid.tags.".$tids[$i];
			$class="tag inline";
			$style="font-size:".$size[$i]."em";
			if($rid){
				$class="$class clickable_span";
				if($owner[$i]==1){
					$class="$class mytag";
					$action="onclick=\"controller('tag', '$divid.rem1')\"";
				}
				else $action="onclick=\"controller('tag', '$divid.add1')\"";
			}
			else $action="";
			//need to get the story in here somehow
			$tagstoreturn=<<<END
$tagstoreturn
<div id="$divid" class="$class" style="$style" $action>$thetag</div>
END;
		}//end for
		return $tagstoreturn;
	}//end what==tag
	elseif($what=="classessummary"){
		$sumtoreturn="";
		$sqlresults=$assoc;
		$rows = ($sqlresults)? mysqli_num_rows($sqlresults) : 0;
		if(!$rows){
			if($classcheck=="self"){
				$sumtoreturn=opendiv('sumcsinstructing', '', 'story')."<p>You currently don't have any classes.</p>".closediv("sumcsinstructing");
				$sumtoreturn.=opendiv('sumcstaking', '', 'story')."<p>You currently aren't taking any classes.</p>".closediv("sumcstaking");
			}
			else{
				$sumtoreturn=opendiv('sumcsinstructing', '', 'story')."<p>This user doesn't have any publicly visible classes.</p>".closedivs(1);
			}
		}
		else{//we have rows
			$firstrun=1;
			if($classcheck=='self') $printtotal=$ntss=$ntgss=$ntcs=$ntgcs=$ntts=0;
			while($assoc=mysqli_fetch_assoc($sqlresults)){
				$classid=$assoc["ClassID"];
				$classname=htmlspecialchars($assoc["ClassName"]);
				$classownerdname=htmlspecialchars($assoc["DisplayName"]);
				$classowneruname=$assoc["UserName"];
				$classownerid=$assoc["OwnerID"];
				$role=$assoc["Role"];
				if($classcheck=="self"){//get some other information too
					$classmemberdname=(is_null($assoc["CMemName"]))? 'NA' : htmlspecialchars($assoc["CMemName"]);//only used for classes someone is taking
					$classmemberid=(is_null($assoc["CMemName"]))? 0 : $assoc['CMemID'];//only used for classes someone is taking
					$nss=(is_null($assoc["NSs"]))? 0 : $assoc["NSs"];
					$ngss=(is_null($assoc["NGSs"]))? 0 : $assoc["NGSs"];
					$gss=(is_null($assoc["GSs"]))? 0 : $assoc["GSs"];
					$ncs=(is_null($assoc["NCs"]))? 0 : $assoc["NCs"];
					$ngcs=(is_null($assoc["NGCs"]))? 0 : $assoc["NGCs"];
					$gcs=(is_null($assoc["GCs"]))? 0 : $assoc["GCs"];
				}
				if($firstrun){//if it's our first time through open up the appropriate div
					$firstrun=0;
					$sumtoreturn=opendiv('sumcsinstructing', '', 'story');
					$firstrole=$role;
					if($classcheck=="self" && $firstrole<2){//The user isn't instructing classes
						$sumtoreturn.="<p>You currently don't have any classes.</p>".closediv("sumcsinstructing")."<br>".opendiv('sumcstaking', '', 'story');
						$sumtoreturn.=tableheaders("sumcstaking");
					}
					else{//they are instructing classes so start the tables
						if($classcheck=="self")$sumtoreturn.=tableheaders("sumcsinstructingself");
						else $sumtoreturn.=tableheaders("sumcsinstructingother");
					}
				}
				elseif($classcheck=='self' && $role!=$firstrole){//we switched roles (matters only for self
					if($printtotal>1){
						$sgclass=($ntgss<$ntss)? "class=\"gradeneed\"" : ""; //there are stories to grade
						$cgclass=($ntgcs<$ntcs)? "class=\"gradeneed\"" : ""; //there are comments to grade
						$nts=$ntss+$ntcs;
						$tgclass=($ntgss+$ntgcs<$nts)? "class=\"gradeneed\"" : "";//there are submissions to grade
						if($firstrole==2){
							$actions=getactions('classes', array('role'=>$firstrole, 'classid'=>'teaching', 'rid'=>$rid));
							$params=array('username'=>$_SESSION['UserName']);
							$storieslink=($ntss)? createlink("classesstories", $params, $ntss) : $ntss;
							$commentslink=($ntcs)? createlink("classescomments", $params, $ntcs) : $ntcs;
							$totalslink=($nts)? createlink("classesall", $params, $nts) : $nts;
							$sumtoreturn.="<tr class='totalrow'><td>TOTAL:</td><td $sgclass>$storieslink</td><td $cgclass>$commentslink</td><td $tgclass>$totalslink</td><td>$actions</td></tr>";
						}
					}
					$sumtoreturn.="</table>\n<br>".closediv("sumcsinstructing");
					$sumtoreturn.="<br>".opendiv('sumcstaking', '', 'story').tableheaders("sumcstaking");
					$printtotal=$ntss=$ntgss=$ntcs=$ntgcs=$ntts=0;
					$firstrole=$role;
				}
				//print the summary
				$actions=getactions('classes', array('role'=>$role, 'classid'=>$classid, 'rid'=>$rid));
				if($classcheck=="self"){
					$sgclass=($ngss<$nss)? "class=\"gradeneed\"" : ""; //there are stories to grade
					$cgclass=($ngcs<$ncs)? "class=\"gradeneed\"" : ""; //there are comments to grade
					$nts=$nss+$ncs;
					$tgclass=($ngss+$ngcs<$nts)? "class=\"gradeneed\"" : "";//there are submissions to grade
					if($role==2){//classesinstructing columns: classname, classid, number students, number stories, number comments, num total, and actions
						$params=array("username"=>$classowneruname, "classid"=>$classid);
						$classlink=createlink("classsummary", $params, $classname);
						$storieslink=($nss)? createlink("classstories", $params, $nss) : $nss;
						$commentslink=($ncs)? createlink("classcomments", $params, $ncs) : $ncs;
						$totalslink=($nts)? createlink("classall", $params, $nts) : $nts;
						$sumtoreturn.="\n<tr><td>$classlink ($classid)</td><td $sgclass>$storieslink</td><td $cgclass>$commentslink</td><td $tgclass>$totalslink</td><td>$actions</td></tr>";
					}
					else{//classes taking columns classname, classid, instructordname, number stories, number comments, num total and actions
						//$params=array("username"=>$classowneruname, "classid"=>$classid);
						//$classlink=createlink("class", $params, $classname);
						$ownerlink=createlink("classessummary", array('username'=>$classowneruname), $classownerdname);
						$params=array('username'=>$_SESSION['UserName'], 'classid'=>$classid);
						$classlink=($nts)? createlink("classall", $params, $classname) : $classname;
						$storieslink=($nss)? createlink("classstories", $params, $nss) : $nss;
						$commentslink=($ncs)? createlink("classcomments", $params, $ncs) : $ncs;
						$totalslink=($nts)? createlink("classall", $params, $nts) : $nts;
						$studdivid="class.$classid.stud.$classmemberid";
						$infobut=button('changeclassmembername', $studdivid.'.form', 'Change', 'inline', $studdivid.'.info.but'); //314
						$formbut=button('changeclassmembername', $studdivid.'.save', 'Update', 'inline', $studdivid.'.form.but');
						$studnamediv=<<<END
	<div id="$studdivid.info" class="inline">
		<div id="$studdivid.info.name" class="inline">$classmemberdname</div> $infobut
	</div>
	<div id="$studdivid.form" class="nodisplay">
		<form onsubmit="return controller('changeclassmembername', '$studdivid.save')">
			<input id="$studdivid.newname" type="text" size="30" maxlength="50" value="$classmemberdname" />
			<input id="$studdivid.oldname" type="hidden" value="$classmemberdname" />
		</form>
		$formbut
	</div>
END;
						$gts=$gss+$gcs;
						$sumtoreturn.="\n<tr><td>$classlink ($classid)</td><td>$ownerlink</td><td>$studnamediv</td><td $sgclass>$storieslink / $gss</td><td $cgclass>$commentslink / $gcs</td><td $tgclass>$totalslink / $gts</td><td>$actions I'm here</td></tr>";
					}
					$printtotal++;
					$ntss+=$nss;
					$ntgss+=$ngss;
					$ntcs+=$ncs;
					$ntgcs+=$ngcs;
				}//we're not checking self
				else{//we're just printing courses that someone else is instructing (3 columns classname, classid, and actions//need to get relationship to get appropriate actions
					$sumtoreturn.="\n<tr><td>$classname ($classid)</td><td>$actions</td></tr>";
				}
			}//we have no more results to fetch so close the remaining divs appropriately
			if($classcheck=='self'){
				if($printtotal>1){
					$sgclass=($ntgss<$ntss)? "class=\"gradeneed\"" : ""; //there are stories to grade
					$cgclass=($ntgcs<$ntcs)? "class=\"gradeneed\"" : ""; //there are comments to grade
					$nts=$ntss+$ntcs;
					$tgclass=($ntgss+$ntgcs<$nts)? "class=\"gradeneed\"" : "";//there are submissions to grade
				}
				if($role==2){
					if($printtotal>1){
						$actions=getactions('classes', array('role'=>$role, 'classid'=>'teaching', 'rid'=>$rid));
						$params=array('username'=>$_SESSION['UserName']);
						$storieslink=($ntss)? createlink("classesstories", $params, $ntss) : $ntss;
						$commentslink=($ntcs)? createlink("classescomments", $params, $ntcs) : $ntcs;
						$totalslink=($nts)? createlink("classesall", $params, $nts) : $nts;
						$sumtoreturn.="<tr class='totalrow'><td>TOTAL:</td><td $sgclass>$storieslink</td><td $cgclass>$commentslink</td><td $tgclass>$totalslink</td><td>$actions</td></tr>";
					}
					$sumtoreturn.="</table>\n<br>".closediv("sumcsinstructing").opendiv('sumcstaking', '', 'story')."<p>You currently aren't taking any classes.</p>".closediv("sumcstaking");
				}
				elseif($role==1){
					//Don't print details for total for courses taking cause it doesn't make sense to sum submissions over classes
					if($printtotal>1){
						$actions=getactions('classes', array('role'=>$role, 'classid'=>'taking', 'rid'=>$rid));
						/*
						$params=array('username'=>$_SESSION['UserName']);
							//NEED TO CREATE A DIFFERENT CATEGORY classes/taking to show your submissions to classes you're taking need to change get cids, etc
						$storieslink=$ntss;//($ntss)? createlink("classesstories", $params, $ntss) : $ntss;
						$commentslink=$ntcs;//($ntcs)? createlink("classescomments", $params, $ntcs) : $ntcs;
						$totalslink=$nts;//($nts)? createlink("classesall", $params, $nts) : $nts;
						$sumtoreturn.="<tr class='totalrow'><td>TOTAL:</td><td></td><td $sgclass>$storieslink</td><td $cgclass>$commentslink</td><td $tgclass>$totalslink</td><td>$actions</td></tr>";
						*/
						$sumtoreturn.="<tr class='totalrow'><td></td><td></td><td></td><td></td><td></td><td>$actions</td></tr>";
					}
					
					$sumtoreturn.="</table>\n<br>".closediv("sumcstaking");
				}
			}
			else $sumtoreturn.="</table>\n<br>".closedivs(1);//when the requester doesn't request their own page don't present any special options
		}//done with all rows
		$sumtoreturn.=createlink('classes', '', 'Learn more about classes').'.';
		return $sumtoreturn;
	}//end classessummary
	elseif ($what=="grade"){
		$grade=$assoc["grade"];
		if(is_null($grade))$grade="";
		if($classcheck==$rid){
			$params=array("divid"=>$assoc["divid"],"score"=>$grade);
			return createform("grade", $params);
		}
		return($grade=="")? "Not<br>Yet<br>Scored" : "Score:<br><div class=\"thegrade\">$grade</div>";
	}//end what == grade
	elseif ($what=="author"){
		$divid=$assoc["divid"].".author";
		$adname=htmlspecialchars($assoc["adname"]);
		$auname=$assoc["auname"];
		$priv=$assoc["priv"];
		$arel=$assoc["arel"];//relationship between author and requester self, following, none(i.e. FALSE)
		$crel=$assoc["crel"];//relationship between requester and class content was submitted for instructor, student, none(i.e. FALSE)
		
		if($rid)$runame=$_SESSION['UserName'];
		
		$chunk='Submitted';//"<div id=\"$divid.opt\" class=\"inline\">Submitted";
		
		if($crel){
			$cname=htmlspecialchars($assoc["cname"]);
			$cid=$assoc["cid"];
			$params=array("username"=>$runame, "classid"=>$cid);
		}
		
		if($arel=="self"){
			$chunk.= " By You";
			if($priv) $chunk.=' Anonymously ('.button("authanon", "$divid.show", "Reclaim", "", "$divid.anon").')';
			else $chunk.=' ('.button("authanon", "$divid.hide", "Anonymize", "", "$divid.anon").')';
			if($_SESSION['IsStudent']){
				if($crel) $chunk.=' For '.createlink("classall", $params, $cname).' ('.button('submitforclass', "$divid.submitforclass.form", 'Change').')';
				else $chunk.=' ('.button('submitforclass', "$divid.submitforclass.form", 'Submit For Class').')';
			}else $chunk.= '('.button('class', "class.taking.form", 'Submit For Class').')';
		}
		elseif($crel)$chunk.=" For Your Class ".createlink("classsummary", $params, $cname);//if we're here, we must be the instructor of the class
		elseif($arel=="following")$chunk.=($priv)? " Anonymously" : " By ".createlink("user", array("username"=>$auname), $adname)." (".button("follow", "$divid.cease", "Stop Following", "", "$divid.follow").")";
		elseif(!$arel){//it's not from you, a student, or someone you're following.  Offer option to follow
			if($priv) $chunk.=" Anonymously";
			else{
				$chunk.=" By ".createlink("user", array("username"=>$auname), $adname);
				if($rid)$chunk.=" (".button("follow", "$divid.start", "Follow", "", "$divid.follow").")";
			}
		}
		$time=$assoc["time"];
		return $chunk." -- $time\n";//</div>\n";
	}
	elseif ($what=="vote"){
		$divid=$assoc["divid"].".votes";
		$sig=$assoc["sig"];
		$sigmes="p<.05";
		$sigid="$divid.sig";
		$nsmes="n.s.";
		$nsid="$divid.nsg";
		if($rid && !is_null($sig)){//we have votes
			if($sig==1){//they voted significant
				$sigclass="votedsig";
				$sigaction="del";
				$nsclass="votefornsg";
				$nsaction="nsg";
			}
			else{//they voted nsg
				$sigclass="voteforsig";
				$sigaction="sig";
				$nsclass="votednsg";
				$nsaction="del";
			}
		}
		else{//they didn't vote
			$sigclass="voteforsig";
			$sigaction=($rid)? "sig" : "log";
			$nsclass="votefornsg";
			$nsaction=($rid)? "nsg" : "log";
		}
		return <<<END
<div id="$sigid" class="$sigclass" onclick="controller('vote', '$sigid.$sigaction')">$sigmes</div>
<div id="$nsid" class="$nsclass" onclick="controller('vote', '$nsid.$nsaction')">$nsmes</div>
END;
		}//end $what==vote
}

function formatcontents($sqlresults, $what, $from, $rid, $page=1, $extra='', $canurl=''){
	//echo "<p>In format contents what is $what, from is $from, rid is $rid, page is $page</p>";
	//what: all, stories, comments, tags, summary
	//from: self, feed, classes, class, classmember, following, user, storyname, storyid, comment, frontpage, submissions
	if($what=="all" && $from=="story"){//sqlresults is an array of results for stories, comments and tags
		$commentresults=$sqlresults["comments"];
		$tagresults=$sqlresults["tags"];
		$sqlresults=$sqlresults["stories"];
	}
	$rows = ($sqlresults)? mysqli_num_rows($sqlresults) : 0;
	//echo "<p>what is $what, from is $from, rid is $rid, and rows is $rows.</p>";
	if($what=="summary"){ //do the summary even if we have no rows
		if($from=="classes"){//actually pass the whole result set for the summary
			if($rid==$page) $classcheck="self";//using page to contain the $id information for summaries
			else $classcheck="other";
			//echo "<p>classcheck before formatcontent was $classcheck page was $page, rid was $rid </p>";
			if($classcheck=='other' && !$rows) return FALSE;
			return formatcontent($sqlresults, "classessummary", $from, $rid, $classcheck, $extra); 
		}
		elseif($from=="class"){
			if($rows){
				$chunk=tableheaders("sumclass");
				$printtotal=$ntss=$ntgss=$ntcs=$ntgcs=$ntts=0;
				while($assoc=mysqli_fetch_assoc($sqlresults)){
					$name=htmlspecialchars($assoc["MDispName"]);
					$cid=$assoc["ClassID"];
					$nss=(is_null($assoc["NSs"]))? 0: $assoc["NSs"];
					$gss=(is_null($assoc["GSs"]))? 0: $assoc["GSs"];
					$ncs=(is_null($assoc["NCs"]))? 0: $assoc["NCs"];
					$gcs=(is_null($assoc["GCs"]))? 0: $assoc["GCs"];
					$nts=$nss+$ncs;
					$gts=$gss+$gcs;
					$actions=getactions('class', array('cid'=>$cid, 'sname'=>$name, 'rid'=>$rid));
					$chunk.="<tr><td>$name</td><td>$nss</td><td>$gss</td><td>$ncs</td><td>$gcs</td><td>$nts</td><td>$gts</td><td>$actions</td></tr>";
					$printtotal++;
					$ntss+=$nss;
					$ntgss+=$gss;
					$ntcs+=$ncs;
					$ntgcs+=$gcs;
				}
				if($printtotal>1){
					$sgclass=($ntgss<$ntss)? "class=\"gradeneed\"" : ""; //there are stories to grade
					$cgclass=($ntgcs<$ntcs)? "class=\"gradeneed\"" : ""; //there are comments to grade
					$nts=$ntss+$ntcs;
					$tgclass=($ntgss+$ntgcs<$nts)? "class=\"gradeneed\"" : "";//there are submissions to grade
					$actions=getactions('class', array('sname'=>'all', 'cid'=>$cid, 'rid'=>$rid));
					$params=array('username'=>$_SESSION['UserName'], 'classid'=>$cid);
					$storieslink=($ntss)? createlink("classstories", $params, $ntss) : $ntss;
					$commentslink=($ntcs)? createlink("classcomments", $params, $ntcs) : $ntcs;
					$totalslink=($nts)? createlink("classall", $params, $nts) : $nts;
					$chunk.="<tr class='totalrow'><td>TOTAL:</td><td $sgclass>$storieslink</td><td></td><td $cgclass>$commentslink</td><td></td><td $tgclass>$totalslink</td><td></td><td>$actions</td></tr>";
				}
				$chunk.="</table>";
				return $chunk;
			}
			else return "There are no students currently in this class.";
		}
	}//end what == summary
	elseif($rows){
		$nsfwok=($rid)? $_SESSION['NSFW'] : 0;
		$output="";
		$classcheck=($_SESSION['IsInstructor']==1 || $_SESSION['IsStudent']==1)? 1 : 0;
		if($from=="story"){//getting a single story
			if($what=="stories" || $what=="all"){//do the story part
				$assoc=mysqli_fetch_assoc($sqlresults);
				$sid=$assoc["SID"];
				$stitle=htmlspecialchars($assoc["Title"]);
				$snsfw=$assoc["SNSFW"];
				$shide=(isset($assoc["SHide"]))? $assoc["SHide"] : 0;
				$storychunk=formatcontent($assoc, "story", $from, $rid, $classcheck, $extra);
				if($what=="stories") return $storychunk;
				//echo "<p>The story chunk is $storychunk.</p>";
			}
			if($what=="comments" || $what=="all"){//do the comment part
				$commentchunk="";
				$action=($rid)? "commentform" : "commentlogin";
				if($what=="all") $commentrows = ($commentresults)? mysqli_num_rows($commentresults) : 0;
				else $commentrows = ($sqlresults)? mysqli_num_rows($sqlresults) : 0;
				if($commentrows){
					$indentlevel=0;
					$rgt=1;//start right here so we don't close any divs
					while($commentrows-->0){
						if($what=="all") $assoc = mysqli_fetch_assoc($commentresults);
						else $assoc = mysqli_fetch_assoc($sqlresults);
						$cchunk=formatcontent($assoc, "comment", $from, $rid, $classcheck, $extra);
						//get the values from the assoc that we'll need to structure the tree
						$cnsfw=$assoc["CNSFW"];
						$chide=(isset($assoc["CHide"]))? $assoc["CHide"] : 0;
						$cid=$assoc["CID"];
						$scid=$assoc["SID"];
						$lft=$assoc["Lft"];
						if($rgt==1){
							$commentchunk.="<div id=\"story.$scid.comments.reply\">".button($action, "story.$scid.comments.reply", "Submit A Comment");
							$commentchunk.=" ".button("hide", "story.$scid.comments.hidet", "Hide", "", "story.$scid.comments.hidet")."</div>\n";//will work first time through
						}
						$divstoclose=$lft-$rgt;//based on previous rgt value
						if($divstoclose>0){
							$commentchunk.=closedivs($divstoclose*2);//won't close divs on first or children Times 2 cause closing the comment div and the comment.contents div
							$indentlevel=$indentlevel-$divstoclose;
						}
						$rgt=$assoc["Rgt"];
						if($indentlevel++>0)$class="comment bumpright";
						$commentbutton="<div id=\"story.$scid.comment.$cid.reply\" class=\"reply\">".button($action, "story.$scid.comment.$cid.reply", "Reply")."</div>";
						$cchunk=altwrapper("comment", "$cchunk $commentbutton", "story.$scid.comment.$cid", $rid, $cnsfw, $nsfwok, $chide, 0, FALSE);
						$commentchunk.="\n<div id=\"story.$scid.comment.$cid\" class=\"comment\">$cchunk";
					}
					$commentchunk.=closedivs($indentlevel*2);
				}//end of rows
				else{//we had no rows
					//$theassoc=var_export($assoc, TRUE);
					if($what=="all"){
						$commentbutton.="<div id=\"story.$sid.comments.reply\">".button($action, "story.$sid.comments.reply", "Submit A Comment");
						$commentbutton.=" ".button("hide", "story.$sid.comments.hidet", "Hide", "", "story.$sid.comments.hidet")."</div>\n";
					}
					else{
						$commentbutton.="<div id=\"story.$page.comments.reply\">".button($action, "story.$page.comments.reply", "Submit A Comment");
						$commentbutton.=" ".button("hide", "story.$page.comments.hidet", "Hide", "", "story.$page.comments.hidet")."</div>\n";
					}
					$commentchunk.=$commentbutton;
				}
				if($what=="comments") return $commentchunk;
				//echo "<p>The comment chunk is $commentchunk.</p>";
			}
			if($what=="tags" || $what=="all"){//do the story part
				$divid=($what=="all")? "story.$sid.tags" : "story.$page.tags";//using page to contain the story id when not all
				if($what=="all")$tagrows = ($tagresults)? mysqli_num_rows($tagresults) : 0;
				else $tagrows = ($sqlresults)? mysqli_num_rows($sqlresults) : 0;
				$tagchunk=button("tag", "$divid.hide", "Hide", "closer");
				if($tagrows){
					if($what=="all") $tagchunk.=formatcontent($tagresults, "tag", $from, $rid, $classcheck, $extra);
					else $tagchunk.=formatcontent($sqlresults, "tag", $from, $rid, $classcheck, $extra);
				}
				else $tagchunk.="There are no tags yet.";
				$tagchunk.="<br>";
				$tagchunk.=($rid)? createform("tags", "$divid") : "Please, login to submit tags.";
				if($what=="tags") return $tagchunk;
			}
			if($what=="all"){//return the appropriate contents
				//wrap the comments
				$commentchunk=altwrapper("commentgroup", $commentchunk, "story.$sid.comments", 0, 0, 0, 0, 0, TRUE);//don't need fancy options for the comments container
				$commentchunk="<div id=\"story.$sid.comments\" class=\"comments\">$commentchunk</div>";
				//wrap the tags, story, and comments
				$tagchunk=opendiv("tags", "story.$sid.tags", "tags").$tagchunk.closedivs(1);
				$allchunk=$tagchunk.$storychunk.$commentchunk;
				//$allchunk=<<<END
					//<div id="story.$sid.tagwrapper" class="tagwrapper">Tags<div id="story.$sid.tags"> $tagchunk </div>Submit A Tag</div>
					//$storychunk
					//$allchunk
//END;
				$allchunk=altwrapper("story", $allchunk, "story.$sid", $rid, $snsfw, $nsfwok, $shide, (($extra=='show')? 1 : 0), TRUE);
				
				$body=<<<END
<div id="story.$sid" class="story"> 
	$allchunk
</div>
END;
				$body.=advert('storyend');
				return array("body"=>$body, "title"=>$stitle);//getting all from stories is special
					//we return an array instead of a single string
					//we return the array to get the appropriate title for the story to display in the headers
			}
		}//end from == story
		elseif($what=="vote"){//just getting the votes
			//need divid and sig that's it from contains the divid information
			$assoc=mysqli_fetch_assoc($sqlresults);//has sig
			$assoc["divid"]=$from;
			//echo "<p>rid is $rid.</p>";
			return formatcontent($assoc, "vote", $from, $rid, $classcheck, $extra);
		}
		elseif($what=="classesattendingoptions"){
			$chunk="\n\t<option value=\"0\" selected=\"selected\">No Class Selected</option>";
			while($assoc=mysqli_fetch_assoc($sqlresults)){
				$chunk.="\n\t<option value=\"".$assoc['ID']."\">".htmlspecialchars($assoc['ClassName'])."</option>";
			}
			return $chunk;
		}
		elseif($what=="comments" && $from=="comment"){//just getting a single comment
			$assoc=mysqli_fetch_assoc($sqlresults);
			$sid=$assoc["SID"];
			$cid=$assoc["CID"];
			$divid="story.$sid.comment.$cid";
			//$theassoc=var_export($assoc, TRUE);
			$chunk=formatcontent($assoc, "comment", $from, $rid, $classcheck, $extra);
			$action=($rid)? "commentform" : "commentlogin";
			$chunk.=" <div id=\"$divid.reply\" class=\"reply\">".button($action, "story.$sid.comment.$cid.reply", "Reply")."</div>";
			$chunk="<div id=\"$divid\" class=\"comment\">".altwrapper("comment", $chunk, "$divid", $rid, 0, 0, 0, 0, TRUE)."</div>";
			return $chunk;
		}
		else{//we're getting stories and potentially comments that go with them
			$counter=0;
			$limit=($from=='search')? 30 : 15;//limit number of stories on a page make sure this is the same as in getcontents
			$allchunk="";
			do{
				$counter++;
				//get the chunk for the story
				$assoc=mysqli_fetch_assoc($sqlresults);
				$sid=$assoc["SID"];
				$snsfw=$assoc["SNSFW"];
				$shide=(isset($assoc["SHide"]))? $assoc["SHide"] : 0;
				$storychunk=formatcontent($assoc, "story", $from, $rid, $classcheck, $extra);
				
				//get the chunk for the comments if any
				//$commentchunk="<div id=\"story.$sid.comments\" class=\"comments\">";
				$commentchunk="";
				if(($what=="all" || $what=="comments") && $assoc["CID"]!=0){
					$cid=$assoc["CID"];
					$cnsfw=$assoc["CNSFW"];
					$ncs=$assoc["NCs"];
					switch($from){
						case "feed":
							$commentchunk.="Your collabor8rs have commented on this story $ncs times. Below is the most recent. ";
							break;
						case "self":
							$commentchunk.="You have commented on this story $ncs times. Below is the most recent. ";
							break;
						case "user":
							$commentchunk.="This user has commented on this story $ncs times. Below is the most recent. ";
							break;
						case "classes":
							$commentchunk.="Your students have commented on this story $ncs times. Below is the most recent. ";
							break;
						case "class":
							$commentchunk.="Your students from this class have commented on this story $ncs times. Below is the most recent. ";
							break;
						default:
							break;
					}
					$commentchunk.=button("commentsshow", "story.$sid.comments", "View All", "inline")."<div id=\"story.$sid.comment.$cid\" class=\"comment\">";
					$cchunk=formatcontent($assoc, "comment", $from, $rid, $classcheck, $extra);
					$cchunk=altwrapper("comment", $cchunk, "story.$sid.comment.$cid", $rid, $cnsfw, $nsfwok, 0, 0, TRUE);
					$commentchunk.=$cchunk.closedivs(1);
				}//end getting comments chunk
				else $commentchunk.=button("commentsshow", "story.$sid.comments", "View Comments", "inline");
				//wrap up the comment chunk
				$commentchunk=altwrapper("commentgroup", $commentchunk, "story.$sid.comments", 0, 0, 0, 0, 0, TRUE);
				$commentchunk="<div id=\"story.$sid.comments\" class=\"comments\">$commentchunk</div>";
				//put everything together
				//wrap up the tag spacer, story, and comments
				$storychunk=<<<END
<div id="story.$sid.tags" class="tags nodisplay"></div>
$storychunk
$commentchunk
END;
				$storychunk=altwrapper("story", $storychunk, "story.$sid", $rid, $snsfw, $nsfwok, $shide, (($extra=='show')? 1 : 0), TRUE);
				
				$allchunk.=<<<END
<div id="story.$sid" class="story"> 
$storychunk
</div>
END;
			}while($counter<$rows && $counter<$limit);//end do while
			if($counter>8)$allchunk.=advert('storiesend');
			$allchunk.='<div class="center">';
			if($page>1){
				$canurl=substr($canurl, 0, strrpos($canurl, '/', -6)).'/';//-6 skip pageX/ don't do more cause don't know if X has more than 1 digit
				if($page>2)$allchunk.=createlink('haveurl', $canurl.'page'.($page-1), '<--');//we have earlier pages the user could request
				else $allchunk.=createlink('haveurl', $canurl, '<--');
			}
			$allchunk.=' Page '.$page++.' ';//always show the current page
			if($rows>$limit)$allchunk.=createlink('haveurl', $canurl.'page'.$page, '-->');//we have more pages the user to request
			$allchunk.='</div>';
		}//end else (i.e. not from==story and not what==summary)
		return $allchunk;
	}//end we had rows
	else{//we had no rows do something to indicate there were no results
		if($from=="story"){
			if($what=="all" || $what=="stories") return FALSE;
			if($what=="comments"){
				$action=($rid)? "commentform" : "commentlogin";
				//$theassoc=var_export($assoc, TRUE);
				$chunk="<div id=\"story.$page.comments.reply\">There are no comments on this story yet. ";//we're getting comments directly so the story id should be stored in $page
				$chunk.=button($action, "story.$page.comments.reply", "Submit A Comment");
				$chunk.=" ".button("hide", "story.$page.comments.hidet", "Hide", "", "story.$page.comments.hidet")."</div>";
				return $chunk;
			}
			if($what=="tags"){
				$chunk=button("tag", "story.$page.tags.hide", "Hide", "closer")."There are no tags yet.<br>";
				if($rid)$chunk.=createform("tags", "story.$page.tags");
				else $chunk.="Please, login to submit tags.";
				return $chunk;
			}
		}
		else return FALSE;
	}
}//end function format contents

function generatechallenge(){
	srand();
	//create the challenge variable
	$challenge="";
	//fill the challenge variable
	for($i=0; $i<80; $i++)
	{
		$challenge.=dechex(rand(0,15));
	}
	$_SESSION['challenge']=$challenge;
	return $challenge;
}

function getactions($type, $params=array()){
	$response='';
	if($type=='class'){
		$sname=$params['sname'];
		$cid=$params['cid'];
		$rid=$params['rid'];
		if(!$rid) return 'Login for actions.';
		$tid="class.$cid.student.$sname.drop";
		if($sname=='all') $response.=button('class', $tid, 'Drop-All', '', $tid);
		else $response.=button('class', $tid, 'Drop', '', $tid);
	}
	elseif($type=='classes'){
		$role=$params['role'];
		$classid=$params['classid'];
		$rid=$params['rid'];
		if($role==2){//teaching
			if($classid=='teaching'){
				$tid='class.teaching.student.all.drop';
				$response.=button('class', $tid, 'Empty-All', '', $tid);
				$tid='class.teaching.drop';
				$response.=" ".button('class', $tid, 'Delete-All', '', $tid);
			}
			elseif(isvalididnum($classid)){
				$tid="class.$classid.student.all.drop";
				$response.=button('class', $tid, 'Empty', '', $tid);
				$tid="class.$classid.drop";
				$response.=" ".button('class', $tid, 'Delete', '', $tid);
			}
		}
		elseif($role==1){//taking
			if($classid=='taking'){
				$tid='class.taking.drop';
				$response.=button('class', $tid, 'Drop-All', '', $tid);
			}
			elseif(isvalididnum($classid)){
				$tid="class.$classid.drop";
				$response.=button('class', $tid, 'Drop', '', $tid);
			}
		}
		else{//not teaching or taking
			if($rid){
				if(isvalididnum($classid))$response.=button('class', "class.$classid.form", 'Join');
				elseif($classid=='taking')$response.=button('class', "class.$classid.form", 'Join A Class!');
			}
			else{
				if(isvalididnum($classid))$response.='Login to use classes.';
				elseif($classid=='taking')$response.='Login to use classes.';
			}
		}
	}
	return $response;
}

function getCIDs($what, $from, $ID, $rid=0, $role='', $extra=''){
	//what = stories, comments, tags
	//from = self, feed, classes, class, following, user, story, comment
	if($from=='search'){
		$sfor=$extra['sfor'];
		$matchwhat=($sfor=='stories')? 'Title, TheText' : 'TheText';
		$sby=$extra['sby'];
		$db=usedb();
		$tall=mysqli_real_escape_string($db, $extra['tall']);
		$tany=mysqli_real_escape_string($db, $extra['tany']);
		$tnone=mysqli_real_escape_string($db, $extra['tnone']);
		mysqli_close($db);
		$tmine=$extra['tmine'];
		$cmine=$extra['cmine'];
		if($tall){
			if($tany)$tany.=' '.$tall;
			else $tany=$tall;
		}
		if($sby=='contents'){
			if($tall)$tall='+'.str_replace(' ', ' +', $tall);
			if($tall && $tnone){
				$tnone='-'.str_replace(' ', ' -', $tnone);
				$where="MATCH($matchwhat) AGAINST('$tall $tnone' IN BOOLEAN MODE)";
			}
			elseif($tall) $where="MATCH($matchwhat) AGAINST('$tall' IN BOOLEAN MODE)";
			elseif($tnone) $where="MATCH($matchwhat) AGAINST('$tnone')=0 AND MATCH($matchwhat) AGAINST('$tany')>0";
			else $where="MATCH($matchwhat) AGAINST('$tany')>0";
		}
		elseif($sby=='tags'){
			$tanycount=substr_count($tany, ' ')+1;
			$tany="'".str_replace(' ', "','", $tany)."'";
			if($tall){
				$tallcount=substr_count($tall, ' ')+1;
				$tall="'".str_replace(' ', "','", $tall)."'";
			}
			if($tnone){
				$tnonecount=substr_count($tnone, ' ')+1;
				$tnone="'".str_replace(' ', "','", $tnone)."'";
			}
		}
		if($rid && $cmine)$where="UserID = $rid AND $where";
	}
	if($what=='stories'){
		if($from=='feed'){
			return $sql=<<<SQL
				SELECT stories.ID AS SID, stories.LastActionTime AS STime
				FROM stories
				WHERE stories.UserID = $ID
				ORDER BY STime DESC
SQL;
		}
		elseif($from=='self'){
			return $sql=<<<SQL
				SELECT stories.ID AS SID, stories.CreationTime AS STime
				FROM stories
				WHERE stories.UserID = $ID
				ORDER BY STime DESC
SQL;
		}
		elseif($from=='user'){
			return $sql=<<<SQL
				SELECT stories.ID AS SID, stories.CreationTime AS STime
				FROM stories
				WHERE stories.UserID = $ID AND stories.Privacy = 0
				ORDER BY STime DESC
SQL;
		}
		elseif($from=='following'){
			return $sql=<<<SQL
				SELECT stories.ID AS SID, stories.CreationTime AS STime
				FROM following LEFT JOIN stories ON stories.UserID = following.FollowedID
				WHERE following.FollowerID=$ID AND stories.Privacy = 0
				ORDER BY STime DESC
SQL;
		}
		elseif($from=='classes'){
			return $sql=<<<SQL
				SELECT classcontentlinks.ContentID AS SID, stories.CreationTime AS STime
				FROM classes
				LEFT JOIN classcontentlinks ON classcontentlinks.ClassID=classes.ID
				LEFT JOIN stories ON stories.ID = classcontentlinks.ContentID
				WHERE classes.OwnerID = $ID AND classcontentlinks.ContentType = 0
				ORDER BY STime DESC
SQL;
		}
		elseif($from=='class'){
			if($role=='instructor'){
				return $sql=<<<SQL
					SELECT classcontentlinks.ContentID AS SID, stories.CreationTime AS STime
					FROM classcontentlinks
					LEFT JOIN stories ON stories.ID = classcontentlinks.ContentID
					WHERE classcontentlinks.ClassID = $ID 
					AND classcontentlinks.ContentType = 0
					ORDER BY STime DESC
SQL;
			}
			elseif($role=='student'){
				return $sql=<<<SQL
					SELECT classcontentlinks.ContentID AS SID, stories.CreationTime AS STime
					FROM classcontentlinks
					LEFT JOIN stories ON stories.ID = classcontentlinks.ContentID
					WHERE classcontentlinks.ClassMemberID = $extra AND classcontentlinks.ClassID = $ID AND classcontentlinks.ContentType = 0
					ORDER BY STime DESC
SQL;
			}
			else return FALSE;
		}//end if from class
		elseif($from=='story'){
			return $sql=<<<SQL
				SELECT ID AS SID, CreationTime AS STime
				FROM stories
				WHERE ID = $ID
				LIMIT 1
SQL;
		}
		elseif($from=='submissions'){
			return $sql=<<<SQL
				SELECT ID AS SID, CreationTime AS STime
				FROM stories
				ORDER BY STime DESC
SQL;
		}
		elseif($from=='search'){
			if($sby=='contents'){
				return $sql=<<<SQL
					SELECT ID AS SID, MATCH($matchwhat) AGAINST ('$tany') AS STime
					FROM stories
					WHERE $where
					ORDER BY STime DESC
SQL;
			}
			elseif($sby=='tags'){
				$tanytab=<<<SQL
					SELECT COUNT(TagID) AS Relevance, ContentID AS SID
					FROM tags
					LEFT JOIN taglinks ON taglinks.TagID=tags.ID
					WHERE tags.TheText
SQL;
				if($tanycount===1)$tanytab.=" = $tany";
				else $tanytab.=" IN ($tany)";
				if($tmine) $tanytab.=" AND taglinks.UserID = $rid";
				$tanytab.=' AND ContentType = 0 GROUP BY SID';
				if($tnone){
					$tnonetab=<<<SQL
						SELECT 1 AS BAD, ContentID
						FROM tags
						LEFT JOIN taglinks ON taglinks.TagID=tags.ID
						WHERE tags.TheText
SQL;
					if($tnonecount===1) $tnonetab.=" = $tnone";
					else $tnonetab.=" IN ($tnone)";
					if($tmine) $tanytab.=" AND taglinks.UserID = $rid";
					$tnonetab.=' AND ContentType = 0 GROUP BY ContentID';
				}
				$sql='SELECT SID, Relevance AS STime FROM (';
				if($tall){
					$sql.=<<<SQL
						SELECT ContentID
						FROM tags
						LEFT JOIN taglinks on taglinks.TagID=tags.ID
						WHERE tags.TheText
SQL;
					if($tallcount===1) $sql.=" = $tall";
					else $sql.=" IN ($tall)";
					if($tmine) $sql.=" AND taglinks.UserID = $rid";
					$sql.=' AND ContentType = 0 GROUP BY ContentID';
					if($tallcount>1) $sql.=' HAVING COUNT(DISTINCT ContentID, TagID)=2';
					$sql.=') AS Tall LEFT JOIN';
					if($tnone) $sql.=" ($tnonetab) AS Tnone on Tnone.ContentID=Tall.ContentID LEFT JOIN";
					$sql.=" ($tanytab) AS Tany on Tany.SID=Tall.ContentID";
				}
				else{
					$sql.="$tanytab) AS Tany";
					if($tnone)$sql.=" LEFT JOIN ($tnonetab) AS Tnone on Tnone.ContentID=Tany.SID";
				}
				if($cmine) $sql.=' LEFT JOIN stories ON stories.ID = SID';
				if($tnone && $cmine) $sql.=' WHERE BAD IS NULL AND stories.UserID = '.$rid;
				elseif($tnone) $sql.=' WHERE BAD IS NULL';
				elseif($cmine) $sql.=' WHERE stories.UserID = '.$rid;
				$sql.=' ORDER BY STime DESC';
				return $sql;
			}//end search by tags
			else return FALSE;
		}//end search
		else return FALSE;//can't get a story from a comment
		//aren't doing for story cause we'll just get the story itself//maybe change this
	}//end what == stories
	elseif($what=='comments'){
		if($from=='feed'){
			return $sql=<<<SQL
				SELECT comments.ContentID AS SID, comments.LastActionTime AS STime, comments.ID AS CID
				FROM comments
				WHERE comments.UserID = $ID AND comments.ContentType = 0
				ORDER BY STime DESC
SQL;
		}
		elseif($from=='self'){//if we want 1 line per comment regardless of what it was on (e.g. true history) we'll need to get rid of the grouping
			return $sql=<<<SQL
				SELECT comments.ContentID AS SID, comments.CreationTime AS STime, comments.ID AS CID
				FROM comments
				WHERE comments.UserID = $ID AND comments.ContentType = 0
				ORDER BY STime DESC
SQL;
		}
		elseif($from=='user'){
			return $sql=<<<SQL
				SELECT comments.ContentID AS SID, comments.CreationTime AS STime, comments.ID AS CID
				FROM comments
				WHERE comments.UserID = $ID AND comments.ContentType = 0 AND comments.Privacy = 0
				ORDER BY STime DESC
SQL;
		}
		elseif($from=='following'){
			return $sql=<<<SQL
				SELECT comments.ContentID AS SID, comments.CreationTime AS STime, comments.ID AS CID
				FROM following
				LEFT JOIN comments ON comments.UserID = following.FollowedID
				ORDER BY STime DESC
SQL;
		}
		elseif($from=='classes'){
			return $sql=<<<SQL
				SELECT comments.ContentID AS SID, comments.CreationTime AS STime, classcontentlinks.ContentID AS CID
				FROM classes
				LEFT JOIN classcontentlinks ON classcontentlinks.ClassID=classes.ID
				LEFT JOIN comments ON comments.ID = classcontentlinks.ContentID
				WHERE classes.OwnerID=$ID AND classcontentlinks.ContentType = 1 AND comments.ContentType = 0
				ORDER BY STime DESC
SQL;
		}
		elseif($from=='class'){
			if($role=='instructor'){
				return $sql=<<<SQL
					SELECT comments.ContentID AS SID, comments.CreationTime AS STime, classcontentlinks.ContentID AS CID
					FROM classcontentlinks
					LEFT JOIN comments ON comments.ID = classcontentlinks.ContentID
					WHERE classcontentlinks.ClassID = $ID AND classcontentlinks.ContentType = 1 AND comments.ContentType = 0
					ORDER BY STime DESC
SQL;
			}
			elseif($role=='student'){
				return $sql=<<<SQL
					SELECT comments.ContentID AS SID, comments.CreationTime AS STime, classcontentlinks.ContentID AS CID
					FROM classcontentlinks
					LEFT JOIN comments ON comments.ID = classcontentlinks.ContentID
					WHERE classcontentlinks.ClassMemberID = $extra AND classcontentlinks.ClassID = $ID
						AND classcontentlinks.ContentType = 1 AND comments.ContentType = 0
					ORDER BY STime DESC
SQL;
			}
			else return FALSE;
		}//end from class
		elseif($from=='story'){
			return $sql=<<<SQL
				SELECT ContentID AS SID, CreationTime AS STime, ID AS CID, Rgt, Lft
				FROM comments
				WHERE ContentID = $ID AND ContentType = 0
				ORDER BY Lft ASC
SQL;
		}
		elseif($from=='comment'){
			return $sql=<<<SQL
				SELECT ContentID AS SID, CreationTime AS STime, ID AS CID, Rgt, Lft
				FROM comments
				WHERE ID = $ID
				ORDER BY Lft ASC
SQL;
		}
		elseif($from=='search'){
			if($sby=='contents'){
				return $sql=<<<SQL
					SELECT comments.ContentID AS SID, MATCH($matchwhat) AGAINST ('$tany') AS STime, comments.ID AS CID
					FROM commenttext
					LEFT JOIN comments ON comments.ID = commenttext.ID 
					WHERE $where ORDER BY STime DESC
SQL;
			}
			elseif($sby=='tags'){
			}
			else return FALSE;
		}
		else return FALSE;//can't get a comment from a comment or from submissions
		//aren't doing for story cause we'll just get the story itself//maybe change this
	}//end what comments
	elseif($what=='tags'){
		if($from=='feed' || $from=='self'){//not getting action time on tags, so don't need to differentiate between sort times
			return $sql=<<<SQL
				SELECT ContentID AS SID, MAX(CreationTime) AS STime
				FROM taglinks
				WHERE UserID = $ID AND ContentType = 0
				GROUP BY SID
SQL;
		}
		elseif($from=='story'){
			if($rid){
				return <<<SQL
				SELECT taglinks.ContentID AS SID, taglinks.TagID AS TID, tags.TheText, COUNT(taglinks.TagID) AS NTs, owner.IsOwner
				FROM taglinks
				LEFT JOIN tags on tags.ID = taglinks.TagID
				LEFT JOIN (
					SELECT TagID, 1 AS IsOwner
					FROM taglinks
					WHERE ContentID = $ID AND ContentType = 0 AND UserID = $ri
				) AS owner ON owner.TagID = taglinks.TagID
				WHERE taglinks.ContentID = $ID AND taglinks.ContentType = 0
				GROUP BY TID
				ORDER BY TheText
SQL;
			}
			else{
				return <<<SQL
					SELECT taglinks.TagID AS TID, tags.TheText, COUNT(taglinks.TagID) AS NTs
					FROM taglinks
					LEFT JOIN tags on tags.ID = taglinks.TagID
					WHERE taglinks.ContentID = $ID AND taglinks.ContentType = 0
					GROUP BY TID
					ORDER BY TheText
SQL;
			}
		}
		//not doing comments for tags, but if needed just copy in from story section and change taglinks.ContentType to 1
		else return FALSE;
		//aren't doing for story cause we'll just get the story itself//maybe change this
	}//end what == tags
	elseif($what=='summary'){
		if($from=='classes'){
			if($rid==$ID){//requester is self so get classes instructing and taking
				return $sql=<<<SQL
				SELECT classes.ClassName, classes.OwnerID, users.DisplayName, users.UserName, T.*
				FROM (
					SELECT *
					FROM (
						SELECT classes.ID AS ClassID, 2 AS Role, 'NA' AS CMemName, 0 AS CMemID, NSs, NGSs, GSs, NCs, NGCs, GCs
						FROM classes LEFT JOIN(
							SELECT COUNT(classcontentlinks.ContentID) AS NSs,
								COUNT(classcontentlinks.Grade) AS NGSs,
								SUM(IFNULL(classcontentlinks.Grade,0)) AS GSs, classes.ID AS ClassID
							FROM classes 
							LEFT JOIN classcontentlinks ON classcontentlinks.ClassID = classes.ID
							WHERE classes.OwnerID = $ID && classcontentlinks.ContentType = 0
							GROUP BY ClassID
						)AS Stories ON Stories.ClassID = classes.ID
						LEFT JOIN(
							SELECT COUNT(classcontentlinks.ContentID) AS NCs,
								COUNT(classcontentlinks.Grade) AS NGCs,
								SUM(IFNULL(classcontentlinks.Grade,0)) AS GCs, classes.ID AS ClassID
							FROM classes 
							LEFT JOIN classcontentlinks ON classcontentlinks.ClassID = classes.ID
							WHERE classes.OwnerID = $ID && classcontentlinks.ContentType = 1
							GROUP BY ClassID
						)AS Comments ON Comments.ClassID = classes.ID
						WHERE classes.OwnerID = $ID
					) AS T
					UNION SELECT *
					FROM(
						SELECT classmembers.ClassID, 1 AS Role, classmembers.DisplayName AS CMemName,
							classmembers.ID AS CMemID, NSs, NGSs, GSs, NCs, NGCs, GCs
						FROM classmembers
						LEFT JOIN(
							SELECT COUNT(classcontentlinks.ContentID) AS NSs,
								COUNT(classcontentlinks.Grade) AS NGSs,
								SUM(IFNULL(classcontentlinks.Grade,0)) AS GSs, classmembers.ClassID
							FROM classmembers 
							LEFT JOIN classcontentlinks ON classcontentlinks.ClassMemberID = classmembers.ID
							WHERE classmembers.UserID = $ID && classcontentlinks.ContentType = 0
							GROUP BY ClassID
						)AS Stories ON Stories.ClassID = classmembers.ClassID
						LEFT JOIN(
							SELECT COUNT(classcontentlinks.ContentID) AS NCs,
								COUNT(classcontentlinks.Grade) AS NGCs,
								SUM(IFNULL(classcontentlinks.Grade,0)) AS GCs, classmembers.ClassID
							FROM classmembers 
							LEFT JOIN classcontentlinks ON classcontentlinks.ClassMemberID = classmembers.ID
							WHERE classmembers.UserID = $ID && classcontentlinks.ContentType = 1
							GROUP BY ClassID
						)AS Comments ON Comments.ClassID = classmembers.ClassID
						WHERE classmembers.UserID = $ID
					) AS T
				)AS T LEFT JOIN classes ON classes.ID = T.ClassID
				LEFT JOIN users ON users.ID = classes.OwnerID
				ORDER BY Role DESC, ClassName ASC, CMemName ASC
SQL;
			}//end rid == ID
			elseif($rid){//Get The Public Classes for users.ID = $ID and the user $rid's relationship to the class
				return $sql=<<<SQL
					SELECT classes.ID AS ClassID, classes.ClassName, classes.OwnerID, users.DisplayName,
						users.UserName, IFNULL(Relationship,0) AS Role
					FROM classes
					LEFT JOIN users ON users.ID = classes.OwnerID
					LEFT JOIN(
						SELECT 1 AS Relationship, classes.ID
						FROM classes
						LEFT JOIN classmembers ON classmembers.ClassID = classes.ID
						WHERE classes.OwnerID = $ID AND classmembers.UserID = $rid
					) AS T
					ON T.ID = classes.ID
					WHERE classes.OwnerID = $ID and classes.Visibility = TRUE
					ORDER BY ClassName ASC
SQL;
			}
			else{//Get The Public Classes for users.ID = $ID
				return $sql=<<<SQL
					SELECT classes.ID AS ClassID, classes.ClassName, classes.OwnerID, users.DisplayName, users.UserName, 0 AS Role
					FROM classes
					LEFT JOIN users ON users.ID = classes.OwnerID
					WHERE classes.OwnerID = $ID and classes.Visibility = TRUE
					ORDER BY ClassName ASC
SQL;
			}
		}//end from classes
		elseif($from=='class'){
			if($role!=='instructor' && $role!=='student') return FALSE; //ONLY show class summary for instructors and students
			elseif($role=='instructor'){//Show the summary each classmembers contributions
				return $sql=<<<SQL
					SELECT $ID AS ClassID, classmembers.ID AS MID, classmembers.DisplayName AS MDispName,
						Stories.NSs, Stories.GSs, Comments.NCs, Comments.GCs
					FROM classmembers
					LEFT JOIN(
						SELECT COUNT(ContentID) AS NSs, SUM(IFNULL(Grade,0)) AS GSs, ClassMemberID
						FROM classcontentlinks
						WHERE classcontentlinks.ClassID = $ID AND classcontentlinks.ContentType = 0
						GROUP BY ClassMemberID
					)AS Stories ON Stories.ClassMemberID = classmembers.ID
					LEFT JOIN(
						SELECT COUNT(ContentID) AS NCs, SUM(IFNULL(Grade,0)) AS GCs, ClassMemberID
						FROM classcontentlinks
						WHERE classcontentlinks.ClassID = $ID AND classcontentlinks.ContentType = 1
						GROUP BY ClassMemberID
					)AS Comments ON Comments.ClassMemberID = classmembers.ID
					WHERE classmembers.ClassID = $ID
					ORDER BY MDispName ASC
SQL;
			}//end role == instructor
			else return FALSE;
		}//end from class
		else return FALSE;//no summaries for classmembers
	}
	elseif($what=='vote'){
		return $sql=<<<SQL
			SELECT Sig AS sig
			FROM votes
			WHERE ContentType = $from AND ContentID = $ID AND UserID = $rid
			LIMIT 1
SQL;
	}
	elseif($what=='classesattendingoptions'){
		if($from=='self'){
			return $sql=<<<SQL
			SELECT classes.ID, classes.ClassName
			FROM classmembers
			LEFT JOIN classes ON classes.ID = classmembers.ClassID
			WHERE classmembers.UserID=$ID
SQL;
		}
	else return FALSE;
	}
	else return FALSE;
}

function getClinks($contentstolink, $type){
	if($type=='stories'){
		return <<<SQL
			SELECT T.*, classes.ID AS SCID, classes.ClassName AS SCName, users.ID AS SCOwnerID,
				users.UserName AS SCOwnerUName, users.DisplayName AS SCOwnerDName,
				classmembers.ID AS SCMID, classmembers.UserID AS SCMUID,
				classmembers.DisplayName AS SCMDName, classuser.UserName AS SCMUName,
				Slinks.Grade AS Sgrade,	Slinks.ID AS SlinkID
			FROM ($contentstolink) AS T
			LEFT JOIN (
				SELECT ContentID, ClassID, ClassMemberID, Grade, ID
				FROM classcontentlinks
				WHERE ContentType = 0
			) AS Slinks ON Slinks.ContentID = T.SID
			LEFT JOIN classes ON classes.ID = Slinks.ClassID
			LEFT JOIN users ON users.ID = classes.OwnerID
			LEFT JOIN classmembers ON classmembers.ID = Slinks.ClassMemberID
			LEFT JOIN users AS classuser ON classuser.ID = classmembers.UserID
SQL;
	}//end stories
	else{//we're linking comments
		return <<<SQL
			SELECT T.*, classes.ID AS CCID, classes.ClassName AS CCName, users.ID AS CCOwnerID,
				users.UserName AS CCOwnerUName, users.DisplayName AS CCOwnerDName, classmembers.ID AS CCMID,
				classmembers.UserID AS CCMUID, classmembers.DisplayName AS CCMDName,
				classuser.UserName AS CCMUName,	Clinks.Grade AS Cgrade, Clinks.ID AS ClinkID
			FROM ($contentstolink) AS T
			LEFT JOIN (
				SELECT ContentID, ClassID, ClassMemberID, Grade, ID
				FROM classcontentlinks
				WHERE ContentType =1
			) AS Clinks ON Clinks.ContentID = T.CID
			LEFT JOIN classes ON classes.ID = Clinks.ClassID
			LEFT JOIN users ON users.ID = classes.OwnerID
			LEFT JOIN classmembers ON classmembers.ID = Clinks.ClassMemberID
			LEFT JOIN users AS classuser ON classuser.ID = classmembers.UserID
SQL;
	}
}

function getcontents($what, $from, $id, $page=1, $extra='', $canurl=''){
//echo "<p>What is $what, from is $from, id is $id, page is $page, and extra is $extra.</p>";
					//what: all, stories, comments, tags, summary, instructorsummary, studentsummary, classes
					//from: search, self, feed, classes, class, following, user, storyname, storyid, comment, frontpage, submissions
	//hidden filtering will remove stories from feed, self, user, following, frontpage, and submissions
		//The hidden filter is not applied to classes, or class data when requested outside of a feed
	//This way stories will show up on students pages even if hidden so you can hide students work from your feed but find it later
	//check to see that the identifier is valid and return the mysql escaped version if it is
	if($from=='submissions' || $from=='frontpage' || $from=='search')$temparr = array('id'=>$id, 'user'=>$id);
	elseif($what=='vote') $temparr = array('id'=>$id, 'user'=>$page);//vote is special page holds the requesters userid
	else $temparr = checkcontentid($from, $id);
	//$thetarr=var_export($temparr, TRUE);
	if(!$temparr)return FALSE;
	$id=$temparr['ID'];
	$rid=$temparr['user'];
	$role='';
	$mid='';
	if($from=='class'){
		$role=$temparr['role'];
		if($role!='instructor' && $role!='student') return FALSE;
		elseif($role=='student' && $from=='class') $mid=$temparr['MID'];
	}
	if($from=='storylink' || $from=='storyid')$from='story';//checkcontentid returned ids for both, so we can collapse the difference
	$hasclasses=($_SESSION['IsInstructor']==1)? 1 : 0;
	$inclasses=($_SESSION['IsStudent']==1)? 1 : 0;
	$isfollowing=($_SESSION['IsFollowing']==1)? 1 : 0;
	
	//echo "<p>After getting the temparr the id is $id rid is $rid role is $role and mid is $mid.</p>";
	
	$limit=($from=='search')? 30 : 15;//limit number of stories on a page make sure this is the same as in format contents
	$offset=$limit++*($page-1);//will get the appropriate offset and get us one more than we wanted so we can check for pagination
	
	if($from=='feed'){//see if we have followers or classes to get
		if($what=='all' || $what=='stories'){
			$cols='SID, STime';
			$chunk=unionwrapper($cols, getCIDs('stories', 'feed', $id));
			if($isfollowing)$chunk="$chunk UNION ".unionwrapper($cols, getCIDs('stories', 'following', $id));
			if($hasclasses)$chunk="$chunk UNION ".unionwrapper($cols, getCIDs('stories', 'classes', $id));
			if($isfollowing && $hasclasses) $chunk=unionwrapper($cols, $chunk, '', 'SID');//group by SID to remove duplicates
			if($what=='all')$storieschunk=$chunk;
			else{//wrap hidden stories if this is all we're getting
				if($rid){
					if($extra=='show')$chunk=hiddenwrapper($chunk, $rid, 'show', 'stories');
					else $chunk=hiddenwrapper($chunk, $rid, 'hide', 'stories');
				}
				$allchunk=$chunk;
			}
		}//end getting stories
		if($what=='all' || $what=='comments'){
			$cols='SID, STime, CID';
			$chunk=unionwrapper($cols, getCIDs('comments', 'feed', $id));
			if($isfollowing)$chunk="$chunk UNION ".unionwrapper($cols, getCIDs('comments', 'following', $id));
			if($hasclasses)$chunk="$chunk UNION ".unionwrapper($cols, getCIDs('comments', 'classes', $id));
			if($isfollowing && $hasclasses)$chunk=unionwrapper($cols, $chunk, '', 'CID');//group by CID to remove duplicate comments
			//now order by cid and group by SID for most recent comment and number of comments for each story
			if($rid){//hide hidden comments so they don't contribute or vice versa
				if($extra=='show')$chunk=hiddenwrapper($chunk, $rid, 'show', 'comments');
				else $chunk=hiddenwrapper($chunk, $rid, 'hide', 'comments');
				$cols='SID, STime, CID, COUNT(CID) AS NCs, CHide';
			}
			else $cols='SID, STime, CID, COUNT(CID) AS NCs';
			$chunk=unionwrapper($cols, $chunk, 'CID', 'SID');//order by cid and group by SID to get most recent comment per story
			if($what=='all')$commentschunk=$chunk;
			else{//wrap hidden stories if this is all we're getting
				if($rid){
					if($extra=='show')$chunk=hiddenwrapper($chunk, $rid, 'show', 'stories');
					else $chunk=hiddenwrapper($chunk, $rid, 'hide', 'stories');
				}
				$allchunk=$chunk;
			}
		}//end getting comments
		if($what=='all' || $what=='tags'){
			$cols='SID, STime';
			$chunk=unionwrapper($cols, getCIDs('tags', 'feed', $id));
			if($what=='all')$tagschunk=$chunk;
			else{//wrap hidden stories if this is all we're getting
				if($rid){
					if($extra=='show')$chunk=hiddenwrapper($chunk, $rid, 'show', 'stories');
					else $chunk=hiddenwrapper($chunk, $rid, 'hide', 'stories');
				}
				$allchunk=$chunk;
			}
		}
		if($what=='all'){//put the chunks together
			//commentschunk contains info the others so we need to group tags and stories and add the columns
			$extracol=($rid)? ', 0 AS CHide' : '';
			$cols='SID, STime, 0 AS CID, 0 AS NCs'.$extracol;
			$chunk=unionwrapper($cols, "$storieschunk UNION $tagschunk");
			$chunk="$chunk UNION $commentschunk";
			//order by cid and sort by sid to get the most recent comment for each story
			$extracol=($rid)? ', CHide' : '';
			$cols='SID, MAX(STime) AS STime, CID, SUM(NCs) AS NCs'.$extracol;
			$allchunk=unionwrapper($cols, $chunk, 'CID', 'SID');
			//wrap for hidden stories //comments were already wrapped above
			if($rid){
				if($extra=='show')$allchunk=hiddenwrapper($allchunk, $rid, 'show', 'stories');
				else $allchunk=hiddenwrapper($allchunk, $rid, 'hide', 'stories');
			}
		}
		//need to limit the stories here before we start connecting everything
		$allchunk=<<<SQL
			SELECT *
			FROM ($allchunk) AS T
			ORDER BY STime DESC
			LIMIT $limit OFFSET $offset
SQL;
		//screen for classcontents
		if($inclasses || $hasclasses){
			$allchunk=getClinks($allchunk, 'stories');//check if any of the stories were authored for classes
			if($what=='all' || $what=='comments')$allchunk=getClinks($allchunk, 'comments');//and for comments, if necessary
		}
		//get the information about the stories and comments
		$allchunk=contentjoiner($what, $from, $allchunk, $rid);
	}
	elseif ($from=='classes' || $from=='class'){
		if($what=='summary' && $from=='class' && $role=='student'){
			$what='all';
			$from='class';
		}
		if($what=='summary'){
			$allchunk=getCIDs('summary', $from, $id, $rid, $role);
			$page=$id; //we don't limit summaries by pages so we can use page to pass info on to format contents
		}
		else{//not getting summary information
			if($what=='all' || $what=='stories'){
				if($what=='all')$storieschunk=getCIDs('stories', $from, $id, $rid, $role, $mid);
				else{//wrap hidden stories if this is all we're getting
					$chunk=getCIDs('stories', $from, $id, $rid, $role, $mid);
					if($rid){
						//don't ever hide class content when the user is requesting the class information
						if($extra=='show')$chunk=hiddenwrapper($chunk, $rid, 'show', 'stories');
						else $chunk=hiddenwrapper($chunk, $rid, 'mark', 'stories');
					}
					$allchunk=$chunk;
				}
			}//end getting stories
			if($what=='all' || $what=='comments'){
				$chunk=getCIDs('comments', $from, $id, $rid, $role, $mid);
				//now order by cid and group by SID for most recent comment and number of comments for each story
				if($rid){//show or mark comments don't ever hide class content when the user is requesting the class information
					if($extra=='show')$chunk=hiddenwrapper($chunk, $rid, 'show', 'comments');
					else $chunk=hiddenwrapper($chunk, $rid, 'mark', 'comments');
					$cols='SID, STime, CID, COUNT(CID) AS NCs, CHide';
				}
				else $cols='SID, STime, CID, COUNT(CID) AS NCs';
				$chunk=unionwrapper($cols, $chunk, 'CID', 'SID');//order by cid and group by SID to get most recent comment per story
				if($what=='all')$commentschunk=$chunk;
				else{//wrap hidden stories if this is all we're getting
					if($rid){//don't ever hide class content when the user is requesting the class information
						if($extra=='show')$chunk=hiddenwrapper($chunk, $rid, 'show', 'stories');
						else $chunk=hiddenwrapper($chunk, $rid, 'mark', 'stories');
					}
					$allchunk=$chunk;
				}
			}//end getting comments
			if($what=='all'){//put the chunks together
				//commentschunk contains info the others so we add the columns to storieschunk
				$extracol=($rid)? ', 0 AS CHide' : '';
				$cols='SID, STime, 0 AS CID, 0 AS NCs'.$extracol;
				$allchunk=unionwrapper($cols, $storieschunk);
				$allchunk="$allchunk UNION $commentschunk";
				$extracol=($rid)? ', CHide' : '';
				$cols='SID, MAX(STime) AS STime, CID, SUM(NCs) AS NCs'.$extracol;
				$allchunk=unionwrapper($cols, $allchunk, 'CID', 'SID');
				//wrap for hidden stories //comments were already wrapped above
				if($rid){//don't ever hide class content when the user is requesting the class information
					if($extra=='show')$allchunk=hiddenwrapper($allchunk, $rid, 'show', 'stories');
					else $allchunk=hiddenwrapper($allchunk, $rid, 'mark', 'stories');
				}
			}
			//need to limit the stories here before we start connecting everything
			$allchunk=<<<SQL
				SELECT *
				FROM ($allchunk) AS T
				ORDER BY STime DESC
				LIMIT $limit OFFSET $offset
SQL;
			//screen for classcontents
			$allchunk=getClinks($allchunk, 'stories');//check if any of the stories were authored for classes
			if($what=='all' || $what=='comments')$allchunk=getClinks($allchunk, 'comments');//and for comments, if necessary
			//get the information about the stories and comments
			$allchunk=contentjoiner($what, $from, $allchunk, $rid);
		}//end else (i.e. not getting summary information
	}
	elseif($from=='user' || $from=='self' || $from=='following'){
		if($what=='classesattendingoptions' && $from=='self'){//get list of classes
			$allchunk=getCIDs('classesattendingoptions', $from, $id);
		}
		else{
			if($what=='all' || $what=='stories'){
				if($what=='all')$storieschunk=getCIDs('stories', $from, $id);
				else{
					$allchunk=getCIDs('stories', $from, $id);
					if($rid){//wrap hidden stories if this is all we're getting
						if($extra=='show')$allchunk=hiddenwrapper($allchunk, $rid, 'show', 'stories');
						else $allchunk=hiddenwrapper($allchunk, $rid, 'hide', 'stories');
					}
				}
			}//end getting stories
			if($what=='all' || $what=='comments'){
				$chunk=getCIDs('comments', $from, $id);
				if($rid){//hide hidden comments so they don't contribute or vice versa
					if($extra=='show')$chunk=hiddenwrapper($chunk, $rid, 'show', 'comments');
					else $chunk=hiddenwrapper($chunk, $rid, 'hide', 'comments');
					$extracol=', CHide';
				}
				else $extracol='';
				//now order by cid and group by SID for most recent comment and number of comments for each story
				$cols='SID, STime, CID, COUNT(CID) AS NCs'.$extracol;
				if($what=='all')$commentschunk=unionwrapper($cols, $chunk, 'CID', 'SID');
				else{
					$allchunk=unionwrapper($cols, $chunk, 'CID', 'SID');
					if($rid){//wrap hidden stories if this is all we're getting
						if($extra=='show')$allchunk=hiddenwrapper($allchunk, $rid, 'show', 'stories');
						else $allchunk=hiddenwrapper($allchunk, $rid, 'hide', 'stories');
					}
				}
			}//end getting comments
			if($from=='self' && ($what=='all' || $what=='tags')){
				if($what=='all')$tagschunk=getCIDs('tags', $from, $id);
				else{
					$allchunk=getCIDs('tags', $from, $id);
					if($rid){//wrap hidden stories if this is all we're getting
						if($extra=='show')$allchunk=hiddenwrapper($allchunk, $rid, 'show', 'stories');
						else $allchunk=hiddenwrapper($allchunk, $rid, 'hide', 'stories');
					}
				}
			}
			if($what=='all'){//put the chunks together
				//commentschunk contains info the others so we need to group tags and stories and add the columns
				$extracol=($rid)? ', 0 AS CHide' : '';
				$cols='SID, STime, 0 AS CID, 0 AS NCs'.$extracol;
				$chunk=($from=='self')? "SELECT * FROM ($storieschunk) AS T UNION $tagschunk" : $storieschunk;
				$chunk=unionwrapper($cols, $chunk);
				$chunk="$chunk UNION $commentschunk";
				//order by cid and group by sid to get the most recent comment for each story
				$extracol=($rid)? ', CHide' : '';
				$cols='SID, MAX(STime) AS STime, CID, SUM(NCs) AS NCs'.$extracol;
				$allchunk=unionwrapper($cols, $chunk, '', 'SID');
				//wrap for hidden stories //comments were already wrapped above
				if($rid){
					if($extra=='show')$allchunk=hiddenwrapper($allchunk, $rid, 'show', 'stories');
					else $allchunk=hiddenwrapper($allchunk, $rid, 'hide', 'stories');
				}
			}
			//need to limit the stories here before we start connecting everything
			$allchunk=<<<SQL
				SELECT *
				FROM ($allchunk) AS T
				ORDER BY STime DESC
				LIMIT $limit OFFSET $offset
SQL;
			//screen for classcontents
			if($inclasses || $hasclasses){
				$allchunk=getClinks($allchunk, 'stories');//check if any of the stories were authored for classes
				if($what=='all' || $what=='comments')$allchunk=getClinks($allchunk, 'comments');//and for comments, if necessary
			}
			//get the information about the stories and comments
			$allchunk=contentjoiner($what, $from, $allchunk, $rid);
			//echo "<p> The allchunk is $allchunk </p>";
		}
	}
	elseif($from=='story'){
		if($what=='all' || $what=='stories'){
			$chunk=getCIDs('stories', $from, $id, $rid);
			if($rid)$chunk=hiddenwrapper($chunk, $rid, 'mark', 'stories');//always mark contents when getting story directly
			if($inclasses || $hasclasses)$chunk=getClinks($chunk, 'stories');//check if the story was authored for a class
			if($what=='stories')$allchunk=contentjoiner('stories', $from, $chunk, $rid);
			else $allchunk['stories']=contentjoiner('stories', $from, $chunk, $rid);
		}//end getting stories
		if($what=='all' || $what=='comments'){
			$chunk=getCIDs('comments', $from, $id, $rid);
			if($rid)$chunk=hiddenwrapper($chunk, $rid, 'mark', 'comments');//always mark contents when getting story directly
			if($inclasses || $hasclasses)$chunk=getClinks($chunk, 'comments');//check if the comment was authored for a class
			if($what=='comments'){
				$allchunk=contentjoiner('comments', $from, $chunk, $rid);
				$page=$id;//pass the story id through page if we're getting comments through a story
			}
			else $allchunk['comments']=contentjoiner('comments', $from, $chunk, $rid);
		}//end getting comments
		if($what=='all' || $what=='tags'){
			if($what=='tags'){
				$allchunk=getCIDs('tags', $from, $id, $rid);
				$page=$id;//pass the story id through page if we're getting comments through a story
			}
			else $allchunk['tags']=getCIDs('tags', $from, $id, $rid);
		}//end getting tags
		//don't put anything together and make three calls to mysqli_query if what==all
	}
	elseif($from=='comment'){//don't need to check for hidden cause this is only called when adding a new comment
		$chunk=getCIDs('comments', 'comment', $id, $rid);
		if($inclasses || $hasclasses)$chunk=getClinks($chunk, 'comments');//check if the comment was authored for a class
		$allchunk=contentjoiner('comments', 'comment', $chunk, $rid);
	}
	elseif($from=='submissions'){
		$chunk=getCIDs('stories', $from, '');
		if($rid){//hide or show the hidden contents depending on extra
			if($extra=='show')$chunk=hiddenwrapper($chunk, $rid, 'show', 'stories');
			else $chunk=hiddenwrapper($chunk, $rid, 'hide', 'stories');
			if($hasclasses || $inclasses)$chunk=getClinks($chunk, 'stories');
		}
		//need to limit the stories here before we start connecting everything
		$chunk=<<<SQL
			SELECT *
			FROM ($chunk) AS T
			ORDER BY STime DESC
			LIMIT $limit OFFSET $offset
SQL;
		//screen for classcontents
		$allchunk=contentjoiner($what, $from, $chunk, $rid);
	}
	elseif($what=='vote'){
		//this is different from holds the contenttype id holds the contentid rid holds the rid
		$allchunk=getCIDs('vote', $from, $id, $rid);
		$from=($from==0)? "story.$id" : "comment.$id";
	}
	elseif($from=='search'){
		$allchunk=getCIDs($what['sfor'], 'search', '', $rid, '', $what);
		$what=$what['sfor'];
		if($what=='comments'){
			if($rid){//hide hidden comments so they don't contribute or vice versa
				if($extra=='show')$allchunk=hiddenwrapper($allchunk, $rid, 'show', 'comments');
				else $allchunk=hiddenwrapper($allchunk, $rid, 'hide', 'comments');
			}
			//now group by SID for most recent comment and number of comments for each story
			$allchunk=unionwrapper('*', $allchunk, '', 'SID');
		}
		if($rid){//need to fix this when we're searching for comments too, this is just for stories
			if($extra=='show')$allchunk=hiddenwrapper($allchunk, $rid, 'show', 'stories');
			else $allchunk=hiddenwrapper($allchunk, $rid, 'hide', 'stories');
			if($inclasses || $hasclasses){
				if($what=='comments')$allchunk=getClinks($allchunk, 'comments');//check if any of the comments were authored for classes
				$allchunk=getClinks($allchunk, 'stories');//check if any of the stories were authored for classes
			}
		}
		$allchunk=<<<SQL
			SELECT *
			FROM ($allchunk) AS T
			LIMIT $limit
			OFFSET $offset
SQL;
		//screen for classcontents
		$allchunk=contentjoiner($what, 'search', $allchunk, $rid);
	}
	
	/*
	echo "<p>What is $what, from is $from, and allchunk is below.</p>";
	var_dump($allchunk);
	
	if(!($what=="all" && $from=="story")) echo "<p>$allchunk</p>";
	else{
		echo "<p> THE STORIES: ".$allchunk["stories"]."</p>";
		echo "<p> THE COMMENTS: ".$allchunk["comments"]."</p>";
		echo "<p> THE TAGS: ".$allchunk["tags"]."</p>";
	}
	*/
	//echo "<p> THE TAGS: ".$allchunk["tags"]."</p>";
	
	$db=usedb();
	if(!($what=='all' && $from=='story')) $result=mysqli_query($db, $allchunk);
	else{//we have three chunks to run
		$result['stories']=mysqli_query($db, $allchunk['stories']);
		$result['comments']=mysqli_query($db, $allchunk['comments']);
		$result['tags']=mysqli_query($db, $allchunk['tags']);
	}
	mysqli_close($db);
	
	//$allchunk.="<br><br>".formatcontents($result, $what, $from, $rid, $page, $extra, $canurl);
	$allchunk=formatcontents($result, $what, $from, $rid, $page, $extra, $canurl);
	return $allchunk;//."<br> the tarr was $thetarr rid was $rid and page is $page";//."<br>The sql was $sql";
}

function getheaddiv(){
	$contents='<div class="head"><div class="logo"><a href="http://collabor8r.com" alt="Collabor8" class="headlink">Collabor8r<span class="beta">Beta</span></div></a>';
	$linkclass="link headopt";
	if(isloggedin()){
	  $params=array("username"=>$_SESSION['UserName']);
	  $contents.="<div class=\"clickable_span headopt\" onclick=\"return controller('login', 'login.shut')\">Logout</div>";//logout
	  $contents.=createlink("options", $params, "Options", $linkclass);//options
	  $contents.="<div class=\"clickable_span headopt\" onclick=\"return controller('search', 'search.form')\">Search</div>";//search
	  if($_SESSION['IsStudent']==1 || $_SESSION['IsInstructor']==1) $contents.=createlink("classessummary", $params, "Classes", $linkclass);//classes
	  else $contents.=createlink("classes", $params, "Classes", $linkclass);//classes
	  $contents.=createlink("feedall", $params, "Feed", $linkclass);//feed
	  $contents.=createform('url');//Submit URL
	}
	else{
	  $contents.="<div class=\"clickable_span headopt\" onclick=\"return controller('login', 'login.lfrm')\">Login</div>";//login
	  $contents.="<div class=\"clickable_span headopt\" onclick=\"return controller('login', 'login.rfrm')\">Register</div>";//register
	  $contents.=createlink("classes", array(), "Classes", $linkclass);//classes
	  $contents.="<div class=\"clickable_span headopt\" onclick=\"return controller('search', 'search.form')\">Search</div>";//search
	}
	return $contents."<br class=\"clearad\"></div>";
}

function getpassword($from, $id){
	if($from!='users' && $from!='classes')return FALSE;
	$db=usedb();
	$sql=<<<SQL
		SELECT PassWord
		FROM $from
		WHERE $from.ID=$id
SQL;
	$result=mysqli_query($db, $sql);
	mysqli_close($db);
	if($result===FALSE){
		emailadminerror('mysqlerror', array('location'=>"getpassword.$from.$id", 'query'=>$sql));
		return FALSE;
	}
	elseif(mysqli_num_rows($result)==0) return FALSE;//the content doesn't exist
	else{
		$assoc=mysqli_fetch_assoc($result);
		return $assoc['PassWord'];//return the password
	}
}

function getresponselinkinfo(){
	$response="";
	//get what the user requested and normalize it
	$requested = strtolower(preg_replace("/\/$/", "", $_SERVER["REQUEST_URI"]));//remove trailing forward slashes and make everything lower case
	//explode the request into an array and remove empty values
	$linkinfo = array_filter(explode('/', preg_replace("/^\//", "", $requested)));
	//check the array for, get, and remove any hidden flags from linkinfo
	$hidden="";
	while(!(($key=array_search("hidden", $linkinfo))===FALSE)){
			$response.=" found a hidden ";
			$hidden="show";
			unset($linkinfo[$key]);
	}
	$linkinfo=array_merge($linkinfo);
	//check if the user is logged in if not ignore the hidden flag
	$rid=isloggedin();
	if($rid) $rname=$_SESSION['UserName'];
	else{
		$rname="";//set rname to nothing
		$hidden="";//ignore earlier hidden flags
	}
	
	//check the array for, get, and remove page numbers from linkinfo
	$page="1";
	$regex="/^page0*([1-9][0-9]*)$/";
	$tarr=preg_grep($regex, $linkinfo);
	foreach($tarr AS $key => $value){
		preg_match($regex, $value, $matches);
		$page=$matches[1];
		unset($linkinfo[$key]);
	}
	$linkinfo=array_merge($linkinfo);
	
	$params=array("hidden"=>$hidden, "page"=>$page);
	//when adding more do $params=array("other"=>$stuff)+$params;
	$errorcode=FALSE;
	$advertisement='storieshead';
	$numinfo=count($linkinfo);
	if(!$numinfo){//nothing extra to get so present the default stories
		$thetitle="Collabor8r: Encouraging Interdisciplinary Collaboration";
		$canonicalurl=createurl("submissions", $params);
		if(!($thebody=getcontents("stories", "submissions", $rid, $page, $hidden, $canonicalurl)))$errorcode=404;
	}
	else{//we have seomthing in the linkinfo to get
		$info1=$linkinfo[0];
		if($info1=='stories'){
			$thetitle="Collabor8r: Encouraging Interdisciplinary Collaboration";
			if($page)$thetitle.=" Page $page";
			$canonicalurl=createurl("submissions", $params);
			if($numinfo>1){//do something special with the stories
				$info2=$linkinfo[1];
				if(regexchecker($info2, "internallink")){//try to get the requested story
					$params=array("link"=>$info2)+$params;
					$canonicalurl=createurl("storylink", $params);
					if(!($contents=getcontents("all", "storylink", $info2, $page, $hidden, $canonicalurl)))$errorcode=404;
					else{
						$thetitle=$contents["title"];
						$thebody=$contents["body"];
						$advertisement='FALSE';
					}
				}//end check that info1 is potentially an internal link
				else $errorcode=404;
			}//end looking for more than just stories
			else{//present the default stories
				if(!($thebody=getcontents("stories", "submissions", $rid, $page, $hidden, $canonicalurl)))$errorcode=404;
			}
		}//end stories
		elseif($info1=='users'){
			if($numinfo>1){
				$username=$linkinfo[1];
				$params=array("username"=>$username)+$params;
				if(regexchecker($username, "username")){//is username in appropriate form
					$privaleges=($rname==$username);
					if($numinfo>2){//we're looking for something specific on the user
						$info3=$linkinfo[2];
						if($info3=='all'){
							$canonicalurl=createurl("userall", $params);
							if($privaleges){//show all users submissions
								$thetitle="Collabor8r: Your submissions.";
								if(!($thebody=getcontents("all", "self", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
							}
							else{//show all submissions that are publicly visible
								$thetitle="Collabor8r: User $username's submissions.";
								if(!($thebody=getcontents("all", "user", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
							}
						}
						elseif($info3=='stories'){
							$canonicalurl=createurl("userstories", $params);
							if($privaleges){//show all users stories
								$thetitle="Collabor8r: Your stories.";
								if(!($thebody=getcontents("stories", "self", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
							}
							else{//show all stories that are publicly visible
								$thetitle="Collabor8r: User $username's public stories.";
								if(!($thebody=getcontents("stories", "user", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
							}
						}
						elseif($info3=='comments'){
							$canonicalurl=createurl("usercomments", $params);
							if($privaleges){//show all users comments
								$thetitle="Collabor8r: Stories on which you've commented.";
								if(!($thebody=getcontents("comments", "self", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
							}
							else{//show all commented on stories that are publicly visible
								$thetitle="Collabor8r: Stories on which user $username publicly commented.";
								if(!($thebody=getcontents("comments", "user", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
							}
						}
						elseif($info3=='tags'){
							if($privaleges){//show all users tags
								$thetitle="Collabor8r: Stories you've tagged.";
								$canonicalurl=createurl("usertags", $params);
								if(!($thebody=getcontents("tags", "self", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
							}
							else $errorcode=403;//only users can see what they've tagged
						}
						elseif($info3=='feed'){
							if($privaleges){//show info from feed
								$thetitle="Collabor8r: Your feed.";
								$canonicalurl=createurl("feedall", $params);
								if($numinfo>3){//we're looking for something specific in feed
									$info4=$linkinfo[3];
									if($info4=='all'){
										if(!($thebody=getcontents("all", "feed", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
									}
									elseif($info4=='stories'){
										$thetitle="Collabor8r: Stories from your feed.";
										$canonicalurl=createurl("feedstories", $params);
										if(!($thebody=getcontents("stories", "feed", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
									}
									elseif($info4=='comments'){
										$thetitle="Collabor8r: Stories on which you and your collabor8rs have commented.";
										$canonicalurl=createurl("feedcomments", $params);
										if(!($thebody=getcontents("comments", "feed", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
									}
									else $errorcode=404;
								}//end looking for specific feed item
								else{//not looking for anything specific, so present the whole feed
									if(!($thebody=getcontents("all", "feed", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
								}
							}//end of privaledges
							else $errorcode=403;
						}
						elseif($info3=='following'){
							if($privaleges){//show info from those the requester is following
								$thetitle="Collabor8r: All submissions by collabr8rs you're following.";
								$canonicalurl=createurl("followingall", $params);
								if($numinfo>3){//we're looking for something specific in following
									$info4=$linkinfo[3];
									if($info4=='all'){
										if(!($thebody=getcontents("all", "following", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
									}
									elseif($info4=='stories'){
										$thetitle="Collabor8r: Stories by collabr8rs you're following.";
										$canonicalurl=createurl("followingstories", $params);
										if(!($thebody=getcontents("stories", "following", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
									}
									elseif($info4=='comments'){
										$thetitle="Collabor8r: Stories on which collabr8rs you're following have commented.";
										$canonicalurl=createurl("followingcomments", $params);
										if(!($thebody=getcontents("comments", "following", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
									}
									else $errorcode=404;
								}//end looking for specific following item
								else{//not looking for anything specific, so present the whole following
									if(!($thebody=getcontents("all", "following", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
								}
							}//end of privaledges
							else $errorcode=403;
						}//end of following
						elseif($info3=='classes'){
							if($numinfo>3 && $privaleges){//we're looking for something specific in classes and have the authority to do so
								$classid=$linkinfo[3];
								if(isvalididnum($classid)){//we've requested info for a particular class
									//get the users relationship to the class
									$temparr=checkcontentid("class", $classid);
									if($temparr){//we the requester is affiliated with the class
										$params=array("classid"=>$classid)+$params;
										$role=$temparr["role"];
										if($numinfo>4){//we're looking for something specific in classes
											$info5=$linkinfo[4];
											if($info5=='all'){
												if($role=="instructor")$thetitle="Collabor8r: All submissions for class $classid.";
												else $thetitle="Collabor8r: All your submissions for class $classid.";
												$canonicalurl=createurl("classall", $params);
												if(!($thebody=getcontents("all", "class", $classid, $page, $hidden, $canonicalurl)))$errorcode=404;
											}
											elseif($info5=='stories'){
												if($role=="instructor")$thetitle="Collabor8r: Stories for class $classid.";
												else $thetitle="Collabor8r: Stories you submitted for class $classid.";
												$canonicalurl=createurl("classstories", $params);
												if(!($thebody=getcontents("stories", "class", $classid, $page, $hidden, $canonicalurl)))$errorcode=404;
											}
											elseif($info5=='comments'){
												if($role=="instructor")$thetitle="Collabor8r: Stories on which classmembers from class $classid have commented.";
												else $thetitle="Collabor8r: Stories on which you commented for class $classid.";
												$canonicalurl=createurl("classcomments", $params);
												if(!($thebody=getcontents("comments", "class", $classid, $page, $hidden, $canonicalurl)))$errorcode=404;
											}
											else $errorcode=404;
										}//end looking for something specific
										else{//not looking for anything specific present summary for instructor or contributions for student
											if($role=="instructor"){
												$thetitle="Collabor8r: Summary for class $classid.";
												$canonicalurl=createurl("classsummary", $params);
												if(!($thebody=getcontents("summary", "class", $classid, $page, $hidden, $canonicalurl)))$errorcode=404;
											}
											else{//it's a student
												$thetitle="Collabor8r: Your submissions for class $classid.";
												$canonicalurl=createurl("classall", $params);
												if(!($thebody=getcontents("all", "class", $classid, $page, $hidden, $canonicalurl)))$errorcode=404;
											}
										}//end not looking for anything specific
									}//end the requester is affilliated
									else $errorcode=403; //the user isn't affiliated with the class or it doesn't exist
								}//end looking for particular class
								elseif($classid=='all'){
									$thetitle="Collabor8r: All submissions by your classmembers.";
									$canonicalurl=createurl("classesall", $params);
									if(!($thebody=getcontents("all", "classes", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
								}
								elseif($classid=='stories'){
									$thetitle="Collabor8r: Stories by your classmembers.";
									$canonicalurl=createurl("classesstories", $params);
									if(!($thebody=getcontents("stories", "classes", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
								}
								elseif($classid=='comments'){
									$thetitle="Collabor8r: Stories on which your classmembers have commented.";
									$canonicalurl=createurl("classescomments", $params);
									if(!($thebody=getcontents("comments", "classes", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
								}
								else $errorcode=404;
							}//done looking for something specific
							elseif($numinfo==3){//get the class summaries even if no privaleges
								if($privaleges) $thetitle="Collabor8r: Summary of your classes.";
								else $thetitle="Collabor8r: Summary of user $username's publicly visible classes.";
								$canonicalurl=createurl("classessummary", $params);
								if(!($thebody=getcontents("summary", "classes", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
								$advertisement=FALSE;
							}//end getting class summaries
							elseif(!$privaleges) $errorcode=403; //the user wasn't the classes owner and wanted more than the summary
						}
						elseif($info3=='options'){
							if(!$privaleges)$errorcode=403;
							else{
								$thetitle="Collabor8r: Your Options.";
								$thebody=createform('options');
								$canonicalurl=createurl("options", $params);
								$advertisement=FALSE;
							}
						}
						else $errorcode=404;
					}//end looking for something specific about the user
					else{//get feed if privaleges or publicly visible stories
						if($privaleges){
							$thetitle="Collabor8r: Your feed.";
							$canonicalurl=createurl("feedall", $params);
							if(!($thebody=getcontents("all", "feed", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
						}//end privaleges
						else{
							$thetitle="Collabor8r: User $username's publicly visible stories.";
							$canonicalurl=createurl("userstories", $params);
							if(!($thebody=getcontents("stories", "user", $username, $page, $hidden, $canonicalurl)))$errorcode=404;
						}
					}//end we're not looking for anything specific
				}//end username is in correctform
				else{//data isn't in appropriate form
					$errorcode=404;
				}
			}//end we have a username
			else{//requester should have provided a userid
				$thetitle="Collabor8r: Users";
				$canonicalurl=createurl("users", '');
				$thebody="Please specify the user name of the user you're interested in.";
				$advertisement=FALSE;
			}
		}
		elseif($info1=='classes'){
			$thetitle="Collabor8r: Information about classes.";
			$altlink=($rname!="")? createlink("classessummary", array("username"=>$rname), "Create, Join, or View Your Classes.") : "Login or register to start or join classes.";
			$thebody=include '/homepages/41/d92908607/htdocs/collabor8r/protected/classes.php';
			$thebody.="$altlink";
			$canonicalurl=createurl("classes", $params);
			$advertisement=FALSE;
		}
		elseif($info1=='search'){
			$advertisement='searchhead';
			$thetitle="Collabor8r: Find Stories Relevant To You!";
			$canonicalurl=createurl('search');
			$error='';
			$sfor=$cmine=$sby=$tall=$tany=$tnone=$tmine='';
			if($_SERVER['REQUEST_METHOD'] == "GET"){//we should have data posted if the users got here the way they're supposed to
				if(isset($_GET['sfor']) && (($sfor=$_GET['sfor'])==='stories') || ($sfor==='comments')) $sfor=$_GET['sfor'];
				else $error='You must choose to search for stories or comments.\n<br>';
				if($rid && isset($_GET['cmine']) && $_GET['cmine']==='true')$cmine=TRUE;
				if($sfor==='stories' && isset($_GET['sby']) && (($sby=$_GET['sby'])==='tags')) $sby='tags';
				else $sby='contents';
				if(isset($_GET['tall'])) $tall=$_GET['tall'];
				if(isset($_GET['tany'])) $tany=$_GET['tany'];
				if(isset($_GET['tnone'])) $tnone=$_GET['tnone'];
				if($tall==='' && $tany==='') $error.='Please include some terms to search for.\n<br>';
				if($sby=='tags' && ($tall || $tany || $tnone)){
					if($tall){
						$tall=mb_strtolower($tall);
						if(strpos($tall, ','))$tall=str_rep(',', ' ', regexspacecleaner($tall, ',', '-'));
						else $tall=regexspacecleaner($tall);
					}
					if($tany){
						$tany=mb_strtolower($tany);
						if(strpos($tany, ','))$tany=str_rep(',', ' ', regexspacecleaner($tany, ',', '-'));
						else $tany=regexspacecleaner($tany);
					}
					if($tnone){
						$tnone=mb_strtolower($tnone);
						if(strpos($tnone, ','))$tnone=str_rep(',', ' ', regexspacecleaner($tnone, ',', '-'));
						else $tnone=regexspacecleaner($tnone);
					}
				}
				else{
					if($tall)$tall=regexspacecleaner($tall);
					if($tany)$tany=regexspacecleaner($tany);
					if($tnone)$tnone=regexspacecleaner($tnone);
				}
				if($rid && $sfor==='stories' && $sby==='tags' && isset($_GET['tmine']) && $_GET['tmine']==='true')$tmine=TRUE;
				$params=array('sfor'=>$sfor, 'sby'=>$sby, 'cmine'=>$cmine, 'tall'=>$tall, 'tany'=>$tany, 'tnone'=>$tnone, 'tmine'=>$tmine);
				if(!$error===''){//we have one or more errors represent the form
					$thebody=$error.createform('search', $params);
					$advertisement=FALSE;
				}
				else{//we don't have errors, try getting the stories
					if(!($thebody=getcontents($params, 'search', $rid, $page, $hidden, $canonicalurl))){
						$thebody='<span class="caution">We\'re sorry, but we were unable to find anything.  Please refine your search and try again.</span><br>';
						$thebody.=createform('search', $params);
						$advertisement=FALSE;
					}
				}
			}
			else $errorcode=404;
		}
		elseif($info1=='terms'){
			$thetitle="Collabor8r: Terms of Service";
			$thebody=include '/homepages/41/d92908607/htdocs/collabor8r/protected/terms.php';
			$canonicalurl=createurl('tos');
			$advertisement=FALSE;
		}
		elseif($info1=='about'){
			$thetitle="Collabor8r: About Us!";
			$thebody=include '/homepages/41/d92908607/htdocs/collabor8r/protected/about.php';
			$canonicalurl=createurl('about');
			$advertisement=FALSE;
		}
		elseif($info1=='403')$errorcode=403;
		else $errorcode=404;//site doesn't got to the page I want if user goes to collabor8r.com/404 Need to fix htaccess I think
	}
	
	if(!$errorcode);//no problems don't do anything
	elseif($errorcode==403){
		header('HTTP/1.1 403 Forbidden');
		$thetitle="Collabor8r: 403 Forbidden.";
		$thebody="403! You don't have permission to view this information.";
		$canonicalurl=createurl("403", $params);
		$advertisement=FALSE;
	}
	else{//give 404
		header('HTTP/1.1 404 Not Found');
		$thetitle="Collabor8r: 404 Not Found.";
		$thebody="404! We're sorry, but we couldn't find the information you requested.";
		$canonicalurl=createurl("404", $params);
		$advertisement=FALSE;
	}
	
	return array('title'=>$thetitle, 'body'=>$thebody, 'canonicalurl'=>$canonicalurl, 'advertisement'=>$advertisement); 
}

function givefooter(){
	include '/homepages/41/d92908607/htdocs/collabor8r/protected/footer.php';
}

function giveheader($title="Collabor8r: Encouraging Interdisciplinary Collaboration", $canonicalurl="http://collabor8r.com/"){
	// Setting the Content-Type header with charset
	header('Content-Type: text/html; charset=utf-8');
	//Put in the DOCTYPE
	//include the needed javascripts
	//include the needed stylesheets

	$headdiv=getheaddiv();
	echo <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="/css/collabor8r.css" />
		<script type="text/javascript" src="/js/md5.js"></script>
		<script type="text/javascript" src="/js/r8r.js"></script>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="canonical" href="$canonicalurl" />
		<title>$title</title>
	</head>
	<body>
    $headdiv
    <div class="bigwrap">
			<div id="contentdiv" class="content">
				<noscript>
					Please turn on Javascript to Collabor8.
				</noscript>
END;
}

function havechallenge(){
  $challenge=(isset($_SESSION['challenge']))? $_SESSION['challenge'] : generatechallenge();
  return $challenge;
}

function hiddenwrapper($contentstowrap, $userid, $action='hide', $contenttype='stories'){
	if(!$userid)return $contentstowrap;//don't wrap anything if we don't have a user id to check for hidden contents
	if($contenttype=='stories'){
		$ctype=0;
		$cas='SHide';
		$joinon='T.SID';
	}
	elseif($contenttype=='comments'){
		$ctype=1;
		$cas='CHide';
		$joinon='T.CID';
	}
	else return FALSE;
	if($action=='hide')$action=' WHERE hiding.Hidden IS NULL ';
	elseif ($action=='show')$action=' WHERE hiding.Hidden IS NOT NULL ';
	elseif ($action=='mark')$action='';
	else return FALSE;
	return $sql=<<<SQL
		SELECT T.*, hiding.Hidden AS $cas
		FROM ($contentstowrap) AS T
		LEFT JOIN(
			SELECT *
			FROM hidden
			WHERE hidden.UserID = $userid AND hidden.ContentType = $ctype
		)AS hiding ON hiding.ContentID = $joinon $action
SQL;
}

function isint($varinquestion){
	return (is_numeric($varinquestion) && (int)$varinquestion==$varinquestion)? TRUE : FALSE;
}

function isloggedin(){
  $userid=(isset($_SESSION['ID']))? $_SESSION['ID']: 0;
  return $userid;
}

function isvalididnum($varinquestion){
	return (is_numeric($varinquestion) && (int)$varinquestion==$varinquestion && $varinquestion > 0)? TRUE : FALSE;
}

function loginforaction($action){
	switch($action){
		case 'class':
		case 'commentform':
		case 'commentsubmit':
		case 'authanon':
		case 'follow':
		case 'grade':
		case 'hide':
		case 'story':
		case 'vote':
		case 'options':
		case 'submitforclass';
		case 'changeclassmembername';
			return TRUE;
			break;
		case "tag"://need to check operation type
		case "login"://need to check operation type
		default:
			return "na";
			break;
	}
}

function messageforproblemwithpost($problem){
	$theproblem=(is_array($problem))? $problem['problem'] : $problem;
	$prefix='';
	if(!(($dotloc=strpos($theproblem, '.'))===FALSE)){
		$prefix=substr($theproblem, 0, $dotloc);
		$theproblem=substr($theproblem, $dotloc+1);
	}
	switch($theproblem){
		case "requiredvaluesmissing":
			$message = "We're sorry, you are missing some required information.	Please, try again.<br><br>\n";
			break;
		case "passwordsdontmatch":
			$message = "Unfortunately, the passwords you provided don't match.	Please, try again.<br><br>\n";
			break;
		case "classdoesnotexist":
			$message = "Unfortunately, the class you specified does not exist.	Please, try again.<br><br>\n";
			break;
		case "passwordincorrect":
			$message = "We're sorry, but the password provided was incorrect.	Please, try again.<br><br>\n";
			break;
		case "badurl":
			$message = "We're sorry, but the URL you provided appears to be invalid.	Please, try again and contact us if you believe we're in error.<br><br>\n";
			break;
		case "bademail":
			$message = "We're sorry, but the email you provided appears to be invalid.	Please, try again and contact us if you believe we're in error.<br><br>\n";
			break;
		case "baduname":
			$message = "User names must be at least 3 characters long.  Additionally, they can only contain letters, numbers, and underscores and must begin and end with a letter or number.<br><br>\n";
			break;
		case "storyalreadyexists":
			$storylink=$problem['storylink'];
			$message = "We're sorry, but this story already exists.	You can comment on the story <a href=\"http://collabor8r.com/stories/$storylink\">here</a>.<br><br>\n";
			break;
		case "storytitlealreadyexists":
			$message = "We're sorry, but a story with this title already exists.	Please edit the title and try again.<br><br>\n";
			break;
		case "unknowncontententype":
			$message = "We're sorry, but you did not request a valid content type.	Please, try again.<br><br>\n";
			break;
		case "badcontentid":
			$message = "We're sorry, but you did not request a valid content id.	Please, try again.<br><br>\n";
			break;
		case "unknownop":
			$message = "We're sorry, but we're not sure what to do with the information you provided.<br><br>\n";
			break;
		case "badgrade":
			$message = "Grades must integers from 0 to 100<br><br>\n";
			break;
		case "login":
			$message = "You must be logged in to do that.<br><br>\n";
			break;
		case "urlchange":
			$message = "We're sorry, but this story has a different URL from the one you just tried submitting.  Please try again and alert us if this problem continues.<br><br>\n";
			break;
		case 'tos':
			$message = "You must agree with the terms of service to register.<br><br>\n";
			break;
		case 'keepitclean':
			$message = "There's no need for that kind of language.<br><br>\n";
			break;
		case 'usernameunavailable':
			$message = "We're sorry, but that username is not available.<br><br>\n";
			break;
		case "process":
		default:
			$message = "We're sorry, we were unable to process your request. Please, try again.<br><br>\n";
			break;
	}
	echo $prefix.$message;
}

function messagetouser($type, $params=""){
	$message="";
	switch($type){
		case "dangeroussubmission":
			if($params==1 || $params==3){
				$message.=<<<END
<p>Warning- Suspected phishing page. This page may be a forgery or imitation of another website, designed to trick users into sharing personal
or financial information. Entering any personal information on this page may result in identity theft or other abuse. You can find out more
about phishing from <a href="http://www.antiphishing.org" class="link">www.antiphishing.org</a>.</p>
END;
			}
			if($params==2 || $params==3){
				$message.=<<<END
<p>Warning- Visiting this web site may harm your computer. This page appears to contain malicious code that could be downloaded to your computer
 without your consent. You can learn more about harmful web content including viruses and other malicious code and how to protect your computer
 at <a href="http://www.stopbadware.org" class="link">StopBadware.org</a>.</p>
END;
			}
			$message.=<<<END
<p>Advisory provided by Google.  For more information about why this advisory was made, please check their
<a href="http://code.google.com/apis/safebrowsing/safebrowsing_faq.html#whyAdvisory" class="link">Safe Browsing FAQ</a>.</p>
<p>Google works to provide the most accurate and up-to-date phishing and malware information. However, it cannot guarantee that its information is
comprehensive and error-free: some risky sites may not be identified, and some safe sites may be identified in error.</p>
END;
			break;
		case 'badfrequency':
			$message.=<<<END
<p class="caution">Due to recent high frequency of potentially dangerous submissions made under your account, your account has been blocked for 24 hours.  If you feel 
this decision was made in error or you suspect your account has been hacked, please contact us immediately.</p>
END;
			break;
		case 'badfrequencyclose':
			$message.=<<<END
<p class="caution">Several potentially dangerous submissions have been made under your account recently.  If this continues, your your account will be blocked for 24 hours.
If you feel you are receiving this message in error or you suspect your account has been hacked, please contact us immediately.</p>
END;
			break;
			case 'baddprime':
			$message.=<<<END
<p class="caution">Due to the high ratio of rejected to accepted submissions, your account has been blocked for 24 hours.  If you feel 
this decision was made in error or you suspect your account has been hacked, please contact us immediately.</p>
END;
			break;
		case 'baddprimeclose':
			$message.=<<<END
<p class="caution">The ratio of rejected to accepted submissions is becoming to high.  If this pattern continues, your account
will be blocked for 24 hours.  If you feel your are receiving this message in error or you suspect your account has been hacked, please contact us immediately.</p>
END;
			break;
	}
	return $message;
}

function missingrequiredvalues($postarr){
	$keys=preg_grep('/req_/',array_keys($postarr));
	foreach ($keys as $key)if($postarr[$key]=='' && $postarr[$key]!=0)return TRUE;
	return FALSE;
}

function opendiv($type, $divid="", $divclass="", $divaction=""){
	//to create a message, not needed for all divs
	switch($type){
		case "sumcsinstructing":
			$divid=$type;
			$message="<span class=\"classheader\">Classes Instructing</span><hr />";
			break;
		case "sumcstaking":
			$divid=$type;
			$message="<span class=\"classheader\">Classes Taking</span><hr />";
			break;
		default:
			$message="";
			break;
	}
	if($divclass!="")$divclass="class=\"$divclass\"";
	return "\n<div id=\"$divid\" $divclass $divaction>$message";
}

function passwordsdontmatch($postarr){
	$keys=preg_grep('/password[12]/',array_keys($postarr));
	if(count($keys)!=2)return FALSE;
	if($postarr[$keys[0]]!=$postarr[$keys[1]])return FALSE;
	return TRUE;
}

function problemwithpost($action, $postarr){
	//echo "the action is $action ";
	//check for missing required values
	if(missingrequiredvalues($postarr))return 'requiredvaluesmissing';
	if($action=='class'){
		if($postarr['req_optype']=='open');//don't do anything
		elseif($postarr['req_optype']=='form'){
			if(!isvalididnum($postarr['req_classid']) && $postarr['req_classid']!='taking' && $postarr['req_classid']!='teaching') return 'badcontentid';
		}
		elseif($postarr['req_optype']=='drop'){
			if(!isvalididnum($postarr['req_classid']) && $postarr['req_classid']!='taking' && $postarr['req_classid']!='teaching') return 'badcontentid';
			elseif($postarr['req_what']!='class' && $postarr['req_what']!='student') return 'process';
			//req_whatid could be a number or a string of letters, punctuation, and spaces so we'll check this later
		}
		elseif($postarr['req_optype']=='join'){
			if(!isvalididnum($postarr['req_classid'])) return 'failure.badcontentid';
			$classid=intval($postarr['req_classid']);
			if(!$encryptedpassword=getpassword('classes', $classid)) return 'failure.classdoesnotexist';
			elseif (!verifyusersresponse($_SESSION['challenge'],$postarr['req_userresponse'],$encryptedpassword)) return 'failure.passwordincorrect';
		}
		else return 'unknownop';
	}
	elseif($action=='login'){
		$optype=$postarr['req_optype'];
		if($optype=='shut' || $optype=='lfrm' || $optype=='open' || $optype=='rfrm' || $optype=='efrm' || $optype=='funm' || $optype=='fpwd'){
		}
		elseif($optype=='join'){
			if($postarr['req_agree']!='true') return 'failure.tos';
			elseif(!regexchecker($postarr['req_pword'], 'md5')) return 'failure.process';//check that it's in the right format (md5)
			elseif(!regexchecker($postarr['req_email'], 'email')) return 'failure.bademail';//check that it's in the right format for an email address
			elseif(!regexchecker($postarr['req_uname'], 'username')) return 'failure.baduname';
			elseif(count(array_intersect(array($postarr['req_uname']), badwords()))>0) return 'failure.keepitclean';
			elseif(count(array_intersect(array($postarr['req_uname']), reservedwords()))>0 || regexchecker($postarr['req_uname'], 'taboo')) return 'failure.usernameunavailable';
		}
		elseif($optype=='sunm'){
			if(!regexchecker($postarr['req_email'], 'email')) return 'failure.bademail';
		}
		elseif($optype=='spwd'){
			if(!regexchecker($postarr['req_uname'], 'username')) return 'failure.baduname';
			elseif(!regexchecker($postarr['req_email'], 'email')) return 'failure.bademail';
		}
		else return 'failure.unknownop';
	}
	elseif($action=='vote'){
		if(!($postarr['req_optype']=='sig' || $postarr['req_optype']=='nsg' || $postarr['req_optype']=='del')) return 'unknownop';
		elseif($postarr['req_contenttype']!='story' && $postarr['req_contenttype']!='comment') return 'unknowncontententype';
		elseif(!isvalididnum($postarr['req_contentid'])) return 'badcontentid';
	}
	elseif($action=='commentsshow'){
		if($postarr['req_contenttype']!='story') return 'unknowncontententype';
		elseif(!isvalididnum($postarr['req_storyid'])) return 'badcontentid';
	}
	elseif($action=='commentform'){
		if($postarr['req_contenttype']!='story') return 'unknowncontententype';
		elseif(!isvalididnum($postarr['req_storyid'])) return 'badcontentid';
		elseif($postarr['req_length']!=3 && $postarr['req_length']!=4) return 'requiredvaluesmissing';
		elseif($postarr['req_length']==4) if(!isvalididnum($postarr['req_commentid'])) return 'badcontentid';
	}
	elseif($action=='commentsubmit'){
		if($postarr['req_contenttype']!='story') return 'failure.unknowncontententype';
		elseif(!isvalididnum($postarr['req_storyid'])) return 'failure.badcontentid';
		elseif($postarr['req_length']!=3 && $postarr['req_length']!=4) return 'failure.requiredvaluesmissing';
		elseif($postarr['req_length']==4) if(!isvalididnum($postarr['req_commentid'])) return 'failure.badcontentid';
		elseif(!isint($postarr['forclass'])) return 'failure.classdoesnotexist';//ok to have 0 here cause 0 let's us know it's not for a class
		elseif($postarr['req_access']=='true') return 'failure.process';
		else{//make sure there are no badwords
			$thewords=explode(" ", strtolower($postarr['req_comment']));//explode the comment
			//identify badwords
			$tarr=array_intersect($thewords, badwords());
			if(count($tarr)>0){
				return 'failure.keepitclean';
			}
			if(regexchecker(strtolower($postarr['req_comment']), 'taboo')){ //identifies words like youfucker or educat8r
					return 'failure.keepitclean';
			}
		}
	}
	elseif($action=='authanon'){
		if($postarr['req_contenttype']!='story' && $postarr['req_contenttype']!='comment' ) return 'unknowncontententype';
		elseif(!isvalididnum($postarr['req_contentid'])) return 'badcontentid';
		elseif($postarr['req_optype']!='show' && $postarr['req_optype']!='hide') return 'unknownop';
	}
	elseif($action=='follow'){
		if($postarr['req_contenttype']!='story' && $postarr['req_contenttype']!='comment' ) return 'unknowncontententype';
		elseif(!isvalididnum($postarr['req_contentid'])) return 'badcontentid';
		elseif($postarr['req_optype']!='start' && $postarr['req_optype']!='cease') return 'unknownop';
	}
	elseif($action=='grade'){
		if($postarr['req_contenttype']!='story' && $postarr['req_contenttype']!='comment' ) return 'unknowncontententype';
		elseif(!isvalididnum($postarr['req_contentid'])) return 'badcontentid';
		elseif(!isvalididnum($postarr['req_gid'])) return 'badcontentid';
		elseif(!isint($postarr['req_score'])) return 'badgrade';
		elseif($postarr['req_score']>100 || $postarr['req_score']<0) return 'badgrade';
	}
	elseif($action=='hide'){
		if($postarr['req_contenttype']!='story' && $postarr['req_contenttype']!='comment' ) return 'unknowncontententype';
		elseif(!isvalididnum($postarr['req_contentid'])) return 'badcontentid';
		elseif($postarr['req_optype']!='hidep' && $postarr['req_optype']!='showp') return 'process';
	}
	elseif($action=='tag'){
		$rid=isloggedin();
		$optype=$postarr['req_optype'];
		if($optype!='add1' && $optype!='rem1' && $optype!='adds' && $optype!='grab') return 'unknownop';
		elseif(($optype=='add1' || $optype=='rem1' || $optype=='adds') && !$rid) return 'login';
		elseif(!isvalididnum($postarr['req_storyid'])) return 'badcontentid';
		elseif(($optype=='add1' || $optype=='rem1') && !isvalididnum($postarr['req_tags'])) return 'badcontentid';
	}
	elseif($action=='story'){
		if($optype=='save'){
			if($postarr['req_url']!=$_SESSION['urlsubmitting']) return 'urlchange';
			elseif($postarr['req_access']=='true') return 'process';
		}//don't check for errors for optype form here
	}
	elseif($action=='options'){
		$optype=$postarr['req_optype'];
		if($optype=='unm'){
			if(!regexchecker($postarr['req_uname'], 'username')) return 'failure.baduname';
			elseif(!regexchecker($postarr['req_userresponse'], 'md5')) return 'failure.process';//check that it's in the right format (md5)
		}
		elseif($optype=='pwd'){
			if((!regexchecker($postarr['req_userresponse'], 'md5')) || (!regexchecker($postarr['req_newpword'], 'md5'))) return 'failure.process';//check that it's in the right format (md5)
		}
		elseif($optype=='dnm'){
			if($postarr['req_dname']=='') return 'failure.requiredvaluesmissing';
		}
		elseif($optype=='eml' || $optype=='eok'){
			if(!regexchecker($postarr['req_email'], 'email')) return 'failure.bademail';
			elseif(!regexchecker($postarr['req_userresponse'], 'md5')) return 'failure.process';
			elseif($optype=='eok' && $postarr['req_confirmation']=='') return 'failure.requiredvaluesmissing';
		}
		elseif($optype=='aff'){
			if($postarr['req_affiliation']=='') return 'failure.requiredvaluesmissing';
		}
		elseif($optype=='sho' || $optype=='sfw'){
			if($postarr['req_checked']!='true' && $postarr['req_checked']!='false') return 'failure.requiredvaluesmissing';
		}
		else return 'failure.unknownop';
	}
	elseif($action=='email'){
		$optype=$postarr['req_optype'];
		if($optype=='rfrm' || $optype=='rsnd'){
			if($postarr['req_contenttype']!='story' && $postarr['req_contenttype']!='comment') return 'failure.unknowncontententype';
			elseif(!isvalididnum($postarr['req_contentid'])) return 'failure.badcontentid';
		}
		elseif($optype=='csnd'){
			if(!regexchecker($postarr['req_email'], 'email')) return 'failure.bademail';
		}
		else return 'failure.unknownop';
	}
	elseif($action=='submitforclass'){
		if(($postarr['req_contenttype']!='story') && ($postarr['req_contenttype']!='comment')) return 'unknownop';
		elseif(!isvalididnum($postarr['req_contentid'])) return 'badcontentid';
		elseif(($postarr['req_optype']!='form') && ($postarr['req_optype']!='save')) return 'unknownop';
		elseif($temparr['req_optype']=='save' && !isvalididnum($postarr['req_submitfor'])) return 'process';
	}
	elseif($action=='changeclassmembername'){
		if($postarr['req_optype']!='save') return 'unknownop';
		elseif(!isvalididnum($postarr['req_classid'])||!isvalididnum($postarr['req_classmemberid']))return 'badcontentid';
		elseif($postarr['req_newname']=='') return 'unknownop';;
	}
	return FALSE;
}

function processpost($action,$postarr,$userid){//userid is not needed for all functions
	if($action=='class'){
		if($postarr['req_optype']=='drop'){
			$classid=$postarr['req_classid'];
			$role=$sql1=$sql2=$sql3=$sessionvar=$result=FALSE;
			if($classid!='taking' && $classid!='teaching'){
				$classid=intval($classid);
				if(($temparr=checkcontentid('class', $classid))===FALSE || (($role=$temparr['role'])!='instructor' && $role!='student')){
					echo 'failureYou are not affiliated with this class.';
					return FALSE;
				}
			}
			if($postarr['req_what']=='class'){
				if($classid=='taking'){
					$sessionvar='IsStudent';
					$sql1=<<<SQL
						DELETE classmembers, classcontentlinks
						FROM classmembers
						LEFT JOIN classcontentlinks ON classcontentlinks.ClassMemberID = classmembers.ID
						WHERE classmembers.UserID = $userid
SQL;
					$sql2=<<<SQL
						UPDATE users
						LEFT JOIN classmembers ON classmembers.UserID = users.ID
						SET users.IsStudent = 0
						WHERE IsStudent = 1 AND classmembers.ID IS NULL
SQL;
					$message="You've successfully dropped all of your classes.";
				}
				elseif($classid=='teaching'){
					$sessionvar='IsInstructor';
					$sql1=<<<SQL
						DELETE classes, classmembers, classcontentlinks
						FROM classes
						LEFT JOIN classmembers ON classmembers.ClassID = classes.ID
						LEFT JOIN classcontentlinks ON classcontentlinks.ClassID = classes.ID
						WHERE classes.OwnerID = $userid
SQL;
					$sql2=<<<SQL
						UPDATE users
						LEFT JOIN classmembers ON classmembers.UserID = users.ID
						SET users.IsStudent = 0
						WHERE IsStudent = 1 AND classmembers.ID IS NULL
SQL;
					$sql3=<<<SQL
						UPDATE users
						LEFT JOIN classes ON classes.OwnerID = users.ID
						SET users.IsInstructor = 0
						WHERE IsInstructor = 1 AND classes.ID IS NULL
SQL;
					$message="You've successfully deleted all of your classes.";
				}
				else{//it's a particular class
					if($role=='instructor'){
						$sessionvar='IsInstructor';
						$sql1=<<<SQL
							DELETE classes, classmembers, classcontentlinks
							FROM classes
							LEFT JOIN classmembers ON classmembers.ClassID = classes.ID
							LEFT JOIN classcontentlinks ON classcontentlinks.ClassID = classes.ID
							WHERE classes.ID = $classid AND classes.OwnerID = $userid
SQL;
						$sql2=<<<SQL
							UPDATE users
							LEFT JOIN classmembers ON classmembers.UserID = users.ID
							SET users.IsStudent = 0
							WHERE IsStudent = 1 AND classmembers.ID IS NULL
SQL;
						$sql3=<<<SQL
							UPDATE users
							LEFT JOIN classes ON classes.OwnerID = users.ID
							SET users.IsInstructor = 0
							WHERE IsInstructor = 1 AND classes.ID IS NULL
SQL;
						$message="You've successfully deleted this class.";
					}
					else{//checkcontentid only returns instructor, student or false.  we already checked for false and instructor so it must be student dropping a class
						$sessionvar='IsStudent';
						$sql1=<<<SQL
							DELETE classmembers, classcontentlinks
							FROM classmembers
							LEFT JOIN classcontentlinks
							ON classcontentlinks.ClassMemberID = classmembers.ID
							WHERE classmembers.ClassID = $classid AND classmembers.UserID = $userid
SQL;
						$sql2=<<<SQL
							UPDATE users
							LEFT JOIN classmembers ON classmembers.UserID = users.ID
							SET users.IsStudent = 0
							WHERE IsStudent = 1 AND classmembers.ID IS NULL
SQL;
						$message="You've successfully dropped this class.";
					}
				}
			}
			elseif($postarr['req_what']=='student'){
				$studid=$postarr['req_whatid'];
				if($studid=='all'){
					if($classid=='teaching'){
						$sql1=<<<SQL
							DELETE classmembers, classcontentlinks
							FROM classes
							LEFT JOIN classmembers ON classmembers.ClassID = classes.ID
							LEFT JOIN classcontentlinks ON classcontentlinks.ClassID = classes.ID
							WHERE classes.OwnerID = $userid
SQL;
						$sql2=<<<SQL
							UPDATE users
							LEFT JOIN classmembers ON classmembers.UserID = users.ID
							SET users.IsStudent = 0
							WHERE IsStudent = 1 AND classmembers.ID IS NULL
SQL;
						$message="You've successfully dropped all students from all of your classes.";
					}
					elseif($role=='instructor'){
						$sql1=<<<SQL
							DELETE classmembers, classcontentlinks
							FROM classes
							LEFT JOIN classmembers ON classmembers.ClassID = classes.ID
							LEFT JOIN classcontentlinks ON classcontentlinks.ClassID = classes.ID
							WHERE classes.ID = $classid AND classes.OwnerID = $userid
SQL;
						$sql2=<<<SQL
							UPDATE users
							LEFT JOIN classmembers ON classmembers.UserID = users.ID
							SET users.IsStudent = 0
							WHERE IsStudent = 1 AND classmembers.ID IS NULL
SQL;
						$message="You've successfully dropped all students from your class.";
					}
				}
				elseif($role=='instructor'){
					$sql1='displayname';//need to escape the displayname below after calling usedb
					$sql2=<<<SQL
						UPDATE users
						LEFT JOIN classmembers ON classmembers.UserID = users.ID
						SET users.IsStudent = 0
						WHERE IsStudent = 1 AND classmembers.ID IS NULL
SQL;
					$dname=$postarr['req_whatid'];
					$message="You've successfully dropped $dname from your class.";
				}
				$sql2=<<<SQL
					UPDATE users
					LEFT JOIN classmembers ON classmembers.UserID = users.ID
					SET users.IsStudent = 0
					WHERE IsStudent = 1 AND classmembers.ID IS NULL
SQL;
			}
			if($sql1===FALSE){
				echo 'failureWe\'re sorry, but there was a problem processing your request.  Please let us know if the problem continues.';
				emailadminerror('unknowndrop', array('classid'=>$classid, 'what'=>$postarr['req_what'], 'whatid'=>$postarr['req_whatid']));
				return false;
			}
			$db=usedb();
			if($sql1=='displayname'){
				$dname=mysqli_real_escape_string($db, $dname);
				$sql1=<<<SQL
					DELETE classmembers, classcontentlinks
					FROM classes
					LEFT JOIN classmembers ON classmembers.ClassID = classes.ID
					LEFT JOIN classcontentlinks ON classcontentlinks.ClassID = classes.ID
					WHERE classes.ID = $classid AND classes.OwnerID = $userid AND classmembers.DisplayName = '$dname'
SQL;
			}
			if(mysqli_query($db, $sql1)){//we successfully deleted the contents in question
				//execute the other queries if there are any
				$sqlerror=array('problem'=>'no');
				if($sql2){
					if(!mysqli_query($db, $sql2)){
						$sqlerror['problem']='yes';
						$sqlerror['location2']='dropclassupdatingsql2';
						$sqlerror['query2']=$sql2;
					}
				}
				if($sql3){
					if(!mysqli_query($db, $sql3)){
						$sqlerror['problem']='yes';
						$sqlerror['location3']='dropclassupdatingsql2';
						$sqlerror['query3']=$sql3;
					}
				}
				$sql1=<<<SQL
					SELECT IsInstructor, IsStudent
					FROM users
					WHERE ID = $userid
SQL;
				$result=mysqli_query($db, $sql1);
				mysqli_close($db);
				if($result){
					$assoc = mysqli_fetch_assoc($result);
					$_SESSION['IsInstructor']=$assoc['IsInstructor'];
					$_SESSION['IsStudent']=$assoc['IsStudent'];
				}
				else{
					$sqlerror['problem']='yes';
					$sqlerror['location3']='dropclassselectinginstructorstudent';
					$sqlerror['query3']=$sql1;
				}
				if($sqlerror['problem']=='yes') emailadminerror('failureafterdrop', $sqlerror);
				echo "success$message";
				return TRUE;
			}
			else{
				mysqli_close($db);
				echo 'failureWe\'re sorry, but there was a problem processing your request.  Please let us know if the problem continues.';
				emailadminerror('mysqlerror', array('location'=>"dropclass$classid", 'query'=>$sql1));
				return FALSE;
			}
		}
		elseif($postarr['req_optype']=='form'){
			echo 'success'.createform('class', $postarr['req_classid']);
			return TRUE;
		}
		elseif($postarr['req_optype']=='open'){
			$visibility=($postarr['req_visibility']=='true')? 1 : 0;
			$db=usedb();
			$pword=mysqli_real_escape_string($db, $postarr['req_password1']);
			$cname=mysqli_real_escape_string($db, $postarr['req_classname']);
			$sql=<<<SQL
				INSERT INTO classes (ClassName, PassWord, Visibility, OwnerID)
				VALUES ('$cname', '$pword', $visibility, $userid)
SQL;
			if(mysqli_query($db, $sql)){
				if($_SESSION['IsInstructor']==false){
					$sql=<<<SQL
						UPDATE users
						SET IsInstructor = 1
						WHERE ID = $userid
SQL;
					if(mysqli_query($db, $sql))mysqli_close($db);
					else{
						mysqli_close($db);
						emailadminerror('mysqlerror', array('location'=>"openclassupdateisinstructor$cname", 'query'=>$sql));
					}
				}
				else mysqli_close($db);
				echo 'success';
				return TRUE;
			}
			else{
				mysqli_close($db);
				echo 'failureWe\'re sorry, but there was a problem processing your request.  Please let us know if the problem continues.';
				emailadminerror('mysqlerror', array('location'=>"openclass$cname", 'query'=>$sql));
				return FALSE;
			}
		}
		elseif($postarr['req_optype']=='join'){
			//allready checked that the password was good so we can enter the information directly
			$classid=intval($postarr['req_classid']);
			$role=1;
			$db=usedb();
			$dname=mysqli_real_escape_string($db, $postarr['req_displayname']);
			$sql=<<<SQL
				INSERT INTO classmembers (ClassID, UserID, Role, DisplayName)
				VALUES ($classid, $userid, $role, '$dname')
SQL;
			if(mysqli_query($db, $sql)){
				if($_SESSION['IsStudent']==0){
					$sql=<<<SQL
						UPDATE users
						SET IsStudent = 1
						WHERE ID = $userid
SQL;
					if(mysqli_query($db, $sql))mysqli_close($db);
					else{
						mysqli_close($db);
						emailadminerror('mysqlerror', array('location'=>"joinclass$classid.updating", 'query'=>$sql));
					}
					$_SESSION['IsStudent']=1;
				}
				else mysqli_close($db);
				echo 'success';
				return TRUE;
			}
			else{
				mysqli_close($db);
				echo 'failureWe\'re sorry, but there was a problem processing your request.  Please let us know if the problem continues.';
				emailadminerror('mysqlerror', array('location'=>"joinclass$classid.updating", 'query'=>$sql));
				return FALSE;
			}
		}
	}
	elseif($action=='login'){
		$optype=$postarr['req_optype'];
		$time=time();
		if($optype=='open'){
			if($userid){
				echo 'failureYou\'re already logged in.<br><br>';
				return FALSE;
			}
			$username=strtolower($postarr['req_uname']);
			$db=usedb();
			$username=mysqli_real_escape_string($db, $username);
			$sessionid=mysqli_real_escape_string($db, session_id());
			$userip=mysqli_real_escape_string($db, $_SERVER['REMOTE_ADDR']);
			$sessionstart=mysqli_real_escape_string($db, $_SESSION['StartTime']);
			$sql=<<<SQL
				SELECT users.ID, users.PassWord, errorlabs.EType, users.ECode, errorlabs2.EType
				AS EType2, users.ECode2
				FROM users
				LEFT JOIN errorlabs ON errorlabs.ID = users.EID
				LEFT JOIN errorlabs AS errorlabs2 on errorlabs2.ID = users.EID2
				WHERE users.UserName = '$username' LIMIT 1
SQL;
			if($result=mysqli_query($db, $sql)){
				mysqli_close($db);
				if(mysqli_num_rows($result)==1){
					$assoc = mysqli_fetch_assoc($result);
					$userid=$assoc['ID'];
					$pword=$assoc['PassWord'];
					$etype=$assoc['EType'];//for errors unrelated to login attempts
					$ecode=$assoc['ECode'];
					$etype2=$assoc['EType2'];//for errors related to login attempts or other hacking attempts
					$ecode2=$assoc['ECode2'];
					//check that the password is correct
					if($etype2=='loginbadpwordfreq'){
						if($ecode2<$time){//enough time has passed to let the user try again
							//remove the errorcode
							$db=usedb();
							$sql=<<<SQL
								UPDATE users
								SET EID2 = 0, ECode2=''
								WHERE ID = $userid
SQL;
							if(mysqli_query($db, $sql))mysqli_close($db);
							else{
								mysqli_close($db);
								emailadminerror('mysqlerror', array('location'=>"loginremoveecode2$username", 'query'=>$sql));
							}
						}
						else{
							echo 'failureThere have been too many recent incorrect login attempts for this user.  For this user\'s protection, the account has been disabled for 10 minutes.  If you suspect your account is being hacked, please contact us immediately.<br><br>';
							return FALSE;
						}
					}
					if(!verifyusersresponse($_SESSION['challenge'],$postarr['req_userresponse'],$pword)){
						$etype='loginbadpword';
						$db=usedb();
						$sql=<<<SQL
							INSERT INTO errors (UserID, EID, ETime, Description)
							SELECT $userid, ID, $time, ''
							FROM errorlabs
							WHERE EType = '$etype'
SQL;
						if(mysqli_query($db, $sql)){
							//check how many errors of this type the user has made
							$sql=<<<SQL
								SELECT ETime
								FROM errorlabs
								LEFT JOIN errors ON errors.EID = errorlabs.ID
								WHERE errors.UserID = $userid AND errorlabs.EType = 'loginbadpword'
								ORDER BY ETime DESC LIMIT 4
SQL;
							if($result=mysqli_query($db, $sql)){
								if(mysqli_num_rows($result)==4){//they've submitted similar stories at least 3 times, check usage pattersn
									while($assoc = mysqli_fetch_assoc($result))$etime=$assoc['ETime'];
									if($etime>($time-10)){//they've made 4 errors in the last 10 minutes
										$etype='loginbadpwordfreq';
										$bannedtill=$time+(60*10);
										//log the error and update the user account
										$sql=<<<SQL
											INSERT INTO errors (UserID, EID, ETime)
											SELECT $userid, ID, $time FROM errorlabs
											WHERE EType = '$etype'
SQL;
										if(mysqli_query($db, $sql)){
											$sql=<<<SQL
												UPDATE users
												SET EID2 = (SELECT ID FROM errorlabs WHERE EType = '$etype'), ECode2='$bannedtill'
												WHERE users.ID = $userid
SQL;
											if(mysqli_query($db, $sql))mysqli_close($db);
											else{
												mysqli_close($db);
												emailadminerror('mysqlerror', array('location'=>"loginupdateusers$etype$username", 'query'=>$sql));
											}
										}
										else{
											mysqli_close($db);
											emailadminerror('mysqlerror', array('location'=>"logininserterror$etype$username", 'query'=>$sql));
										}
										echo 'failureRecently, there have been too many incorrect login attempts for this user.  Please, try again in 10 minutes.  If you suspect your account is being hacked, please contact us immediately.<br><br>';
										return FALSE;
									}
								}//end they've had 4 bad login attempts in 10 minutes
								else mysqli_close($db);
							}
							else{
								mysqli_close($db);
								emailadminerror('mysqlerror', array('location'=>"loginselectbadpwordattempt$username", 'query'=>$sql));
							}
						}
						else{
							mysqli_close($db);
							emailadminerror('mysqlerror', array('location'=>"logininsertbadpwordattempt$username", 'query'=>$sql));
						}
						echo 'failureThe password you provided is incorrect.<br><br>';
						return FALSE;
					}//end password was incorrect
					else{//the password was correct, check for other problems before logging the person in
						if($etype){
							if($etype=='baddprime' || $etype=='badfrequency'){
								if($time<intval($ecode)){
									echo 'failure'.messagetouser($etype);
									return FALSE;
								}
								else{//their account is nolonger disabled update the user table
									$db=usedb();
									$sql=<<<SQL
										UPDATE users
										SET EID = 0, ECode=''
										WHERE users.ID = $userid
SQL;
									if(mysqli_query($db, $sql))mysqli_close($db);
									else{
										mysqli_close($db);
										emailadminerror('mysqlerror', array('location'=>"loginupdatenoEID$username", 'query'=>$sql));
									}
								}
							}
							elseif($etype=='newuser'){
								$_SESSION['resolveerror']='authenticate';
								$_SESSION['errorcodeswitchon']=$userid;
								$_SESSION['errorusername']=$username;
								echo 'doerror'.createform('efrm', array('etype'=>'newuser', 'username'=>$username));
								return FALSE;
							}
							else{
								emailadminerror('userloginerror', array('location'=>"loginpwordokbuterror", 'errortype'=>$etype, 'errorcode'=>$ecode));
								//let them login since don't have a handler for this
							}
						}//end error code
						//if we're here, we can log them in
						$db=usedb();
						$sql=<<<SQL
							SELECT *
							FROM users
							WHERE ID = $userid
							LIMIT 1
SQL;
						if($result=mysqli_query($db, $sql)){
							$sql=<<<SQL
								UPDATE users
								SET LastLogin = $time
								WHERE ID = $userid
								LIMIT 1
SQL;
							if(!mysqli_query($db, $sql)){
								mysqli_close($db);
								emailadminerror('mysqlerror', array('location'=>"loginupdateuserinfo", 'query'=>$sql));
							}
							elseif(mysqli_num_rows($result)===1){
								$sql=<<<SQL
									INSERT INTO sessions (ID, UserID, StartTime, LogInTime, IP)
									VALUES ('$sessionid', $userid, $sessionstart, $time, '$userip')
SQL;
								if(!mysqli_query($db, $sql)){
									mysqli_close($db);
									emailadminerror('mysqlerror', array('location'=>'logininsertsessiontable', 'query'=>$sql));
								}
								elseif(mysqli_affected_rows($db)==0){
									mysqli_close($db);
									emailadminerror('mysqlnorows', array('location'=>'logininsertsessiontablenoaffectedrows', 'query'=>$sql));
								}
								else mysqli_close($db);
								$assoc=mysqli_fetch_assoc($result);
								$lltime=timeago($assoc['LastLogin']);
								$dname=$assoc['DisplayName'];
								setsessionvariables($assoc);
								$_SESSION['LoggedIn']=$time;
								echo "successWelcome back $dname.<p> The last time you logged in was $lltime.</p>";
							}
							else{
								echo 'failureWe\'re sorry, but there was a problem processing your request.  Please contact us if the problem continues.<br><br>';
								emailadminerror('loginnorows', array('username'=>$username, 'query'=>$userid));
								return FALSE;
							}
						}
						else{
							mysqli_close($db);
							emailadminerror('mysqlerror', array('location'=>"loginselectuserinfo", 'query'=>$sql));
						}
					}//end the password is correct
				}//end user exists
				else{//user does not exist
					echo 'failureThis user does not exist.<br><br>';
					return FALSE;
				}
			}
			else{//mysqlierror
				mysqli_close($db);
				emailadminerror('mysqlerror', array('location'=>"logincheckuser$username", 'query'=>$sql));
			}
		}//end optype open
		elseif($optype=='shut'){
			if($userid){
				startnewsession($userid, $time);//destroy the session and log the session end time
				echo 'success';
			}
			else echo 'failureYou\'re already logged out.';
			return FALSE;
		}
		elseif($optype=='lfrm'){
			if(!$userid)echo "success".createform('login');
			else echo 'failureYou\'re already logged in.<br><br>';
			return FALSE;
		}
		elseif($optype=='rfrm'){
			if(!$userid)echo "success".createform('register');
			else echo 'failureYou\'re already logged in.<br><br>';
			return FALSE;
		}
		elseif($optype=='efrm'){
			if($userid){
				echo 'failureYou\'re already logged in.<br><br>';
				return FALSE;
			}
			$etype=$postarr['req_etype'];
			if($etype=='newuser'){
				$uname=strtolower($postarr['req_uname']);
				$ecode=$postarr['req_code'];
				if($_SESSION['resolveerror']!='authenticate' || $_SESSION['errorusername']!=$uname){
					echo 'failureThere was a problem processing your request. Please, try again and alert us if the problem continues.<br><br>';
					return FALSE;
				}
				$db=usedb();
				$uname=mysqli_real_escape_string($db, $uname);
				$ecode=mysqli_real_escape_string($db, $ecode);
				$uid=mysqli_real_escape_string($db, $_SESSION['errorcodeswitchon']);
				$sql=<<<SQL
					UPDATE users
					SET EID=0, ECode='', LastLogin = $time
					WHERE ID = $uid AND UserName = '$uname' AND ECode = '$ecode'
SQL;
				if($result=mysqli_query($db, $sql)){
					if(($rows=mysqli_affected_rows($db))===1){//we're good to go
						$sql=<<<SQL
							SELECT *
							FROM users
							WHERE ID = $uid
							LIMIT 1
SQL;
						if($result=mysqli_query($db, $sql)){
							if(($rows=mysqli_num_rows($result))===1){
								mysqli_close($db);
								$assoc=mysqli_fetch_assoc($result);
								$lltime=timeago($assoc['LastLogin']);
								$dname=$assoc['DisplayName'];
								setsessionvariables($assoc);
								echo "successWelcome back $dname.<p> The last time you logged in was $lltime.</p>";
								return TRUE;
							}
							else{
								mysqli_close($db);
								emailadminerror('mysqlerror', array('location'=>"efrm$etype.getuserinfo$uname.affected$rows", 'query'=>$sql));
								echo 'failureThere was a problem processing your request.  Please, refresh the page and try logging in.<br><br>';
								return FALSE;
							}
						}
						else{
							mysqli_close($db);
							emailadminerror('mysqlerror', array('location'=>"efrm$etype.getuserinfo$uname", 'query'=>$sql));
							echo 'failureThere was a problem processing your request.  Please, refresh the page and try logging in.';
							return FALSE;
						}
					}//end we had 1 row
					else{
						mysqli_close($db);
						emailadminerror('mysqlerror', array('location'=>"efrm$etype.updatefor$uname.affected$rows", 'query'=>$sql));
						echo 'failureThere was a problem processing your request.  Please, refresh the page and try logging in.<br><br>';
						return FALSE;
					}
				}
				else{
					mysqli_close($db);
					emailadminerror('mysqlerror', array('location'=>"efrm$etype.updatinguser$uname", 'query'=>$sql));
					echo 'failureThere was a problem processing your request.  Please, try again and alert us if the problem continues.<br><br>';
					return FALSE;
				}
			}//end error newuser
			else{
				emailadminerror('erfmunknowntype', array('location'=>"efrmfinalelse", 'errortype'=>$etype));
				echo 'failureThere was a problem processing your request.  Please, try again and alert us if the problem continues.<br><br>';
				return FALSE;
			}
		}
		elseif($optype=='join'){
			if($userid){
				echo 'failureYou\'re already logged in.<br><br>';
				return FALSE;
			}
			$time=time();
			$anon=($postarr["req_anon"]=='true')? 0 : 1;//setting show stories so we want show stories = 0 when anon is 1
			$nsfw=($postarr["req_nsfw"]=='false')? 0 : 1;
			$uname=regexspacecleaner(strtolower($postarr['req_uname']));
			$email=regexspacecleaner($postarr['req_email']);
			$dname=regexspacecleaner($postarr['req_dname']);
			$affil=regexspacecleaner($postarr['req_affil']);
			$pword=regexspacecleaner($postarr['req_pword']);
			$db=usedb();//makesure the username and email addresses aren't being used
			$uname=mysqli_real_escape_string($db, $uname);
			$email=mysqli_real_escape_string($db, $email);
			$dname=mysqli_real_escape_string($db, $dname);
			$affil=mysqli_real_escape_string($db, $affil);
			$pword=mysqli_real_escape_string($db, $pword);
			$ecode=substr(md5($username.$time.$displayname),10,10);
			$sql=<<<SQL
				SELECT ID
				FROM users
				WHERE UserName = '$uname'
				LIMIT 1
SQL;
			if($result=mysqli_query($db, $sql)){
				if(mysqli_num_rows($result)==1){//user exists
					mysqli_close($db);
					echo 'failureThere is already a user with this username.  Please, try another.<br><br>';
				}
				else{//there's no user with that name check the email
					$sql=<<<SQL
						SELECT ID
						FROM users
						WHERE Email = '$email'
						LIMIT 1
SQL;
					if($result=mysqli_query($db, $sql)){
						if(mysqli_num_rows($result)==1){//email address exists
							mysqli_close($db);
							echo 'failureThere is already a user with this email address.  Please, try another.<br><br>';
						}
						else{//there's no user with that name or email add the user
							$sql=<<<SQL
								INSERT INTO users (UserName, PassWord, Email, DisplayName, Affiliation, NSFW, ShowStories, UserSince, LastActionTime, ECode)
								VALUES ('$uname', '$pword', '$email', '$dname', '$affil', $nsfw,$anon, $time, $time, '$ecode')
SQL;
							if(mysqli_query($db, $sql)){
								$insertid=mysqli_insert_id($db);
								mysqli_close($db);
								if($insertid>0){
									$_SESSION['resolveerror']='authenticate';
									$_SESSION['errorcodeswitchon']=$insertid;
									$_SESSION['errorusername']=$uname;
									echo 'success'.createform('efrm', array('etype'=>'newuser', 'username'=>$uname));
									emailsendauthentication($dname, $email, $uname, $ecode);
								}
								else{
									emailadminerror('mysqlerror', array('location'=>"joinaddinguser$uname", 'query'=>$sql));
									echo 'failureThere was a problem processing your request.  Please, try again and alert us if the problem continues.<br><br>';
								}
							}
							else{
								mysqli_close($db);
								emailadminerror('mysqlerror', array('location'=>"joinaddinguser$uname", 'query'=>$sql));
								echo 'failureThere was a problem processing your request.  Please, try again and alert us if the problem continues.<br><br>';
							}
						}
					}
					else{
						mysqli_close($db);
						emailadminerror('mysqlerror', array('location'=>"joincheckforuseremail$email", 'query'=>$sql));
						echo 'failureThere was a problem processing your request.  Please, try again and alert us if the problem continues.<br><br>';
					}
				}
			}
			else{
				mysqli_close($db);
				emailadminerror('mysqlerror', array('location'=>"joincheckforusername$uname", 'query'=>$sql));
				echo 'failureThere was a problem processing your request.  Please, try again and alert us if the problem continues.<br><br>';
			}
		}//end optype join
		elseif($optype=='funm'){
			if(!$userid)echo "success".createform('sendusername');
			else echo 'failureYou\'re already logged in.<br><br>';
			return FALSE;
		}
		elseif($optype=='fpwd'){
			if(!$userid)echo "success".createform('sendpassword');
			else echo 'failureYou\'re already logged in.<br><br>';
			return FALSE;
		}
		elseif($optype=='sunm'){
			$email=regexspacecleaner($postarr['req_email']);
			$db=usedb();
			$theemail=mysqli_real_escape_string($db, $email);
			$sql=<<<SQL
				SELECT DisplayName, UserName
				FROM users
				WHERE Email = '$theemail'
SQL;
			$result=mysqli_query($db, $sql);
			if(!$result || mysqli_num_rows($result)==0){
				mysqli_close($db);
				emailadminerror('mysqlerror', array('location'=>"suname", 'query'=>$sql));
				echo 'failureWe couldn\'t find a user with the email you supplied. Please, the email address and try again.<br><br>';
				return FALSE;
			}
			mysqli_close($db);
			$assoc = mysqli_fetch_assoc($result);
			$dname=$assoc['DisplayName'];
			$uname=$assoc['UserName'];
			emailuser('username', $email, array('dname'=>$dname, 'uname'=>$uname));
			echo 'successPlease, check your email for your username.<br><br>'.createform('login');
		}
		elseif($optype=='spwd'){
			$uname=regexspacecleaner($postarr['req_uname']);
			$email=regexspacecleaner($postarr['req_email']);
			$time=time();
			$pword=substr(md5($uname.$time.$email),10,10);
			$thepword=md5($pword);
			$db=usedb();
			$theuname=mysqli_real_escape_string($db, $uname);
			$theemail=mysqli_real_escape_string($db, $email);
			$thepword=mysqli_real_escape_string($db, $thepword);
			$sql=<<<SQL
				UPDATE users
				SET Password = '$thepword'
				WHERE UserName = '$theuname' AND Email = '$theemail'
SQL;
			$result=mysqli_query($db, $sql);
			if(!$result || mysqli_affected_rows($db)==0){
				mysqli_close($db);
				emailadminerror('mysqlerror', array('location'=>"spwdupdate", 'query'=>$sql));
				echo 'failureWe couldn\'t find a user with the supplied credentials. Please, check your username and email and try again.<br><br>';
				return FALSE;
			}
			mysqli_close($db);
			emailuser('password', $email, array('pword'=>$pword, 'uname'=>$uname));
			echo 'successPlease, check your email for the new password.<br><br>'.createform('login');
		}
	}//end login
	elseif($action=='vote'){
		if($postarr['req_optype']=='sig')$sig=1;
		elseif($postarr['req_optype']=='nsg')$sig=0;
		else $sig=NULL;
		$cid=intval($postarr["req_contentid"]);
		$ctype=($postarr["req_contenttype"]=="story")? 0 : 1;
		$divid=substr($postarr["req_changeobjectid"], 0, -10);//remove .votes.sig from the id
		if($sig===NULL){
			$sql=<<<SQL
			DELETE
			FROM votes
			WHERE ContentType=$ctype AND ContentID=$cid AND UserID=$userid
SQL;
		}
		else{
			$sql=<<<SQL
			INSERT INTO votes (ContentType, ContentID, UserID, Sig)
			VALUES ($ctype, $cid, $userid, $sig)
			ON DUPLICATE KEY UPDATE Sig=$sig
SQL;
		}
		$db=usedb();
		if(mysqli_query($db, $sql))echo 'success';
		else{
			echo 'failure';
			emailadminerror('mysqlerror', array('location'=>"vote", 'query'=>$sql));
		}
		mysqli_close($db);
	}
	elseif($action=='commentsshow'){
		$sid=intval($postarr["req_storyid"]);
		echo getcontents("comments", "storyid", $sid);
	}
	elseif($action=='commentform')echo createform("comment", htmlspecialchars($postarr["req_parentdivid"]));
	elseif($action=='commentsubmit'){
		$endcode=FALSE;
		$contenttype=0;//already mysql safe
		$contentid=intval($postarr["req_storyid"]);//already mysql safe
		$commentid=($postarr["req_length"]==4)? intval($postarr["req_commentid"]) : 0;//already mysql safe
		$thetext=$postarr["req_comment"];
		$privacy=($postarr["req_anon"]=="true")? 1 : 0;
		$classid=($postarr["forclass"]>=0)? intval($postarr["forclass"]) : 0;
		$nsfw=($postarr["req_nsfw"]=="true")? 1 : 0;
		$time=time();
		$db=usedb();
		$thetext=mysqli_real_escape_string($db, $thetext);
		//Start a transaction so users don't change things while we're working with it
		mysqli_begin_transaction($db);
		if($classid){//try to get the class information
			$sql=<<<SQL
				SELECT classmembers.ID AS CMID
				FROM classmembers
				WHERE classmembers.UserID=$userid AND classmembers.ClassID=$classid
SQL;
			$result=mysqli_query($db, $sql);
			$rows = ($result)? mysqli_num_rows($result) : 0;
			if($rows){
				$assoc = mysqli_fetch_assoc($result);
				$cmid=$assoc["CMID"];
				if(!$cmid){
					mysqli_close($db);
					return "failureYou are not affiliated with the class you selected.  Please try again.";
				}
			}
			else{//we couldn't get the class information
				mysqli_close($db);
				return "failureYou are not affiliated with the class you selected.  Please try again.";
			}
		}
		
		//lock all the comments on the story
		$sql=<<<SQL
			SELECT Rgt
			FROM comments
			WHERE ContentType=$contenttype AND ContentID=$contentid
			ORDER BY Rgt DESC
SQL;
		$result=mysqli_query($db, $sql);
		$rows = ($result)? mysqli_num_rows($result) : 0;
		if($rows){
			if($commentid){//need to get the rgt value for the comment in question
				$sql=<<<SQL
					SELECT Rgt
					FROM comments
					WHERE ID=$commentid
					LIMIT 1
SQL;
				$result=mysqli_query($db, $sql);
				$rows = ($result)? mysqli_num_rows($result) : 0;
				if($rows){
					$assoc = mysqli_fetch_assoc($result);
					$rgt=$assoc["Rgt"];
					if($rgt){//we got the right value, so make room
						//update right values
						$sql=<<<SQL
							UPDATE comments
							SET Rgt=Rgt+2
							WHERE ContentType=$contenttype AND ContentID=$contentid AND Rgt>=$rgt
SQL;
						if(mysqli_query($db, $sql)){//we made room for the right values
							//update left values
							$sql=<<<SQL
								UPDATE comments
								SET Lft=Lft+2
								WHERE ContentType=$contenttype AND ContentID=$contentid AND Lft>$rgt
SQL;
							if(mysqli_query($db, $sql)){//we made room for the left values
								//update lastaction times for users and comments where left<right AND right>right) out of indent b/c so long
								$sql=<<<SQL
									UPDATE comments
									LEFT JOIN users on users.ID=comments.UserID
									SET comments.LastActionTime=$time, users.LastActionTime=$time
									WHERE comments.ContentType=$contenttype AND comments.ContentID=$contentid AND comments.Lft<$rgt AND comments.Rgt>$rgt
SQL;
								if(mysqli_query($db, $sql)){//we updated the times
									$endcode="tempsuccess";
								}
								else{
									$endcode="updatecomment";
									emailadminerror('mysqlerror', array('location'=>"updatecomment", 'query'=>$sql));
								}
							}
							else $endcode="noroom";
						}
						else $endcode="noroom";
					}
					else $endcode="noroom";
				}
				else $endcode="nocomment";//we can't find the comment the user tried to comment on
			}
			else{//get the most recent rgt value
				$assoc = mysqli_fetch_assoc($result);
				$rgt=$assoc["Rgt"]+1;
				if($rgt) $endcode="tempsuccess";
				else $endcode="process";
			}
		}//end we had rows
		else{//there were no rows, so the "right" value is 1
			$rgt=1;
			$endcode="tempsuccess";
		}
		
		if($endcode=="tempsuccess" && is_numeric($rgt) && $rgt>0){//only push on if we don't yet have any errors
			$lft=$rgt++;
			$sql=<<<SQL
				INSERT INTO comments (ContentType, ContentID, Lft, Rgt, UserID, CreationTime, LastActionTime, LastCheckedTime, Privacy, NSFW)
				VALUES ($contenttype, $contentid, $lft, $rgt, $userid, $time, $time, $time, $privacy, $nsfw)
SQL;
			mysqli_query($db, $sql);
			if($insertid=mysqli_insert_id($db)){//we inserted the comment
				//insert the commenttext
				$sql=<<<SQL
					INSERT INTO commenttext (ID, TheText)
					VALUES ($insertid, \"$thetext\")
SQL;
				if(mysqli_query($db, $sql)){//we inserted the comment text
					//update actiontime for the story and its author
					$sql=<<<SQL
						UPDATE stories
						LEFT JOIN users
						ON users.ID = stories.UserID
						SET stories.LastActionTime=$time, users.LastActionTime=$time WHERE stories.ID=$contentid
SQL;
					if(mysqli_query($db, $sql)){//we successfully updated the action times for the story and user
						if($classid){//try to link it to the class and update the action times for the class and classowner
							$sql=<<<SQL
								INSERT INTO classcontentlinks (ClassID, ContentID, ContentType, ClassMemberID)
								VALUES ($classid, $insertid, 1, $cmid)
SQL;
							if(mysqli_query($db, $sql)){//we linked the class
								//update action times for class
								$sql=<<<SQL
									UPDATE classes
									LEFT JOIN users on users.ID=classes.OwnerID
									SET classes.LastActionTime=$time, users.LastActionTime=$time
									WHERE classes.ID=$classid
SQL;
								if(mysqli_query($db, $sql)){//we were able to update the action times for the class and the owner
									$endcode="success";
								}
								else $endcode="updateclass";//we couldn't update the class action times
							}
							else $endcode="classlink";//we couldn't link the comment to the class
						}
						else $endcode="success";//there was no class to update so we're done
					}
					else $endcode="updatestory";//we couldn't update the action times for the story and the user
				}
				else $endcode="insertcommenttext";//we couldn't insert the comment text
			}
			else $endcode="insertcomment";//we couldn't insert the comment
		}//end $endcode=="tempsuccess"
		
		if($endcode=="success"){
			mysqli_commit($db);
			mysqli_close($db);
			$response="success".getcontents("comments", "comment", $insertid, $userid);
		}
		else{
			mysqli_rollback ($db);
			mysqli_close($db);
			$response="failure";
			if($endcode=='nocomment')$response.="It appears that the comment you tried to reply to no longer exists.  Please, refresh and try again.";
			elseif($endcode=='insertcomment')$response.="Please try again, we were unable to insert the comment.";
			elseif($endcode=='insertcommenttext')$response.="Please try again, we were unable to insert the comment text.";
			elseif($endcode=='updatestory')$response.="Please try again, we were unable to update the story's information.";
			elseif($endcode=='updatecomment')$response.="Please try again, we were unable to update the comment's information.";
			elseif($endcode=='classlink')$response.="Please try again, we were unable link the comment with your class.";
			elseif($endcode=='updateclass')$response.="Please try again, we were unable to update the class information.";
			elseif($endcode=='noroom')$response.="Please try again, we were unable to make room for your comment.";
			elseif($endcode=='process')$response.="We're sorry, but there was a problem processing your request. Please, try again.";
			else $response.="We're sorry, but there was a problem processing your request. Please, try again.";
		}//end endcode wasn't success
		echo $response;
	}
	elseif($action=='authanon'){
		$privacy=($postarr["req_optype"]=="hide")? 1 : 0; //1 for anon 0 for not
		$ctab=($postarr["req_contenttype"]=="story")? "stories" : "comments";
		$cid=intval($postarr["req_contentid"]);
		$db=usedb();
		$sql=<<<SQL
			UPDATE $ctab
			SET Privacy = $privacy
			WHERE ID = $cid AND UserID = $userid
SQL;
		$result=mysqli_query($db, $sql);
		mysqli_close($db);
		if($result) echo 'success';
		else echo 'failure';
	}
	elseif($action=='follow'){
		$ctab=($postarr["req_contenttype"]=="story")? "stories" : "comments";
		$cid=intval($postarr["req_contentid"]);
		$response="failure";
		$db=usedb();
		//Start a transaction so users don't change things while we're working with it
		mysqli_begin_transaction($db);
		if($postarr["req_optype"]=="cease"){//we're ceasing to follow someone
			//try to delete the following link
			$sql=<<<SQL
				DELETE following.*
				FROM $ctab
				LEFT JOIN following
				ON following.FollowedID = $ctab.UserID
				WHERE $ctab.ID = $cid AND following.FollowerID = $userid
SQL;
			if(mysqli_query($db, $sql)){
				//check if the user is following anyone else
				$sql=<<<SQL
					SELECT FollowerID
					FROM following
					WHERE FollowerID = $userid
SQL;
				if($result=mysqli_query($db, $sql)){
					if(mysqli_num_rows($result)==0){
						//change IsFollowing to 0
						$sql=<<<SQL
							UPDATE users
							SET IsFollowing = 0
							WHERE ID = $userid
SQL;
						if(mysqli_query($db, $sql)){
							$_SESSION['IsFollowing']=0;
							$response="success";
						}
					}
					else $response="success";
				}
			}
		}
		else{//we're starting to follow someone
			//try to insert the link
			$sql=<<<SQL
				INSERT INTO following (FollowerID, FollowedID)
				SELECT $userid, UserID
				FROM $ctab
				WHERE ID = $cid
				ON DUPLICATE KEY UPDATE FollowerID = FollowerID
SQL;
			if(mysqli_query($db, $sql)){
				//check to see if the user is already following someone
				if(!$_SESSION['IsFollowing']){
					$sql=<<<SQL
						UPDATE users
						SET IsFollowing = 1
						WHERE ID = $userid
SQL;
					if(mysqli_query($db, $sql)){
						$_SESSION['IsFollowing']=1;
						$response="success";
					}
				}
				else $response="success";
			}//otherwise the insert failed
		}
		if($response=="success"){
			mysqli_commit($db);
			echo 'success';
		}
		else{
			mysqli_rollback($db);
			echo 'failure';
		}
		mysqli_close($db);
	}
	elseif($action=='grade'){
		//need to update the grade and update the last action time for the submitter and the last checked time for the class
		$ctab=($postarr["req_contenttype"]=="story")? "stories" : "comments";
		$cid=intval($postarr["req_contentid"]);
		$gid=intval($postarr["req_gid"]);
		$score=intval($postarr["req_score"]);//make sure it's only an int not a float or anything weird
		$time=time();
		$sql=<<<SQL
			UPDATE classcontentlinks
			LEFT JOIN classes
			ON classes.ID = classcontentlinks.ClassID
			LEFT JOIN classmembers
			ON classmembers.ID = classcontentlinks.ClassMemberID
			LEFT JOIN users
			ON users.ID = classmembers.UserID
			LEFT JOIN $ctab
			ON $ctab.ID = classcontentlinks.ContentID
			SET classcontentlinks.Grade = $score, classes.LastActionTime = $time, users.LastActionTime = $time, $ctab.LastActionTime = $time
			WHERE classcontentlinks.ID = $gid and classes.OwnerID = $userid
SQL;
		$db=usedb();
		if(mysqli_query($db, $sql)) echo 'success';
		echo 'failure';
		mysqli_close($db);
	}
	elseif($action=='hide'){
		$ctype=($postarr["req_contenttype"]=="story")? 0 : 1;
		$cid=intval($postarr["req_contentid"]);
		
		if($postarr["req_optype"]=="hidep"){//removing from feed
			$sql=<<<SQL
				INSERT INTO hidden (ContentType, ContentID, UserID, Hidden)
				VALUES ($ctype, $cid, $userid, 1)
				ON DUPLICATE KEY UPDATE Hidden = 1
SQL;
		}
		else{//adding back to feed
			$sql=<<<SQL
				DELETE FROM hidden
				WHERE hidden.ContentType = $ctype AND hidden.ContentID = $cid AND hidden.UserID = $userid
SQL;
		}
		$db=usedb();
		if(mysqli_query($db, $sql)) echo 'success';
		else echo 'failure';
		mysqli_close($db);
	}
	elseif($action=='tag'){
		$optype=$postarr["req_optype"];
		$storyid=intval($postarr["req_storyid"]);
		$response="failure";
		if($optype=="grab"){//grabbing tags for a story
			$response="success".getcontents("tags", "storyid", $storyid);
		}
		else{
			$storyid=intval($postarr["req_storyid"]);
			$time=time();
			if($optype=="add1"){//user clicked on tag to add
				$tagid=intval($postarr["req_tags"]);
				$db=usedb();
				$sql=<<<SQL
					INSERT INTO taglinks (ContentType, ContentID, UserID, TagID, CreationTime)
					VALUES (0, $storyid, $userid, $tagid, $time)ON DUPLICATE KEY UPDATE ContentType = ContentType
SQL;
				if(mysqli_query($db, $sql)) $response="success";
				else $response="failure";
				mysqli_close($db);
			}
			elseif($optype=="rem1"){//user clicked on tag to remove
				$tagid=intval($postarr["req_tags"]);
				$db=usedb();
				$sql=<<<SQL
					DELETE FROM taglinks
					WHERE ContentType = 0 AND ContentID = $storyid AND UserID = $userid AND TagID = $tagid
SQL;
				if(mysqli_query($db, $sql)) $response="success";
				else $response="failure".$postarr["req_optype"];
				mysqli_close($db);
			}
			else{//user is adding several tags
				//make the string lower case
				$tagid=mb_strtolower($postarr["req_tags"]);
				$tagid=regexspacecleaner($tagid, ",", "-");//clean tag id and get rid of spaces around commas
				if(!($tagid=="," || $tagid=="")){
					$tagid=explode(",", $tagid);//explode the tags
					$badtag=array();//create array to store any bad tags
					//identify badwords
					$tarr=array_intersect($tagid, badwords());
					if(count($tarr)>0){
						$badtag=array_merge($badtag, $tarr);
						$tagid=array_diff($tagid, $tarr);
					}
					//identify reserved words
					$tarr=array_intersect($tagid, reservedwords());
					if(count($tarr)>0){
						$badtag=array_merge($badtag, $tarr);
						$tagid=array_diff($tagid, $tarr);
					}
					foreach($tagid as $key=>$value){
						//do some more advanced checking for badwords
						if(regexchecker($value, "taboo")){ //identifies words like youfucker or educat8r
							$badtag[]=$value;
							unset($tagid[$key]);
						}
					}
					$tagid=array_merge($tagid);
					//make sure we have some good tags to add at this point
					if(($thecount=count($tagid))>0){
						$db=usedb();
						foreach($tagid as $key=>$value) $tagid[$key]=mysqli_real_escape_string($db, $value);
						$tagid='("'.implode('"), ("', $tagid).'")';
						//insert the tags
						$sql=<<<SQL
							INSERT INTO tags (TheText)
							VALUES $tagid
							ON DUPLICATE KEY UPDATE ID = ID
SQL;
						if(mysqli_query($db, $sql)){
							//get the tag ids
							$sql=<<<SQL
								SELECT ID
								FROM tags
								WHERE TheText IN ($tagid)
SQL;
							$result=mysqli_query($db, $sql);
							$rows = ($result)? mysqli_num_rows($result) : 0;
							if($rows){
								$tagid=array();
								while($assoc = mysqli_fetch_assoc($result)) $tagid[]=stripslashes($assoc['ID']);
								$common="($time, 0, $storyid, $userid, ";
								$tagid=$common.implode("), $common",$tagid).')';
								$sql=<<<SQL
									INSERT INTO taglinks (CreationTime, ContentType, ContentID, UserID, TagID)
									VALUES $tagid ON DUPLICATE KEY UPDATE ContentType=ContentType
SQL;
								if(mysqli_query($db, $sql)){
									mysqli_close($db);
									$response="success".getcontents("tags", "storyid", $storyid);
								}
								else{
									mysqli_close($db);
									emailadminerror('mysqlerror', array('location'=>"tagsinserttaglinks", 'query'=>$sql));
								}
							}
							else{
								mysqli_close($db);
								emailadminerror('mysqlerror', array('location'=>"tagsselectinsertedids", 'query'=>$sql));
							}
						}
						else{
							mysqli_close($db);
							emailadminerror('mysqlerror', array('location'=>"tagsinsertthetext", 'query'=>$sql));
						}
					}
				}
				if(count($badtag)>0){
					$response.="<!#-jscollabor8iveextra-#!>You are not permitted to use the following as tags:";
					foreach($badtag as $value) $response.=" $value";
					$response.="<br>";
				}
			}
		}//end not grabbing tags
		echo $response;
	}
	elseif($action=='story'){
		if($postarr["req_optype"]=="save"){
			$rule=$postarr['req_rule'];
			$extracol=$extraval='';
			if($rule!='none' && $rule==$_SESSION['rule'] && $_SESSION['storyextra']){
				if($rule=='youtube'){
					$extracol=', Extra';
					//$extraval=", 'youtube.".$_SESSION['storyextra']."'";
					$extraval=", 'youtube.";
				}
			}
			$title=$postarr['req_title'];
			$internallink=regexmaker('internallink', $title);
			$url=$postarr['req_url'];
			$thetext=$postarr['req_summary'];
			//make sure there are no badwords
			$profanity=FALSE;
			if(regexchecker(strtolower($title), 'taboo'))$profanity=TRUE;
			elseif(regexchecker(strtolower($thetext), 'taboo'))$profanity=TRUE;
			elseif(count(array_intersect(explode(" ", strtolower($title)), badwords()))>0)$profanity=TRUE;
			elseif(count(array_intersect(explode(" ", strtolower($thetext)), badwords()))>0)$profanity=TRUE;
			if($profanity===TRUE){
				echo 'failureYour submission contains some unnecessary profanity.  Please, edit your submission appropriately before submitting. Thanks.';
				return FALSE;
			}
			$privacy=($postarr["req_anon"]=="true")? 1 : 0;
			$classid=($postarr["req_forclass"]>0)? intval($postarr["req_forclass"]) : 0;
			$cmid=0;
			$nsfw=($postarr["req_nsfw"]=="true")? 1 : 0;
			$time=time();
			$endcode=FALSE;
			$db=usedb();
			//if there is a classid, make sure the class exists first and get the students classmemberid
			if($classid){//try to get the class information
				$sql=<<<SQL
					SELECT classmembers.ID AS CMID
					FROM classmembers
					WHERE classmembers.UserID=$userid AND classmembers.ClassID=$classid
SQL;
				$result=mysqli_query($db, $sql);
				$rows = ($result)? mysqli_num_rows($result) : 0;
				if($rows){
					$assoc = mysqli_fetch_assoc($result);
					$cmid=$assoc["CMID"];
					if(!$cmid){
						mysqli_close($db);
						echo "failureYou are not affiliated with the class you selected.  Please try again.";
						return FALSE;
					}
				}
				else{//we couldn't get the class information
					mysqli_close($db);
					echo "failureYou are not affiliated with the class you selected.  Please try again.";
					return FALSE;
				}
			}//if we haven't returned, we're good to go
			$title=mysqli_real_escape_string($db, $title);
			$copyinternallink=$internallink=mysqli_real_escape_string($db, $internallink);
			$url=mysqli_real_escape_string($db, $url);
			$thetext=mysqli_real_escape_string($db, $thetext);
			$extracol=mysqli_real_escape_string($db, $extracol);
			if($extraval!='')$extraval.=mysqli_real_escape_string($db, $extraval)."'";
			//Start a transaction so users don't change things while we're working with it
			$counter=1;
			mysqli_begin_transaction($db);
			$sql=<<<SQL
				INSERT INTO stories (CreationTime, UserID, URL, Title, InternalLink, TheText, NSFW, Privacy$extracol)
				VALUES ($time, $userid, '$url', '$title', '$copyinternallink', '$thetext', $nsfw, $privacy $extraval)
SQL;
			$copyinternallink="$internallink_$time$counter";
			mysqli_query($db, $sql);
			if($insertid=mysqli_insert_id($db)){//we inserted the story
				if($cmid){//try to link it to the class and update the action times for the class and classowner
					$sql=<<<SQL
						INSERT INTO classcontentlinks (ClassID, ContentID, ContentType, ClassMemberID)
						VALUES ($classid, $insertid, 0, $cmid)
SQL;
					if(mysqli_query($db, $sql)){//we linked the class
						//update action times for class
						$sql=<<<SQL
							UPDATE classes
							LEFT JOIN users on users.ID=classes.OwnerID
							SET classes.LastActionTime=$time, users.LastActionTime=$time
							WHERE classes.ID=$classid
SQL;
						if(mysqli_query($db, $sql)){//we were able to update the action times for the class and the owner
							$endcode="success";
						}
					}
				}//end they had a class
				else $endcode="success";
			}//end we successfully inserted the story
			if($endcode=="success"){
				mysqli_commit($db);
				mysqli_close($db);
				$_SESSION['storyextra']='';
				$_SESSION['rule']='';
				echo "success<p>Congratulations, your story has been successfully submitted. Now is a great time to tag ths story to help other users find it or provide useful commentary in a comment.</p>";
				$file=getcontents("all", "storyid", $insertid);//need to get all to allow user to comment and tag
				echo $file['body'];//getting all returns the story and a title for the webpage, here we just need the story
			}
			else{
				mysqli_rollback($db);
				mysqli_close($db);
				emailadminerror('mysqlerror', array('location'=>"story.save.insert", 'query'=>$sql, 'counter'=>$counter));
				echo "failureWe're sorry, there was a problem saving the story information.  Please, try again and contact us if the problem continues.";
			}
		}
		elseif($postarr["req_optype"]=="form"){
			$time=time();
			$expiration=$time-(60*60*24*7);//the time one week ago secs*mins*hours*days
			$url=regexchecker($postarr["req_url"], "url");
			
			if($url){//we have at least a host name
				//make sure we have a protocol
				$url[1]=($url[1])? $url[1] : "http";
				//go through the path and remove relative references
				if($url[6])$url[6]=regexchecker($url[6], "urlpath");
				//fix any domains in odd form (e.g. with percent encoded characters)
				if(!(strpos($url[4], "%")===FALSE)){
					require_once('/homepages/41/d92908607/htdocs/collabor8r/protected/functions/idna_convert.class.php');
					$IDN = new idna_convert(array('idn_version' => 2008));
					$url[4] = $IDN->encode(rawurldecode($url[4]));
				}
				
				//check if the domain is in the database
				$protocol=$url[1]."://";
				$hostname=$url[4];
				$path=$url[6];//rawurldecode($url[6]);//we might want to use the raw urldecode for storing the link
				$query=($url[7])? "?".($url[7]):"";
				//$query=($url[7])? "?".rawurldecode($url[7]):"";
				//$query=str_replace('&','%26',$query);
				$useurl=$protocol.$hostname."/".$path.$query;
				$rating=$rule=$checked=NULL;
				$db=usedb();
				$tvar=mysqli_real_escape_string($db, $hostname);
				$sql=<<<SQL
					SELECT Rating, Rule, Checked
					FROM hosts WHERE ID = '$tvar'
SQL;
				$result=mysqli_query($db, $sql);
				mysqli_close($db);
				$rows = ($result)? mysqli_num_rows($result) : 0;
				if($rows){//the domain is in the database
					$assoc = mysqli_fetch_assoc($result);
					$rating=($assoc["Rating"]===NULL)? NULL : intval($assoc["Rating"]);
					$rule=$assoc["Rule"];
					$checked=$assoc["Checked"];
					$alturl=$useurl;
					if($rule)$alturl=regexmakecanonical($useurl, $rule);
					$whereclause=($useurl==$alturl)? $useurl : "$useurl' OR URL = '$alturl";
					//check to see if story has already been submitted
					//if already submitted return already submitted message
					$db=usedb();
					$tvar=mysqli_real_escape_string($db, $useurl);
					$tvar2=mysqli_real_escape_string($db, $alturl);
					$whereclause=($useurl==$alturl)? $useurl : "$useurl' OR URL = '$alturl";
					$sql=<<<SQL
						SELECT URL, ID
						FROM stories
						WHERE URL = '$whereclause'
SQL;
					$result=mysqli_query($db, $sql);
					mysqli_close($db);
					$rows = ($result)? mysqli_num_rows($result) : 0;
					if($rows){//the domain is in the database
						$assoc = mysqli_fetch_assoc($result);
						if($rows==2 && $assoc["URL"]!=$useurl)$assoc = mysqli_fetch_assoc($result);//if we have two rows, get the second if the first url wasn't the $useurl.
						$tvar=$assoc["ID"];
						if($assoc["URL"]==$useurl) $response="The story you suggested is already listed on our site.<br>";
						else $response="It appears that the story you suggested is already on our site.  If this is not the story you suggested, please let us know.<br>";
						echo "success".$response.getcontents("stories", "storyid", $tvar);
						return false;
					}
				}
				if($rating===NULL || $rating===0 || $checked<$expiration){//Try to get the story if we don't have a rating or the rating is ok or we need to check again
								/*test urls
									for urls with xn-- go to website first to get url before IDNA conversion to make sure conversion works
									http://xn--fsqu00a.xn--0zwm56d/%E9%A6%96%E9%A1%B5    //japanese?
									http://xn--5dbqzzl.idn.icann.org/%D7%A2%D7%9E%D7%95%D7%93_%D7%A8%D7%90%D7%A9%D7%99  //hebrew
									http://bit.ly/tinyurlwiki   //should forward
									http://google.com   //should forward
								*/
					//make sure we can get something at the location
					$ch=curl_init("$useurl");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);//Stop from echoing contents to screen
					//curl_setopt($ch, CURLOPT_HEADER, TRUE);//Get the headers from the response
					curl_setopt($ch, CURLOPT_USERAGENT, "Collabor8r (+http://collabor8r.com)");//set the useragent so we can view sites like wikipedia
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);//Follow Redirects to avoid moved files or problems with bitly links
					curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);//Automatically handle referrer info when redirected
					curl_setopt($ch, CURLOPT_MAXREDIRS, 10);//Don't keep looking if there are so many redirects
					//curl_setopt($ch, CURLOPT_NOBODY, TRUE);
					//curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
					$file=curl_exec($ch);
					$curlinfo=curl_getinfo($ch);
					curl_close($ch);
					if($curlinfo['http_code']==200){//we got something
						//since the urls have been cleaned up, use the new ones
						$useurl=$curlinfo['url'];
						$hostname=substr($useurl, $tvar=strpos($useurl, "//")+2, strpos($useurl, "/", $tvar)-$tvar);
						//check for redirects and check if new hostname is in database if true
						if($curlinfo['redirect_count']>0){
							$rating=$rule=$checked=NULL;//only set back to null if redirected (DON'T set this outside the if redirect_count block)
							$db=usedb();
							$tvar=mysqli_real_escape_string($db, $hostname);
							$sql=<<<SQL
								SELECT Rating, Rule, Checked
								FROM hosts
								WHERE ID = '$tvar'
SQL;
							$result=mysqli_query($db, $sql);
							mysqli_close($db);
							$rows = ($result)? mysqli_num_rows($result) : 0;
							if($rows){//the domain is in the database
								$assoc = mysqli_fetch_assoc($result);
								$rating=($assoc["Rating"]===NULL)? NULL : intval($assoc["Rating"]);
								$rule=$assoc["Rule"];
								$checked=$assoc["Checked"];
								$alturl=$useurl;
								if($rule)$alturl=regexmakecanonical($useurl, $rule);
								$whereclause=($useurl==$alturl)? $useurl : "$useurl' OR URL = '$alturl";
								//check to see if story has already been submitted
								//if already submitted return already submitted message
								$db=usedb();
								$tvar=mysqli_real_escape_string($db, $useurl);
								$tvar2=mysqli_real_escape_string($db, $alturl);
								$whereclause=($useurl==$alturl)? $useurl : "$useurl' OR URL = '$alturl";
								$sql=<<<SQL
									SELECT URL, ID
									FROM stories
									WHERE URL = '$whereclause'
SQL;
								$result=mysqli_query($db, $sql);
								mysqli_close($db);
								$rows = ($result)? mysqli_num_rows($result) : 0;
								if($rows){//the domain is in the database
									$assoc = mysqli_fetch_assoc($result);
									if($rows==2 && $assoc["URL"]!=$useurl)$assoc = mysqli_fetch_assoc($result);//if we have two rows, get the second if the first url wasn't the $useurl.
									$tvar=$assoc["ID"];
									if($assoc["URL"]==$useurl) $response="The story you suggested is already listed on our site.<br>";
									else $response="It appears that the story you suggested is already on our site.  If this is not the story you suggested, please let us know.<br>";
									echo "success".$response.getcontents("stories", "storyid", $tvar);
									return false;
								}//end story is in database under $useurl or $alturl
							}//end the domain is in the database
						}//end there were redirects
						if($rating===NULL || $checked<$expiration){//The host isn't in our database or the data are fairly old so let's check google and or 
							//include logic so if ok hosts are now bad, we need to put errors on their links
							$client="collaborator";
							$apikey="ABQIAAAAPYafwJaMvoiCkn0OqUWLjBQ8e_22J05HeoZx3RhPLdvwhWZFZw";
							$appver="0.8";
							$pver="3.0";
							$checkurl=substr($useurl, 0, strpos($useurl, "/", strpos($useurl, "//")+2)+1);//need to have the one because google expects the /
							$googurl="https://sb-ssl.google.com/safebrowsing/api/lookup?client=$client&apikey=$apikey&appver=$appver&pver=$pver&url=$checkurl";
							$ch=curl_init($googurl);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);//Stop from echoing contents to screen
							curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);//Follow Redirects to avoid moved files or problems with bitly links
							curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);//Automatically handle referrer info when redirected
							curl_setopt($ch, CURLOPT_MAXREDIRS, 4);//Don't keep looking if there are so many redirects
							$checkurl=curl_exec($ch);
							$ncurlinfo=curl_getinfo($ch);
							curl_close($ch);
							if($ncurlinfo['http_code']===200){ //phishing, malware, or both
								//if host was previously identified as safe, but now it's not alert admin
								if($rating===0) emailadminerror('hostsafenomore', array('http_code'=>$ncurlinfo['http_code'], 'time'=>$time, 'url'=>$useurl, 'advisory'=>'google safebrowsing'));//there was no problem before but there is no
							}//end flagged as phishing or malware
							else{//it wasn't flagged as harmful so submit story Additionally, alert admin if the error code wasn't 204(i.e. no problem) so we can fix whatever the problem was
								if($ncurlinfo['http_code']!=204) emailadminerror('safebrowsing', array('http_code'=>$ncurlinfo['http_code'], 'userid'=>$userid, 'time'=>$time, 'url'=>$useurl));
								else{//it was rated as safe
									//if it was formerly rated as unsafe alert the admin to check for mismarked existing stories
									if($rating>0) emailadminerror('hostsafeagain', array('http_code'=>$ncurlinfo['http_code'], 'time'=>$time, 'url'=>$useurl, 'advisory'=>'google safebrowsing'));//there was no problem before but there is no
									$rating=0;//set rating to 0 here so we can store the story without indicating there was a problem.  We've sent the admin an error, so they can deal with this if it's a problem.
									//mark the host as safe
									$db=usedb();  
									$hostname=mysqli_real_escape_string($db, $hostname);
									$sql=<<<SQL
										INSERT INTO hosts (ID, Rating, Checked)
										VALUES ('$hostname', $rating, $time)
										ON DUPLICATE KEY UPDATE Checked = $time
SQL;
									mysqli_query($db, $sql);
									mysqli_close($db);
								}//end rated safe
							}//end not flagged as harmful
						}//end host isn't in database or rating expired
						if($rating===0){//everything is good store the file and extract the title/canonicalurl/body etc. then let user edit
							$story=processstoryfile($file, $rule);
							if($story['canonicalurl']!="" && $story['canonicalurl']!=$useurl){
								$useurl=$story['canonicalurl'];
								//makesure we have the host in our url
								if($useurl[0]==='/')$useurl='http://'.$hostname.$useurl;
								//check that this isn't already in the database
								//if already submitted return already submitted message
								$db=usedb();
								$tvar=mysqli_real_escape_string($db, $useurl);
								$sql=<<<SQL
									SELECT URL, ID
									FROM stories
									WHERE URL = '$tvar'
SQL;
								$result=mysqli_query($db, $sql);
								mysqli_close($db);
								$rows = ($result)? mysqli_num_rows($result) : 0;
								if($rows){//the domain is in the database
									$assoc = mysqli_fetch_assoc($result);
									$tvar=$assoc["ID"];
									if($assoc["URL"]==$useurl) $response="It appears that the story you suggested is already on our site.  If this is not the story you suggested, please let us know.<br>";
									echo "success".$response.getcontents("stories", "storyid", $tvar);
									return false;
								}
							}
							//we can set the filename at this point
							$filename=md5($useurl).".$time.$userid.html";
							if(file_put_contents("/homepages/41/d92908607/htdocs/collabor8r/protected/mirror/$filename", $file)===FALSE) emailadminerror('writestoryfile', array('userid'=>$userd, 'url'=>$useurl, 'filename'=>$filename));
							if(file_put_contents("/homepages/41/d92908607/htdocs/collabor8r/protected/mirror/$filename.processed", $tvar)===FALSE) emailadminerror('writestorysummaryfile', array('userid'=>$userd, 'url'=>$useurl, 'filename'=>$filename));
							if($story['title']=='' || $story['summary']=='' || $story['summary']=='...')emailadminerror('storyprocessproblem', array('url'=>$useurl, 'filename'=>$filename));//we couldn't get a title or contents
							$story['rule']=($rule)? $rule : 'none';
							$story['canonicalurl']=$useurl;
							echo "success".createform("submitstory", $story);
							$_SESSION['urlsubmitting']=$useurl;
						}//we can check for $rating out of this so we can report errors appropriately regardless of whether we attempted to get something (e.g. rated 2 at first check so never got here)
					}//end we got something
					else{
						if($hostname!='www.psychologytoday.com'){//1and1 is having problems these urls, use || to handle them or change some code to indicate the problem
							emailadminerror('nocurlresponse', array('time'=>$time, 'url'=>$useurl, 'user'=>$_SESSION['UserName'], 'host'=>$hostname));//alert admin to urls we can't parse
						}
						$story['rule']='none';
						$story['canonicalurl']=$useurl;
						$story['title']='';
						$story['summary']='';
						echo "success".createform("submitstory", $story);
						$_SESSION['urlsubmitting']=$useurl;
					}
				}//end rating is null, 0, or expired to check for contents
				if($rating>0){//it will only be above 0 if we got were successful in getting a story and it was bad or the host was bad at our first check
					//get problem
					if($rating==1)$etype="storyphish";
					elseif($rating==2)$etype="storymalware";
					elseif($rating==3)$etype="storyphishandmalware";//both phishing and malware
					else $etype="unknown";
					//insert update hosts
					$rows=FALSE;
					$db=usedb();
					$hostname=mysqli_real_escape_string($db, $hostname);
					$sql=<<<SQL
						INSERT INTO hosts (ID, Rating, Checked)
						VALUES ('$hostname', $rating, $time)
						ON DUPLICATE KEY UPDATE Rating = $rating, Checked = $time
SQL;
					if(mysqli_query($db, $sql)){
						//store user error in errors
						$useurl=mysqli_real_escape_string($db, $useurl);
						$sql=<<<SQL
							INSERT INTO errors (UserID, EID, ETime, Description)
							SELECT $userid, ID, $time, '$useurl'
							FROM errorlabs
							WHERE EType = '$etype'
SQL;
						if(mysqli_query($db, $sql)){
							//check how many errors of this type the user has made
							$sql=<<<SQL
								SELECT ETime, Ngood
								FROM errorlabs
								LEFT JOIN errors
								ON errors.EID = errorlabs.ID
								LEFT JOIN (SELECT COUNT(ID) AS Ngood, $userid AS UserID
								FROM stories
								WHERE UserID = $userid ) AS T ON T.UserID = errors.UserID
								WHERE errors.UserID = $userid AND errorlabs.EType
								IN ('storyphish', 'storymalware', 'storyphishandmalware')
								ORDER BY ETime DESC
SQL;
							if($result=mysqli_query($db, $sql)){
								mysqli_close($db);
								$rows=mysqli_num_rows($result);
							}
							else{
								mysqli_close($db);
								emailadminerror('mysqlerror', array('location'=>"storyformselecterrortimes", 'query'=>$sql));
							}
						}
						else{
							mysqli_close($db);
							emailadminerror('mysqlerror', array('location'=>"storyforminsertusererror", 'query'=>$sql));
						}
					}
					else{
						mysqli_close($db);
						emailadminerror('mysqlerror', array('location'=>"storyforminsertbadhost", 'query'=>$sql));
					}
					if($rows){//the errors were all properly submitted and stored
						$message=array('dangeroussubmission');
						$response='success';
						if($rows>=3){//they've submitted similar stories at least 3 times, check usage pattersn
							$ngood=$etime=array();
							while($assoc = mysqli_fetch_assoc($result)){$ngood[]=$assoc['Ngood'];$etime[]=$assoc['ETime'];}
							if($etime[2]>$time-(60*60*24)) $message[]=$etype='badfrequency'; //Three Bad Submissions in 24 hours
							elseif($ngood>=9 || $rows/$ngood>=.33) $message[]=$etype='baddprime'; //hit rate too low
							elseif($rows>$ngood) $message[]=$etype='baddprime'; //hit rate too low takes care of submitters with very few good stories
							elseif($etime[1]>$time-(60*60*24)) $message[]='badfrequencyclose'; //2 Bad Submissions in 24 hours
							elseif($ngood>=9 || $rows/$ngood>=.3) $message[]='baddprimeclose'; //hit rate close to too low
							else $etype=FALSE;
							if($etype){//store the error, ban the user for 24 hours, and alert admin
								$time=time();
								$bannedtill=$time+(60*60*24);
								$db=usedb();
								$sql=<<<SQL
									INSERT INTO errors (UserID, EID, ETime)
									SELECT $userid, ID, $time
									FROM errorlabs
									WHERE EType = '$etype'
SQL;
								mysqli_query($db, $sql);
								$sql=<<<SQL
									UPDATE users
									SET EID = (SELECT ID FROM errorlabs WHERE EType = '$etype'), ECode='$bannedtill'
									WHERE users.ID = $userid
SQL;
								mysqli_query($db, $sql);
								mysqli_close($db);
								startnewsession($userid, $time);//destroy the session and log the session end time
								emailadminerror('tempban', array('reason'=>$etype, 'user'=>$userid));//alert admin
								$response='tempban';
							}//end special error type for multiple error violations
						}//end had more than three error violations of the same type
						foreach($message as $value) $response.=messagetouser($value, $rating);
						echo $response;
					}//end errors properly submitted and stored
					else echo "failureThere was an internal error while processing your request.  Please, close this box and resubmit the URL.<br>";
				}//end had negative rating
			}//end we found a url
			else echo "successWe didn't recognize your submission as a URL.  Please, double check and try again.  If you believe this is an error, please contact us with the url in question.<br>";			
		}
		else echo "failureWe're sorry, we didn't understand what you wanted to do.  Please, try again and contact us if the problem continues.<br><br>\n";
	}
	elseif($action=='options'){
		$optype=$postarr['req_optype'];//unm dnm pwd eml eok aff sho sfw
		//make sure the provided password is correct anywhere we should have one
		if($optype=='unm' || $optype=='pwd' || $optype=='eml' || $optype=='eok'){
			$db=usedb();
			$sql=<<<SQL
				SELECT DisplayName, PassWord
				FROM users
				WHERE ID = $userid LIMIT 1
SQL;
			$result=mysqli_query($db, $sql);
			mysqli_close($db);
			if(!$result || ($rows=mysqli_num_rows($result))==0){
				echo "failureThe password you entered does not match the password on file and there were $rows rows.<br><br>";
				emailadminerror('mysqlerror', array('location'=>"optionscheckingforpassword", 'query'=>$sql));
				return FALSE;
			}
			$assoc = mysqli_fetch_assoc($result);
			$pword=$assoc['PassWord'];
			if(!verifyusersresponse($_SESSION['challenge'],$postarr['req_userresponse'],$pword)){
				echo "failureThe password you entered does not match the password on file.<br><br>";
				return FALSE;
			}
			if($optype=='eml'){
				$email=$postarr['req_email'];
				//check if the email is already being used
				$db=usedb();
				$sql=<<<SQL
					SELECT ID
					FROM users
					WHERE Email = '$email'
					LIMIT 1
SQL;
				$result=mysqli_query($db, $sql);
				mysqli_close($db);
				if($result){
					if(mysqli_num_rows($result)!=0){
						echo 'failureAnother user is already using that e-mail address.<br><br>';
						return FALSE;
					}
				}
				else{
					emailadminerror('mysqlerror', array('location'=>"optionscheckingforuserwithemail", 'query'=>$sql, 'optype'=>$optype));
					echo 'failureThere was a problem checking this email address.  Please try again and let us know if the problem continues.<br><br>';
					return FALSE;
				}
				$dname=$assoc['DisplayName'];
				$ccode=substr(md5($pword.$displayname.$email),10,10);
				$_SESSION['TempEmailConfirmation']=md5($ccode);
				$_SESSION['EmailToConfirm']=$postarr['req_email'];
				emailuser('verifyemail', $email, array('dname'=>$dname, 'ccode'=>$ccode));
				echo "successA confirmation of code has been sent to ".htmlspecialchars($email).'<br><br>';
				return FALSE;
			}
			if($optype=='eok' && (($postarr['req_confirmation']!=$_SESSION['TempEmailConfirmation']) || ($postarr['req_email']!=$_SESSION['EmailToConfirm']))){
				echo "failureThe confirmation code you provided was not correct for the email address you submitted.<br><br>";
				return FALSE;
			}
		}
		$check=FALSE;
		if($optype=='unm'){
			$where='UserName';
			$what='user name';
			$success=$clean=$postarr['req_uname'];
		}
		elseif($optype=='dnm'){
			$where='DisplayName';
			$what='display name';
			$success=$clean=$postarr['req_dname'];
		}
		elseif($optype=='pwd'){
			$where='PassWord';
			$what='password';
			$success=$clean=$postarr['req_newpword'];
		}
		elseif($optype=='eok'){
			$where='Email';
			$what='e-mail address';
			$success=$clean=$postarr['req_email'];
		}
		elseif($optype=='aff'){
			$where='Affiliation';
			$what='affiliation';
			$success=$clean=$postarr['req_affiliation'];
		}
		elseif($optype=='sho'){
			$where='ShowStories';
			$what='default for submitting stories and comments';
			$success=$clean=($postarr['req_checked']=='true')? 1 : 0;
		}
		elseif($optype=='sfw'){
			$where='NSFW';
			$what='default for viewing stories and comments';
			$success=$clean=($postarr['req_checked']=='true')? 0 : 1;
		}
		else{
			echo "failureWe're sorry, but there was a problem processing your request.<br><br>";
			emailadminerror('nooption', array('location'=>"optionsfinalelse", 'optype'=>$optype));
			return FALSE;
		}
		$db=usedb();
		$clean=mysqli_real_escape_string($db, $clean);
		if($optype=='unm' || $optype=='eok'){
			$clean="'$clean'";
			$sql=<<<SQL
				SELECT ID
				FROM users
				WHERE $where = $clean
				LIMIT 1
SQL;
			if($result=mysqli_query($db, $sql)){
				if(mysqli_num_rows($result)!=0){
					mysqli_close($db);
					echo "failureEither you or another user is already using that $what.<br><br>";
					return FALSE;
				}
			}
			else{
				mysqli_close($db);
				emailadminerror('mysqlerror', array('location'=>"optionscheckingexistinguniquevalues", 'query'=>$sql, 'optype'=>$optype));
				echo "failureThere was a problem processing your request.  Please, try again and alert us if the problem continues.<br><br>";
				return FALSE;
			}
		}
		else{
			if($optype=='dnm' || $optype=='pwd' || $optype=='aff') $clean="'$clean'";
			//check that the user doesn't already have that value set or it will return false
			$sql=<<<SQL
				SELECT ID
				FROM users
				WHERE ID = $userid AND $where = $clean
				LIMIT 1
SQL;
			if(!$result=mysqli_query($db, $sql)){
				mysqli_close($db);
				emailadminerror('mysqlerror', array('location'=>"optionscheckingfornochange", 'query'=>$sql, 'optype'=>$optype));
				echo "failureThere was a problem processing your request.  Please, try again and alert us if the problem continues.<br><br>";
				return FALSE;
			}
			if(mysqli_num_rows($result)!=0){//the user already has that set
				mysqli_close($db);
				echo "failureThat is already your $what. If you are receiving this after making a subtle change (e.g. changing case) try making a more significant change and then change it again to what you'd like.<br><br>";
				return FALSE;
			}
		}
		$sql=<<<SQL
			UPDATE users
			SET $where = $clean
			WHERE ID = $userid
			LIMIT 1
SQL;
		if(!$result=mysqli_query($db, $sql)){
			mysqli_close($db);
			echo "failureWe're sorry, but there was a problem processing your request.<br><br>";
			emailadminerror('mysqlerror', array('location'=>"optionsupdatenoresult", 'query'=>$sql));
			return FALSE;
		}
		elseif(mysqli_affected_rows($db)!=1){
			mysqli_close($db);
			echo "failureWe were unable to update your $what.  Please, try again and alert us if the problem contintues.<br><br>";
			emailadminerror('mysqlerror', array('location'=>"optionsupdatenorows", 'query'=>$sql));
			return FALSE;
		}
		mysqli_close($db);
		echo "successYour $what has been successfully updated.<br><br>";
		$_SESSION["$where"]=$success;
		return TRUE;
	}
	elseif($action=='email'){
		$optype=$postarr['req_optype'];
		if($optype=='rfrm'){
			if(!isloggedin()) return 'failureYou must be logged in to submit reports.';
			$id=$postarr['req_contentid'];
			if($postarr['req_contenttype']=='story'){
				$ctype=0;
				$table='stories';
			}
			else{
				$ctype=1;
				$table='comments';
			}
			$db=usedb();
			$sql=<<<SQL
				SELECT NSFW
				FROM $table
				WHERE ID=$id
SQL;
			$result=mysqli_query($db, $sql);
			mysqli_close($db);
			if(!$result){
				emailadminerror('mysqlerror', array('location'=>"emailrfrmselectnsfw", 'query'=>$sql));
				$result='unknown';
			}
			elseif(!mysqli_num_rows($result)===1){
				emailadminerror('mysqlerror', array('location'=>"emailrfrmrows", 'query'=>$sql));
				$result='unknown';
			}
			else{
				$assoc=mysqli_fetch_assoc($result);
				$result=$assoc['NSFW'];
			}
			echo "success".createform('report', array('ctype'=>$ctype, 'nsfw'=>$result, 'objectid'=>substr($postarr['req_changeobjectid'],0,-5)));//substr($postarr['req_changeobjectid'],0,5)
			return TRUE;
		}
		elseif($optype=='rsnd'){
			//userid, time, contentid, contenttype, reason, description
			$time=time();
			$ctype=($postarr['req_contenttype']=='story')? 0 : 1;
			$cid=intval($postarr['req_contentid']);
			$reason=(in_array($postarr['reason'], array('nsfw', 'sfw', 'usfw', 'spam', 'porn', 'dupe', 'abuse', 'threat', 'other')))? $postarr['reason'] : 'other';
			$db=usedb();
			$details=mysqli_real_escape_string($db, $postarr['details']);
			$sql=<<<SQL
				INSERT INTO reports
				VALUES ($ctype, $cid, $userid, '$reason', '$details', $time)
SQL;
			$result=mysqli_query($db, $sql);
			if(!$result){
				$error=mysqli_error($db);
				mysqli_close($db);
				if(substr($error, 0, 9)=='Duplicate'){
					echo 'failureYou\'ve already reported this '.$postarr['req_contenttype'].' for this reason.<br><br>';
					return FALSE;
				}
				echo 'failureThere was a problem processing this report.  Please try again and alert us if the problem continues.';
				emailadminerror('mysqlerror', array('location'=>"emailrsnd", 'query'=>$sql));
				return FALSE;
			}
			mysqli_close($db);
			echo 'successThank you for submitting your report. We rely on the support of users like you who take the time to alert us to potential problems.';
			emailadminerror('report', $postarr);
			return TRUE;
		}
		elseif($optype=='csnd'){
			$params=array('name'=>$postarr['req_name'], 'email'=>$postarr['req_email'], 'details'=>$postarr['req_details']);
			if(isset($_SESSION['ID'])) $params['UserID']=$_SESSION['ID'];
			if(isset($_SESSION['UserName']) && $_SESSION['UserName']!=$postarr['req_name']) $params['UserName']=$_SESSION['UserName'];
			if(isset($_SESSION['Email']) && $_SESSION['Email']!=$postarr['req_email']) $params['UserEmail']=$_SESSION['Email'];
			emailadmin('contactform', $params);
			echo 'successThank your for contacting us.  We truly value your feedback and input.';
			return TRUE;
		}
		else echo 'failureWe didn\'t understand your request';
		return FALSE;
	}
	elseif($action=='search'){
		$optype=$postarr['req_optype'];
		if($optype=='form'){
			echo 'success'.createform('search');
		}
	}
	elseif($action=='submitforclass'){
		$contenttype=($postarr['req_contenttype']=='story')? 0 : 1;
		$ctype=(!$contenttype)? 'stories' : 'comments';
		$contentid=intval($postarr['req_contentid']);
		if($postarr['req_optype']=='form'){
			$sql=<<<SQL
				SELECT $ctype.ID, $ctype.UserID, IFNULL(Clinks.ClassID, 0) AS ExistingLink, IFNULL(classmembers.ClassID, 0) AS PotentialLink, classes.ClassName
				FROM $ctype
				LEFT JOIN (
					SELECT ClassID, ContentID
					FROM classcontentlinks
					WHERE ContentType=$contenttype AND ContentID=$contentid
				)AS Clinks ON Clinks.ContentID = $ctype.ID
				LEFT JOIN classmembers ON classmembers.UserID = $ctype.UserID
				LEFT JOIN classes ON classes.ID = classmembers.ClassID
				WHERE $ctype.ID = $contentid
SQL;
			$db=usedb();
			$result=mysqli_query($db, $sql);
			$rows = ($result)? mysqli_num_rows($result) : 0;
			if(!$rows){
				mysqli_close($db);
				emailadminerror('mysqlerror', array('location'=>"submitforclass.form.norows", 'query'=>$sql));
				echo "We're sorry, there was a problem processing your request. Please make sure you're the author and try again.";
			}else{
				$assoc = mysqli_fetch_assoc($result);
				if($assoc['UserID']!=$userid){
					mysqli_close($db);
					emailadminerror('mysqlerror', array('location'=>"submitforclass.form.notauthor", 'query'=>$sql));
					echo "We're sorry, only the author of this content can access this information.";
				}else{
					$existinglink=$assoc['ExistingLink'];
					if($assoc['PotentialLink']){
						$selectedstring='selected="selected"';
						$optionidstring='id="'.$postarr['req_contenttype'].".$contentid.author.submitforclass.options.currentchoice\"";
						if(!$existinglink){
							$selected=$selectedstring;
							$optionid=$optionidstring;
						}else $selected=$optionid='';
						$classops="\n\t<option $optionid value=\"0\" $selected>No Class Selected</option>";
						do{
							$classoptionid=$assoc['PotentialLink'];
							$classoptionname=$assoc['ClassName'];
							$selected=($existinglink==$classoptionid)? $selectedstring : '';
							if($existinglink==$classoptionid){
								$selected=$selectedstring;
								$optionid=$optionidstring;
							}else $selected=$optionid='';
							$classops.="\n\t<option $optionid value=\"$classoptionid\" $selected>$classoptionname</option>";
						}while($assoc=mysqli_fetch_assoc($result));
					}else $classops=0;
					mysqli_close($db);
					echo createform('submitforclass', array('Privacy'=>$assoc['Privacy'], 'NSFW'=>$assoc['NSFW'], 'classops'=>$classops, 'existinglink'=>$existinglink, 'ctype'=>$postarr['req_contenttype'], 'cid'=>$contentid));
				}
			}
		}
		elseif($postarr['req_optype']=='save'){
			$classid=intval($postarr['req_submitfor']);
				//need to get the classmemberid for this student in the above class
			if(!$classid){//they're de-submitting it so just delete the classcontentlink
				//do a delete join to make sure we only delete the users content link
				$sql=<<<SQL
					DELETE classcontentlinks.*
					FROM classcontentlinks
					INNER JOIN $ctype ON $ctype.ID = classcontentlinks.ContentID
					WHERE $ctype.ID = $contentid AND $ctype.UserID = $userid;
SQL;
				//double check that the student owns the content before deleting the link
			}else{//they've changed it to a new class so we need to add a classcontentlink
				//double check that the student owns the content before inserting/updating the link
				$sql=<<<SQL
					INSERT INTO classcontentlinks (ClassID, ContentID, ContentType, Grade, ClassMemberID)
					SELECT classmembers.ClassID, $ctype.ID AS ContentID, $contenttype AS ContentType, NULL AS Grade, classmembers.ID AS ClassMemberID
					FROM $ctype
					INNER JOIN classmembers ON classmembers.UserID = $ctype.UserID
					WHERE $ctype.ID = $contentid AND $ctype.UserID = $userid AND classmembers.ClassID = $classid
					ON DUPLICATE KEY UPDATE ClassID = classmembers.ClassID, Grade = NULL, ClassMemberID = classmembers.ID
SQL;
			}
			$db=usedb();
			$result=mysqli_query($db, $sql);
			mysqli_close($db);
			if($result) echo 'success';//"Congratulations, your submission has been changed.";
			else{
				emailadminerror('mysqlerror', array('location'=>"submitforclass.save.didnotinsertupdateordelete", 'query'=>$sql));
				echo 'failure';//"We're sorry, there was a problem processing your request. Please check the content again and verify that you are the owner before trying again. Please contact us if the problem continues.";
			}
		}
		
	}
	elseif($action=='changeclassmembername'){
		$classid = $postarr['req_classid'];
		$classmemberid = $postarr['req_classmemberid'];
		$newname=$postarr['req_newname'];//need to make safe for mysql
		$db=usedb();
		$newname=mysqli_real_escape_string($db, $newname);
		$sql=<<<SQL
			UPDATE classmembers
			SET DisplayName = '$newname'
			WHERE ID = $classmemberid AND ClassID = $classid AND UserID = $userid
SQL;
		$result=mysqli_query($db, $sql);
		mysqli_close($db);
		if($result) echo 'success';
		else{
			emailadminerror('mysqlerror', array('location'=>"changeclassmembername.save.didnotupdate", 'query'=>$sql));
			echo 'failure';
		}
	}
	else echo "Whoops, some bytes got lost. Please try again and let us know if the problem continues.<br>\n";
	return FALSE;
}

function processstoryfile($file, $rule=""){
	$file=regexcleanhtml($file);//strips odd spacing and runs of spacing
	$title=regexgettitle($file, $rule);//gets title from between title tags e.g. <title>HERE</title> unless a rule says otherwise
	$canonicalurl=regexgetcanonical($file, $rule);//gets the canonical url specified by <link rel="canonical" href="HEREITIS" a variation or another rule
	$summary=regexgetsummary($file, $rule);//gets a summary from the body if it can based on some loose guidelines or rule, if available
	if($rule=='youtube')$extra=substr($canonicalurl, strpos($canonicalurl, 'v=')+2);
	else $extra='';
	return array('title'=>$title, 'canonicalurl'=>$canonicalurl, 'summary'=>$summary, 'extra'=>$extra);
}

function regexcleanhtml($text){//strips odd and unnecessary spacing
	//get rid of unneeded spaces
	$text=regexspacecleaner($text);
	
	//lets get rid of weird spacing first so we can make simpler quearies later
	$regex_pattern="/<\s/"; //makes < followed by a space just < (e.g. slkj < slkj  = slkj <slkj)
	$text = preg_replace($regex_pattern, "<", $text);
	$regex_pattern="/\s>/"; //makes > preceded by a space just > (e.g. slkj > slkj  = slkj> slkj)
	$text = preg_replace($regex_pattern, ">", $text);
	$regex_pattern="/<\/\s/";
	$text = preg_replace($regex_pattern, "</", $text); //makes </ followed by a space just </ (e.g. slkj </ slkj  = slkj </slkj)
	
	return $text;
}

function regexcheckdisplayname($displayname){	//Appropriate displaynames can include letters, numbers, underscores AND unlike usernames also spaces, commas, and periods
	$regex_pattern="/^([a-zA-Z0-9]+-?[a-zA-Z0-9_ ,.]*)+[a-zA-Z0-9]$/";	//the displayname must begin and end with a letter
	return preg_match($regex_pattern,$displayname);//returns 1 if the displayname was valid
}

function regexcheckemail($email){
	$regex_pattern="/(?#UserNameFirstPart)(?:[a-zA-Z0-9!#$%&'*+\/=?^_`{|}~-])+(?#UserNameAdditionalParts)(?:\.[a-zA-Z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?#SubLevelDomains)(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+(?#TopLevelDomains)(?:[A-Z]{2}|com|edu|org|net|gov|mil|biz|info|mobi|name|aero|jobs|museum)\b/";
	return preg_match($regex_pattern,$email);//returns 1 if the email was valid
}

function regexchecker($stringtocheck, $checkfor){
	//echo "<p>We're in regexchecker checking $stringtocheck for $checkfor.</p>";
	$getsomething=FALSE;
	switch($checkfor){
	  case 'pagenumber':
		$getsomething=1;
		$regex="/^page0*([1-9][0-9]*)$/";
		break;
	  case 'username':
			$regex="/^[a-zA-Z0-9][a-zA-Z0-9_-]+[a-zA-Z0-9]$/";
			break;
		case 'id'://don't call this, just use is_numeric
		$regex="/^[1-9][0-9]*$/";
		break;
		case 'md5'://don't call this, just use is_numeric
		$regex="/^[a-f0-9]{32}$/i";
		break;
	  case 'internallink': //lcletter followed by lcletters, numbers, underscores and dashes
		$regex="/^[\w-%]*$/"; //doesn't check everything, just makes sure there are no extra odd characters % is for percent encoded chars
		break;
		case 'taboo':
			$regex="/(fuck)|(viagra)|(cialis)(ass-*hole)|(cock-*sucker)|(god-*dam)|(shit[-\s.!,?]+)|(cunt[-\s.!,?]+)/i";
			return TRUE;
		break;
		case 'url':
			//replace spaces at beginning or end
			$stringtocheck=preg_replace("/^\s+|\s+$/", "", $stringtocheck);
			$stringtocheck=str_split($stringtocheck);
			foreach($stringtocheck as $key=>$value) $stringtocheck[$key]=(preg_match("/^[\w\.-~:\/@\?#%=,;]$/", $value))? $value : rawurlencode($value);//originally had strings in non matching too
			$stringtocheck=implode($stringtocheck);
			$regex="/(?:(https?):\/\/)?(?:([^:@\s]+)(?::([^:@\s]+)?)?@)?((?:(?:[a-z\d]+|(?:%[\dA-F][\dA-F])+)(?:(?:-*(?:[a-z\d]+|(?:%[\dA-F][\dA-F])+))*)?)(?:\.(?:(?:[a-z\d]+|(?:%[\dA-F][\dA-F])+)(?:(?:-*(?:[a-z\d]+|(?:%[\dA-F][\dA-F])+))*)?))+)(?::(\d*))?(?:\/((?:[\w-\._~:@!\$\^'\(\)\*\+,;=%]+\/?)*)?)?(?:\?([\w-\._~:@!\$\^'\(\)\*\+,;=%\?\/]*)?)?(?:#([\w-\._~:@!\$\^'\(\)\*\+,;=%\?\/]*)?)?/i";
			$getsomething="all";
			break;
		case 'email':
			//replace spaces at beginning or end
			$stringtocheck=preg_replace("/^\s+|\s+$/", "", $stringtocheck);
			$regex="/^(([^:@\s]+)@((?:(?:[a-z\d]+|(?:%[\dA-F][\dA-F])+)(?:(?:-*(?:[a-z\d]+|(?:%[\dA-F][\dA-F])+))*)?)(?:\.(?:(?:[a-z\d]+|(?:%[\dA-F][\dA-F])+)(?:(?:-*(?:[a-z\d]+|(?:%[\dA-F][\dA-F])+))*)?))+))$/i"; //not perfect but should be good enough
			$getsomething="all";
			break;
		case 'urlpath':
			//test path ./../path././.info/goodby/../goodby/./../goodby/goodby/../../rmation
			//should return path./.info/rmation
			//replace runs of / with 1 / //there shouldn't be any if have valid url, but just double checking
			$stringtocheck=preg_replace("/\/+/", "/", $stringtocheck);
			//replace any runs of ../ and or ./ at beginning of path with
			$stringtocheck=preg_replace("/^(\.?\.\/)+/", "", $stringtocheck);
			//replace any /./ or runs of /././ with just /
			$stringtocheck=preg_replace("/\/(\.\/)+/", "/", $stringtocheck);
			//replace any pathinfo/../ with just ""
			while(strrpos($stringtocheck, "/../")>0)$stringtocheck=preg_replace("/[^\/]+\/\.\.\//", "", $stringtocheck, 1);
			//replace runs of / with 1 / //there shouldn't be any, but just double checking
			return preg_replace("/\/+/", "/", $stringtocheck);
		default :
		return FALSE;//if we call this without a defined checkfor return false
	}

	//echo "<p>We're in regexchecker checking $stringtocheck for $regex and get something is $getsomething.</p>";

	if(preg_match($regex,$stringtocheck,$matches)==1){
		 if($getsomething=="all")return $matches;
		 elseif($getsomething)return $matches[$getsomething];
	   else return TRUE;
	}
	//if we're here something went wrong so return FALSE
	return FALSE;
}

function regexcheckurl($url){
	$regex_pattern="/^(?#Protocol)(?:(?:ht|f)tp(?:s?)\:\/\/|~\/|\/)?(?#Username:Password)(?:\w+:\w+@)?(?#Subdomains)(?:(?:[-\w]+\.)+(?#TopLevel Domains)(?:com|edu|org|net|gov|mil|biz|info|mobi|name|aero|jobs|museum|travel|[a-z]{2}))(?#Port)(?::[\d]{1,5})?(?#Directories)(?:(?:(?:\/(?:[-\w~!$+|.,=]|%[a-f\d]{2})+)+|\/)+|\?|#)?(?#Query)(?:(?:\?(?:[-\w~!$+|.,*:]|%[a-f\d{2}])+=(?:[-\w~!$+|.,*:=]|%[a-f\d]{2})*)(?:&(?:[-\w~!$+|.,*:]|%[a-f\d{2}])+=(?:[-\w~!$+|.,*:=]|%[a-f\d]{2})*)*)*(?#Anchor)(?:#(?:[-\w~!$+|.,*:=]|%[a-f\d]{2})*)?$/i";
	return preg_match($regex_pattern,$url);//returns 1 if the url was valid
}

function regexcheckusername($username){	//This checks if a username is ok usernames can include letters, numbers, and underscores
	$regex_pattern="/^\s*([a-zA-Z0-9]+[a-zA-Z0-9_-]*)+[a-zA-Z0-9]\s*$/"; //the displayname must begin and end with alphanumeric
	return preg_match($regex_pattern,$username);//returns 1 if the username was valid
}

function regexgetcanonical($text, $rule=''){//get the canonical url if any
	$regex_pattern1="/<link\s+rel\s?=\s?[\"']canonical[\"']\s+href=[\"']([^\"']+)/i";
	$regex_pattern2="/<link\s+href=[\"']([^\"']+)[\"']\s+rel\s?=\s?[\"']canonical[\"']/i";
	$canonicalurl="";
	if(preg_match($regex_pattern1,$text,$match))$canonicalurl=$match[1];
	elseif(preg_match($regex_pattern2,$text,$match))$canonicalurl=$match[1];
	//get rid of unneeded spaces
	$canonicalurl=regexspacecleaner($canonicalurl);
	return $canonicalurl;
}

function regexgetsummary($text, $rule=''){//this should find the body text in a web page and extract a summary from it
									//make sure we call regexnoweirdwhite first on the text
	$minbodylength = 100;
	$optbodylength = 200;
	$maxbodylength = 600;

	$needstrimming=0;
	//Lets get rid of the dangerous stuff first i.e. javascript
	//some websites put <body> and </body> tags into javascript or html comments that throw off the ability to detect the body which used 
	//to come before stripping the script and html
	$regex_pattern="/<script.*?script>/i";
	$text = preg_replace($regex_pattern, "", $text);

	//Get rid of html comments
	$regex_pattern="/<!\s?--.*?-->/";
	$text = preg_replace($regex_pattern, "", $text);
	
	//lets get the body
	$regex_pattern="/<body[^>]*>(.*?)<\/body>/i";
	if(preg_match($regex_pattern,$text,$match))$text=$match[1];

	
	//Get rid of stuff that is unlikely to contain the content we're seeking
	//style elements, unordered lists, ordered lists, list elements, maps, forms, noscript sections, objects, embeds
	$regex_pattern="/<style.*?style>|<ul.*?ul>|<ol.*?ol>|<li.*?li>|<map.*?map>|<form.*?form>|<noscript.*?noscript>|<object.*?object>|<embed.*?embed>/i";
	$text = preg_replace($regex_pattern, "", $text);
	
	//Get rid of all of the remaining non paragraph html tags
			//$regex_pattern="/<[^p][^>]*>|<\/[^p][^>]*>/i"; This doesn't work, tags we want that are rejected by one are matched by the other 
			//this also doesn't get rid of param tags and pre tags
			//<[^p|^/][^>]*> selects all beginning tags that don't start with p
	$regex_pattern="/<\/?\w{2,}[^>]*>/";// this will get rid of any multicharacter tag leaving us only with tags like <a href></a><p stuff></p>
	$text = preg_replace($regex_pattern, "", $text);
	//More housework
	$regex_pattern="/<[^p]>|<[^p]\s[^>]*>|<\/[^p]>/i";//won't take p plain e.g. <p> or followed by parameters e.g. <p stuff> or an ending to p e.g. </p>
														//again make sure regexnoweirdwhite has run first
	$text = preg_replace($regex_pattern, "", $text);
	
	//if we have paragraphs, look for the content in the paragraphs, otherwise just delete any remaining tags and take the first 300+- chars
	//get the position of the first paragraph
	$stringpos=strpos($text, '<p');
	$stringrpos=strrpos($text, '>');
	//delete everything before the first paragraph
	if($stringpos)$text=substr($text,$stringpos);
	//delete everything after the first tag
	if($stringrpos>$stringpos+2)$text=substr($text,0,$stringrpos-$stringpos+1);//this will select everything including the closing ">" tag
	
	//Get the first big paragraph that is likely to contain content, if it exists
	$temptext="";
	$regex_pattern="/<p[^>]*>([^<]{100,}?)<\/p>/i";
	if(preg_match($regex_pattern,$text,$match)){
		$temptext=$match[1];
		//incase we had a really small temp text, lets get some more
		$stringlen=strlen($temptext);
		if($stringlen<$optbodylength){
			//find where in the text we found the temptext so we can get more from that point on
			$stringpos=strpos($text, $temptext);
			$text=substr($text, $stringpos, $maxbodylength); 
			//Trim the string again up to the last </p> between the optbodylength and maxbodylength if it exists
			$stringpos=strrpos($text, '</p>');//we don't have to escape the / here because it's not in the regex delimited by // e.g. "/forwardslash\//"
			if($stringpos>$optbodylength){
				$text=substr($text, 0, $stringpos);
			}else $needstrimming=1;
		}else $text=$temptext;
	}
	//Get rid of any other html tags
	//this is primarily done incase we didn't get something inside the paragraph from earlier
	$regex_pattern="/<[^>]*>|<\/[^>]*>/";
	$text = preg_replace($regex_pattern, "", $text);
	
	//let's turn all runs of white space into a single space one more time incase the previous operations resulted in extra spacing
	$regex_pattern="/\s{2,}/";
	$text = preg_replace($regex_pattern, " ", $text);
	
	//get rid of unneeded spaces
	$text=regexspacecleaner($text);
	
	//if we have more than we want, cut it down to size
	if(strlen($text)>$maxbodylength) $needstrimming=1;
	if($needstrimming){
		$text=substr($text, 0, $maxbodylength);
		$stringrpos=strrpos($text, ' ');
		$text=substr($text, 0, $stringrpos);
		$text="$text...";
	}
	return $text;
}

function regexgettitle($text, $rule=''){	//Appropriate displaynames can include letters, numbers, underscores AND unlike usernames also spaces, commas, and periods
								//make sure we call regexnoweirdwhite first on the text
								//regexnoweirdwhite removes spaces from around the < and > characters
								//regexnoweirdwhite also makes all runs of white spaces including breaks and nbsps into single spaces
	$regex_pattern="/<title\s?[^>]*>(.+?)<\/\s?title>/i";	//will search for anything between and including the title tags (text insensitive)
												//the period followed by the plus will match any number of any characters
												//placing it within the parentheses will allow us to get just the title without grabbing the tags too
	$title="";
	if(preg_match($regex_pattern,$text,$match))$title=$match[1];
	//get rid of unneeded spaces
	$title=regexspacecleaner($title);
	return $title;	
}

function regexmakecanonical($urlstring, $rule=''){//get the canonical url if any
	//do a switch here based on rule to create regex patterns
	//outside of switch to a match
	//if match return match[1]
	//if not match return string
	//get rid of unneeded spaces
	$urlstring=regexspacecleaner($urlstring);
	return $urlstring;
}

function regexmakeinternallink($text){
	//setlocal for American English
	setlocale(LC_ALL, 'en_US.UTF8');
	$text = str_replace ("ø", "oe", $text);

	$text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
		//this doesn't work with the following characters
			//dotless i
			//tried to do the dotless i above, but had some problems
	
	//get rid of any urlencoding
	$text=urldecode($text);

	//replace spaces with underscores and make the string all lowercase
	$text=strtolower(str_replace(" ", "_", $text));
	
	//take the text and get rid of everything but letters, numbers, underscores, and dashes
	$regex_pattern="/[^\w-]+/";
	$text=preg_replace($regex_pattern,'',$text);

	//replace repeated underscores with a single underscore
	$regex_pattern="/_{2,}/";
	$text=preg_replace($regex_pattern,'_',$text);
	
	//replace any occurances of dashes surrounded by underscores with just the dash	
	$regex_pattern="/_?-_?/";
	$text=preg_replace($regex_pattern,'-',$text);
	
	//delete underscores and dashes at the beginning and ends of the string
	$regex_pattern="/^[-_]+|[-_]+$/";
	$text=preg_replace($regex_pattern,'',$text);
	
	if($text==''){//if the text is a blank present a generic title 
		$currenttime=time();
		$text="collabor8r_story_$currenttime";	
	}
	
	$text=regexmakelinkunique($text);	
	return $text;
}

function regexmakelinkunique($link){
	if(internallinkexists($link)){//make sure we don't already have a story like this
		//set up a counter to try for a unique link
		$counter=1;
		//see if there's already a number at the end of the link
		$regex_pattern="/(\d+)$/";
		if(preg_match($regex_pattern,$link,$match)){
			$counter=$match[1];
			$length=strlen($link)-strlen($counter);
			$link=substr($link, 0, $length);
		}
		$original=$link;
		$link="$original$counter";
		while(internallinkexists($link)){//increment the current text by 1 until we have a unique internal link e.g. link, link1, link2, link3...
			$counter++;
			$text="$original$counter";
		}
	}//otherwise everthing is cool it's a unique link
	return $link;
}

function regexmaker($type, $text){
	if($type=='internallink'){
		$copy=$text;//make a copy to work with
		$copy = preg_replace('/[–—]+/', '-', $copy);//change weird dashes into normal ones
		$copy = preg_replace('/[…†‡ªº«»¬°·¿±×÷¹²³½¼¾‰€•¤¶§`¢£¥ƒª¦¯ˆ‰‹‘’“”™š›¤¦¨©®´¸]+/', '_', $copy);
		$copy = preg_replace('/[\s\^\]\\\\`~!@#$^&*()=+[{};:\'"|,<.>\/?]+/', '_', $copy);//replace spaces and common punctuation with _
		do{
			preg_match("/%+$|%+[^\dA-F]|%+[\dA-F][^\dA-F]/", $copy, $matches, PREG_OFFSET_CAPTURE);
			if(count($matches)===1)$copy=substr_replace($copy, '', $matches[0][1], 1);
			else $matches=FALSE;
		}while(!$matches===FALSE);
		$copy = preg_replace('/_*-+_*/', '-', $copy);//replace runs of _-_ with -
		$copy = preg_replace('/_+/', '_', $copy);//replace runs of underscores with one
		$copy = preg_replace('/(^[-_]+)|([-_]+$)/', '', $copy);//get rid of beginning or ending _ or -
		if(preg_match("/[^\w-%]/", $copy)){
			$copy=str_split($copy);
			foreach($copy as $key=>$value) $copy[$key]=(preg_match("/^[\w-%]$/", $value))? $value : rawurlencode($value);//originally had strings in non matching too
				//preivous preg_match("/^[\w\.-~:\/@\?#%=,;]$/", $value) but am stripping some of those characters
			$copy=implode($copy);
		}
		if(($length=strlen($copy))==0){
			$time=time();
			return md5("$type$time$text");
		}
		elseif($length>=300){
			$copy=substr($copy, 0, 300);
			if($perpos=strrpos($copy, '%', -3))$copy=substr($copy, 0, $perpos);
		}
		return $copy;
	}//end internallink
	else return FALSE;
}

function regexspacecleaner ($text, $chartocleanaround="", $spacereplacement=" "){
	//let's turn nonbreaking spaces into a single space
	$regex_pattern="/&nbsp;|&#0160;|&#xa0;/i";
	$text = preg_replace($regex_pattern, " ", $text);
	
	//let's turn breaks into a single space
	$regex_pattern="/<\s*br\s*\/\s*>/";
	$text = preg_replace($regex_pattern, " ", $text);
	
	//let's turn tabs, newlines, and form feeds into a single space
	$regex_pattern="/\t|\r|\n|\f|\v/";
	$text = preg_replace($regex_pattern, " ", $text);
	
	//let's turn all runs of white space into a single space
	$regex_pattern="/\s{2,}/";
	$text = preg_replace($regex_pattern, " ", $text);
	
	//let's get rid of beginning and trailing spaces
	$regex_pattern="/^\s|\s$/";
	$text = preg_replace($regex_pattern, "", $text);
	
	//get rid of spaces around $chartocleanaround
	if($chartocleanaround!==""){
		$regex_pattern="/\s*$chartocleanaround\s*/";
		$text = preg_replace($regex_pattern, "$chartocleanaround", $text);
		//get rid of any runs of the $chartocleanround
		$regex_pattern="/$chartocleanaround+/";
		$text = preg_replace($regex_pattern, "$chartocleanaround", $text);
	}
	
	//replace remaining spaces with $spacereplacement
	if($spacereplacement!==" "){
		$regex_pattern="/\s+/";
		$text = preg_replace($regex_pattern, "$spacereplacement", $text);
	}
	
	return $text;
}

function reservedwords(){//shouldn't be used for tags or usernames
	return array(
		"admin"
		,"administrater"
		,"administrator"
		,"administr8r"
		,"all"
		,"collabor8"
		,"collabor8r"
		,"collabor8ed"
		,"collabor8ing"
		,"collabor8ive"
		,"multir8"
		,"multi-r8"
		,"multir8r"
		,"multi-r8r"
		,"class"
		,"classes"
		,"comment"
		,"comments"
		,"exps"
		,"feed"
		,"hide"
		,"hidden"
		,"login"
		,"logout"
		,"n.s."
		,"p<.05"
		,"show"
		,"story"
		,"stories"
		,"tag"
		,"tags"
		,"taking"
		,"terms"
		,"user"
		,"users"
	);
}

function setsessionvariables($userinfo){
	$_SESSION['ID'] = $userinfo['ID'];
	$_SESSION['UserName'] = $userinfo['UserName'];		
	$_SESSION['Email'] = $userinfo['Email'];
	$_SESSION['DisplayName'] = $userinfo['DisplayName'];
	$_SESSION['Affiliation'] = $userinfo['Affiliation'];
	$_SESSION['UserType'] = $userinfo['UserType'];
	$_SESSION['LastLogin'] = $userinfo['LastLogin'];
	$_SESSION['ErrorType'] = $userinfo['ErrorType'];
	$_SESSION['ShowStories'] = $userinfo['ShowStories'];
	$_SESSION['NSFW'] = ($userinfo['NSFW']==='1')? 1 : 0;
	$_SESSION['LanguageID'] = $userinfo['LanguageID'];
    $_SESSION['IsStudent'] = ($userinfo['IsStudent']==='1')? 1 : 0;
    $_SESSION['IsInstructor'] = ($userinfo['IsInstructor']==='1')? 1 : 0;
    $_SESSION['IsFollowing'] = ($userinfo['IsFollowing']==='1')? 1 : 0;
}

function solutionforproblemwithpost($action, $postarr){
	switch($action){
		case "startclass":
			givestartclassform(htmlspecialchars($postarr['req_classname']), $postarr['req_visibility']);
			break;
		case "getclassjoinform":
		case "joinclass":
			$classid=intval($postarr['req_classid']);
			joinclassform(htmlspecialchars($postarr['req_changeobjectid']), $classid, htmlspecialchars($postarr['req_displayname']));
			break;
		case "dropclass":
			$classid=intval($postarr['req_classid']);
			classoptionbutton("Drop", $classid, "classaction.".$classid);
			break;
		default :
			break;
	}
}

function startnewsession($userid, $time){
	$time=time();
	$db=usedb();
	$sessionstart=mysqli_real_escape_string($db, $_SESSION['StartTime']);
	$sessionid=mysqli_real_escape_string($db, session_id());
	if(!$results=mysqli_query($db, $sql="UPDATE sessions SET EndTime = $time WHERE UserID=$userid AND StartTime=$sessionstart AND ID='$sessionid'")){
		mysqli_close($db);
		emailadminerror('mysqlerror', array('location'=>"startnewsessionupdate", 'query'=>$sql));
	}
	elseif(!mysqli_affected_rows($db)===1){
		mysqli_close($db);
		emailadminerror('mysqlerror', array('location'=>"startnewsessionaffected$rows", 'query'=>$sql));
	}
	else mysqli_close($db);
	$_SESSION=array();
	session_destroy();
}

function tableheaders($type){
	switch($type){
		case "sumcsinstructingself":
			return "<table class=\"sumtab\"><thead><tr><th>Class<br>Title (ID)</th><th>Stories<br>Submitted</th><th>Comments<br>Submitted</th><th>Total<br>Submissions</th><th>Actions</th></tr></thead><tbody>";
			break;
		case "sumcsinstructingother":
			return "<table class=\"sumtab\"><thead><tr><th>Class<br>Title (ID)</th><th>Actions</th></tr></thead><tbody>";
			break;
		case "sumcstaking":
			return "<table class=\"sumtab\"><thead><tr><th>Class<br>Title (ID)</th><th>Instructor</th><th>Submitting\nAs</th><th>Stories<br>Submitted<br>/ Score</th><th>Comments<br>Submitted<br>/ Score</th><th>Total<br>Submissions<br>/ Score</th><th>Actions</th></tr></thead><tbody>";
			break;
		case "sumclass":
			return "<table class=\"sumtab\"><thead><tr><th>Student</th><th>Stories<br>Submitted</th><th>Stories<br>Cumulative<br>Score</th><th>Comments<br>Submitted</th><th>Comments<br>Cumulative<br>Score</th><th>Total<br>Submissions<th>Total<br>Cumulative<br>Score</th><th>Options</th></tr></thead><tbody>";
			break;
		default:
			break;
	}
}

function timeago($timeposted){ //used in r8rfuncs.stories.php
	$needsans="s";
	$timedif=time()-$timeposted;
	if($timedif==1)$needsans="";
	if($timedif<60) return "$timedif second$needsans ago";
	$timedif=floor($timedif/60);
	if($timedif==1)$needsans="";
	if($timedif<60)return "$timedif minute$needsans ago";
	$timedif=floor($timedif/60);
	if($timedif==1)$needsans="";
	if($timedif<24)return "$timedif hour$needsans ago";
	$timedif=floor($timedif/24);
	if($timedif==1)$needsans="";
	return "$timedif day$needsans ago";
}

function unionwrapper($columns, $contentstowrap, $order='', $group=''){
	if($order!='' && $group!='') return "SELECT $columns FROM (SELECT * FROM ($contentstowrap) AS T ORDER BY $order DESC) AS T GROUP BY $group ";
	elseif($order!='')return "SELECT $columns FROM ($contentstowrap) AS T ORDER BY $order DESC ";
	elseif($group!='')return "SELECT $columns FROM ($contentstowrap) AS T GROUP BY $group ";
	else return "SELECT $columns FROM ($contentstowrap) AS T ";
}

function verifyusersresponse($challenge,$response,$encryptedpassword){//returns true if the challenge and password make the right response, false otherwise
	return md5($challenge.$encryptedpassword)==$response;
}
?>