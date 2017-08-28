function controller (type, changeobjectid){
	//make sure we have the changeobjectid
	if(changeobjectid==''){
		alert('Please inform us of this error!');
		return false;
	}
	//alert("type is "+type+"and the id is "+changeobjectid);
	//set the variables we need to fill and check
	var parameters='';
	var contentscode='ok';//No problems
	var finishingcode=0;//Set finishing code to 0 means don't do anything besides updating div
											//if need to do something else, change it later
	//get the data, make sure we have what we need, and update the parameters
	if(type=='refresh'){
		location.reload(true);
		return false;
	}
	else if(type=='class'){
		parameters+="&changeobjectid="+changeobjectid;
		var optype=changeobjectid.slice(-4);//drop, join, open, form
		var tempid=changeobjectid.slice(0,-5);
		finishingcode='class';
		if(optype=='form'){
			document.getElementById('pupcontents').innerHTML = 'Processing...';
			showPopUp('absolute');
		}
		else if(optype=='drop'){
			//var whatarr=changeobjectid.replace(/\./g,"/");
			//alert ('changeobjectid is '+changeobjectid+' and the replaced version is '+changeobjectid.replace(/\./g,"/"));
			var whatarr=changeobjectid.split('.');
			var whatlen=whatarr.length;
			var classid=whatarr[1];
			var what=whatarr[whatlen-3];
			var whatid=whatarr[whatlen-2];
			var message='';
			if(what=='student'){
				if(whatid=='all'){
					if(classid=='teaching'){
						message="This will drop all students from your classes and purge the records of their activities for these classes.";
					}
					else{//from a specific class
						message="This will drop all students from this class and purge the records of their activities for this class.";
					}
				}
				else{//dropping one student
					message="This student will be removed from the class and all records of their activities for this class will be purged.";
				}
			}
			else{//dropping class
				if(classid=='teaching'){
					message="This will drop all students from your classes, purge the records of their activities for these classes, and remove the classes you're instructing from your classes page.";
				}
				else if (classid=='taking'){
					message="This will drop you from all of your classes, purge the records of your activities for these classes, and remove the classes you're taking from your classes page.";
				}
				else{//dropping a particular class
					var temp = document.getElementById(changeobjectid).innerHTML;
					if(temp=='Delete')message="This will drop all students from this class, purge the records of their activites for this classes, and remove this class from your classes page.";
					else message="This will drop you from this class and purge the records related to your activites for this class.";
				}
			}
			var answer=confirm(message+"\n\nPlease make any necessary backups before continuing.\n\nWould you still like to continue?"); 
			if (answer){
				document.getElementById('pupcontents').innerHTML = 'Processing...';
				showPopUp('absolute');
			}
			else return false;
		}
		else if(optype=='open'){
			//get all the variables
			var classname = encodeURIComponent(document.getElementById(tempid+'.name').value);
			var password1 = hex_md5(document.getElementById(tempid+'.password1').value);
			var password2 = hex_md5(document.getElementById(tempid+'.password2').value);
			var blankpass = hex_md5('');
			var visibility = document.getElementById(tempid+'.visibility').checked;
			//check that the ones we need have values
			if(classname=='' || password1==blankpass || password2==blankpass)contentscode='missingdata';
			else if (password1!=password2)contentscode='passwordsdontmatch';
			parameters+='&classname='+classname+'&password1='+password1+'&password2='+password2+'&visibility='+visibility;
		}
		else if(optype=='join'){
			//get all the variables
			var classid = document.getElementById(tempid+'.classid').value;
			var encryptedpassword = hex_md5(document.getElementById(tempid+'.password').value);
			var challenge = document.getElementById(tempid+'.challenge').value;
			var displayname = encodeURIComponent(document.getElementById(tempid+'.displayname').value);
			var userresponse = hex_md5(challenge+encryptedpassword);
			var blankpass = hex_md5('');
			//check that the ones we need have values
			if(classid=='' || encryptedpassword==blankpass || challenge=='')contentscode='missingdata';	
			parameters+='&classid='+classid+'&userresponse='+userresponse+'&displayname='+displayname;
		}
	}
	else if(type=='login'){
		parameters+="&changeobjectid="+changeobjectid;
		var optype=changeobjectid.slice(-4);//open, shut, join, rfrm, lfrm, efrm, fpwd, funm, spwd, sunm
		//open login
		//shut logout
		//join register a new user
		//rfrm register form
		//lfrm login form
		//efrm
		//fpwd
		//funm
		//spwd send password
		//sunm send user name
		//alert(optype);
		finishingcode='login';
		if(optype=='shut' || optype=='lfrm' || optype=='rfrm'){
			document.getElementById('pupcontents').innerHTML = 'Processing...';
			showPopUp('absolute');
		}
		else if(optype=='funm' || optype=='fpwd'){
		}
		else if(optype=='sunm'){
			var email = encodeURIComponent(document.getElementById('sendusername.email').value);
			if(email=='')contentscode='missingdata';	
			parameters+='&email='+email;
		}
		else if(optype=='spwd'){
			var username = document.getElementById('sendpassword.username').value;
			var email = encodeURIComponent(document.getElementById('sendpassword.email').value);
			if(username=='' || email=='')contentscode='missingdata';	
			parameters+='&username='+username+'&email='+email;
		}
		else if(optype=='open'){
			var username = document.getElementById('login.username').value;
			var encryptedpassword = hex_md5(document.getElementById('login.password').value);
			var challenge = document.getElementById('login.challenge').value;
			var userresponse = hex_md5(challenge+encryptedpassword);
			var blankpass = hex_md5('');
			//check that the ones we need have values
			if(username=='' || encryptedpassword==blankpass || challenge=='')contentscode='missingdata';	
			parameters+='&username='+username+'&userresponse='+userresponse;
		}
		else if(optype=='join'){
			var username=document.getElementById('register.username').value;
			var encryptedpassword = hex_md5(document.getElementById('register.password1').value);
			var encryptedpassword2 = hex_md5(document.getElementById('register.password2').value);
			var blankpass = hex_md5('');
			var email=encodeURIComponent(document.getElementById('register.email').value);
			var displayname=encodeURIComponent(document.getElementById('register.displayname').value);
			var affiliation=encodeURIComponent(document.getElementById('register.affiliation').value);
			var anon=document.getElementById('register.anon').checked;
			var nsfw=document.getElementById('register.nsfw').checked;
			var agree=document.getElementById('register.agree').checked;
			if(username=='' || encryptedpassword==blankpass || encryptedpassword2==blankpass || email=='' || displayname==''){
				document.getElementById('register.error').innerHTML = 'Please fill in all required fields.<br><br>';
				document.getElementById('register.error').className="caution";
				return false;
			}
			else if(encryptedpassword!=encryptedpassword2){
				document.getElementById('register.error').innerHTML = 'The passwords you provided do not match.<br><br>';
				document.getElementById('register.error').className="caution";
				return false;
			}
			else if(!agree){
				document.getElementById('register.error').innerHTML = 'If you have do not agree with the terms of service, you cannot register.<br><br>';
				document.getElementById('register.error').className="caution";
				return false;
			}
			parameters+='&username='+username+'&pword='+encryptedpassword+'&email='+email+'&displayname='+displayname+'&affiliation='+affiliation+'&anon='+anon+'&nsfw='+nsfw+'&agree='+agree;
		}
		else if(optype=='efrm'){
			var etype=document.getElementById('efrm.type').value;
			if(etype=='newuser'){
				var username=document.getElementById('efrm.username').value;
				var code=encodeURIComponent(document.getElementById('efrm.code').value);
				if(code==''){
					document.getElementById('register.error').innerHTML = 'Please fill in all required fields.<br><br>';
					document.getElementById('register.error').className="caution";
					return false;
				}
				parameters+='&etype='+etype+'&username='+username+'&code='+code;
			}
			else{
				document.getElementById('efrm.error').innerHTML = 'We\'re sorry, but we\'re not sure what to do with the information you provided.  Please, try again and contact us if the problem continues.<br><br>';
				document.getElementById('efrm.error').className="caution";
				return false;
			}
		}
		else{
			document.getElementById('pupcontents').innerHTML = 'Unknown Operation.';
			showPopUp('absolute');
			return false;
		}
	}
	else if(type=='commentform'){
		parameters = parameters+"&changeobjectid="+changeobjectid;
	}
	else if(type=='commentlogin'){
		alert('Please log in to submit comments!');
		return false;
	}
	else if(type=='commentsshow'){
		parameters+="&changeobjectid="+changeobjectid;
		finishingcode="commentsshow";
	}
	else if(type=='commentsubmit'){
		finishingcode="commentsubmit";
		var comment = encodeURIComponent(document.getElementById(changeobjectid+'.comment').value);
		var anon = document.getElementById(changeobjectid+'.anon').checked;
		var forclass = document.getElementById(changeobjectid+'.forclass').value;
		var access = document.getElementById(changeobjectid+'.isaccessible').checked;
		var nsfw = document.getElementById(changeobjectid+'.nsfw').checked;
		if(comment=='' || forclass=='')contentscode='missingdata';
		parameters = parameters+"&changeobjectid="+changeobjectid+"&comment="+comment+"&anon="+anon+"&forclass="+forclass+"&access="+access+"&nsfw="+nsfw;
	}
	else if(type=='authanon'){
		parameters = parameters+"&changeobjectid="+changeobjectid;
		finishingcode="authanon";
	}
	else if(type=='follow'){
		parameters = parameters+"&changeobjectid="+changeobjectid;
		finishingcode="follow";
	}
	else if(type=='grade'){
		var score = document.getElementById(changeobjectid+'.score').value;
		parameters = parameters+"&score="+score+"&changeobjectid="+changeobjectid;
		//if((score<1 && !(score===0)) || score>100)contentscode='badgrade';
		//if(score<0 || score>100) alert("badscore of "+score);
		//else alert("good score of "+score);
		finishingcode="grade";
	}
	else if(type=='hide'){
		var optype=changeobjectid.slice(-5);
		var tempid=changeobjectid.slice(0,-6);
		if(optype=="showt"){
			changeCSS(tempid+'.contents', "display", "inline");
			changeCSS(tempid+'.alt', "display", "none");
			return false;
		}
		else if(optype=="hidet"){
			changeCSS(tempid+'.contents', "display", "none");
			changeCSS(tempid+'.alt', "display", "inline");
			return false;
		}
		else{//we're permanently hiding or showing the stories
			parameters = parameters+"&changeobjectid="+changeobjectid;
			finishingcode="hide";
		}
	}
	else if(type=='tag'){
		//figure out what we're doing
		var optype=changeobjectid.slice(-4);//hide, show, add1, adds, rem1, grab
		var tempid=changeobjectid.slice(0,-5);
		if(optype=="hide"){
			changeCSS(tempid+'.tagbutton', "display", "inline");
			changeCSS(tempid, "display", "none");
			return false;
		}
		else if(optype=="show"){
			changeCSS(tempid+'.tagbutton', "display", "none");
			changeCSS(tempid, "display", "inline");
			return false;
		}
		else{//we're adding, removing or grabbing tags, need to send parameters
			//if we're not adding several (i.e. adds) we can just send in the parameters
			if(optype=="adds"){
				var thetags = encodeURIComponent(document.getElementById(tempid+'.thetags').value);
				if(thetags=="separate, tags, with, commas" || ""){
					alert ("Please include some tags to submit.");
					return false;
				}
				parameters+="&thetags="+thetags
			}
			parameters = parameters+"&changeobjectid="+changeobjectid;
			finishingcode="tag";
		}
	}
	else if(type=='edit'){
		//figure out what we're doing
		var optype=changeobjectid.slice(-4);//show, hide, save (some to be added later)
		var tempid=changeobjectid.slice(0,-5);
		if(optype=="show"){
			changeCSS(tempid+'.button', "display", "none");
			changeCSS(tempid, "display", "inline");
		}
		return false;
	}
	else if(type=='story'){
		var optype=changeobjectid.slice(-4);//show, hide, save (some to be added later)
		var tempid=changeobjectid.slice(0,-5);
		finishingcode="story";
		if(optype=="save"){
			document.getElementById('storysubmitform.error').className="nodisplay";
			var title = encodeURIComponent(document.getElementById(tempid+'.title').value);
			var summary = encodeURIComponent(document.getElementById(tempid+'.summary').value);
			var nsfw = document.getElementById(tempid+'.nsfw').checked;
			var anon = document.getElementById(tempid+'.anon').checked;
			var forclass = document.getElementById(tempid+'.forclass').value;
			var access = document.getElementById(tempid+'.isaccessible').checked;
			var url = encodeURIComponent(document.getElementById(tempid+'.url').value);
			var rule = document.getElementById(tempid+'.rule').value;
			if(title =='' || summary=='' || url=='' || rule==''){
				document.getElementById(tempid+'.error').innerHTML="Please fill in all required fields!";
				document.getElementById(tempid+'.error').className="caution";
				return false;
			}
			else if(decodeURIComponent(title).match(/^[\W\d_]+$/)){
				document.getElementById(tempid+'.error').innerHTML="You seriously think that \""+decodeURIComponent(title)+"\" is a good title? Try putting in a little more effort! If you really don't want much hassle, just copy and paste the title the site uses.";
				document.getElementById(tempid+'.error').className="caution";
				return false;
			}
			else if(decodeURIComponent(summary).match(/^[\W\d_]+$/)){
				document.getElementById(tempid+'.error').innerHTML="You seriously think that \""+decodeURIComponent(summary)+"\" is a good summary? Try putting in a little more effort! If you really don't want much hassle, just copy and paste a representative chunk of text from the site.";
				document.getElementById(tempid+'.error').className="caution";
				return false;
			}
			document.getElementById(tempid).className="nodisplay";
			document.getElementById(tempid+'.error').innerHTML="Processing...";
			document.getElementById(tempid+'.error').className="inline";
			parameters += "&changeobjectid="+changeobjectid+"&title="+title+"&summary="+summary+"&nsfw="+nsfw+"&anon="+anon+"&forclass="+forclass+"&access="+access+"&url="+url+"&rule="+rule;
		}
		else if(optype=="form"){
			var url = encodeURIComponent(document.getElementById('urlform.url').value);
			var originaltext = "Submit a link...";
			if(url==originaltext || url=='')contentscode='missingdata';
			else showPopUp('absolute');
			parameters+="&changeobjectid="+changeobjectid+'&url='+url;
		}
		else return false;
	}
	else if(type=='vote'){
		var optype=changeobjectid.slice(-3);//del,sig,nsg,lof
		if(optype=='log'){
			alert('Please log in to vote!');
			return false;
		}
		else finishingcode="vote";
		parameters+="&changeobjectid="+changeobjectid;
	}
	else if(type=='options'){
		var optype=changeobjectid.slice(-3);//unm unf dnm pwf pwd eml eok emf aff sho sfw
		parameters+="&changeobjectid="+changeobjectid+"&optype="+optype;
		finishingcode='options';
		var challenge = document.getElementById('options.challenge').value;
		if(optype=='unf'){
			document.getElementById('options.usernamechange.form').className="optbox";
			document.getElementById('options.usernameshow.span').className="nodisplay";
			return false;
		}
		else if(optype=='pwf'){
			document.getElementById('options.passwordchange.form').className="optbox";
			document.getElementById('options.passwordshow.span').className="nodisplay";
			return false;
		}
		else if(optype=='emf'){
			document.getElementById('options.emailchange.div').className="optbox";
			document.getElementById('options.emailchange.form').className="";
			document.getElementById('options.emailconfirm.form').className="nodisplay";
			document.getElementById('options.emailshow.div').className="nodisplay";
			return false;
		}
		else if(optype=='unm'){
			var username=document.getElementById('options.username').value;
			var pword = hex_md5(document.getElementById('options.usernamepassword').value);
			var userresponse = hex_md5(challenge+pword);
			//check that the ones we need have values
			if(username=='' || pword==hex_md5('') || challenge==''){
				document.getElementById('options.error').innerHTML = 'Please enter a new user name and your existing password.<br><br>';
				document.getElementById('options.error').className="caution";
				return false;
			}
			parameters+='&username='+username+'&userresponse='+userresponse;
		}
		else if(optype=='pwd'){
			var oldpword=hex_md5(document.getElementById('options.password').value);
			var pword1=hex_md5(document.getElementById('options.password1').value);
			var pword2=hex_md5(document.getElementById('options.password2').value);
			var userresponse = hex_md5(challenge+oldpword);
			var blankpwd=hex_md5('');
			//check that the ones we need have values
			if(oldpword==blankpwd || pword1==blankpwd || pword2==blankpwd || challenge==''){
				document.getElementById('options.error').innerHTML = 'Please enter your existing password and two copies of your new password.<br><br>';
				document.getElementById('options.error').className="caution";
				return false;
			}
			else if(pword1!=pword2){
				document.getElementById('options.error').innerHTML = 'The new passwords you provided do not match.<br><br>';
				document.getElementById('options.error').className="caution";
				return false;
			}
			else if(pword1==oldpword){
				document.getElementById('options.error').innerHTML = 'The new passwords match the old password.<br><br>';
				document.getElementById('options.error').className="caution";
				return false;
			}
			parameters+='&newpword='+pword1+'&userresponse='+userresponse;
		}
		else if(optype=='dnm'){
			var dname=encodeURIComponent(document.getElementById('options.displayname').value);
			if(dname==''){
				document.getElementById('options.error').innerHTML = 'Please enter a display name.<br><br>';
				document.getElementById('options.error').className="caution";
				return false;
			}
			parameters+='&dname='+dname;
		}
		else if(optype=='eml' || optype=='eok'){
			var email=encodeURIComponent(document.getElementById('options.email').value);
			var pword = hex_md5(document.getElementById('options.emailpassword').value);
			var userresponse = hex_md5(challenge+pword);
			//check that the ones we need have values
			if(email=='' || pword==hex_md5('') || challenge==''){
				document.getElementById('options.error').innerHTML = 'Please enter a new email address and your current password.<br><br>';
				document.getElementById('options.error').className="caution";
				return false;
			}
			parameters+='&email='+email+'&userresponse='+userresponse+'&pword='+pword+'&challenge='+challenge;
			if(optype=='eok'){
				var confirmation = hex_md5(document.getElementById('options.emailconfirm').value);
				var uncodedconfirmation = document.getElementById('options.emailconfirm').value;
				//check that the ones we need have values
				if(confirmation=='' || pword==hex_md5('')){
				document.getElementById('options.error').innerHTML = 'Please enter the confirmation code to validate the new email address.<br><br>';
				document.getElementById('options.error').className="caution";
				return false;
			}
			parameters+='&confirmation='+confirmation+'&uncodedconfirmation='+uncodedconfirmation;
			}
		}
		else if(optype=='aff'){
			var affiliation=encodeURIComponent(document.getElementById('options.affiliation').value);
			if(affiliation==''){
				document.getElementById('options.error').innerHTML = 'Please enter your new affiliation.<br><br>';
				document.getElementById('options.error').className="caution";
				return false;
			}
			parameters+='&affiliation='+affiliation;
		}
		else if(optype=='sho'){
			parameters+='&anon='+document.getElementById('options.anon').checked;
		}
		else if(optype=='sfw'){
			parameters+='&nsfw='+document.getElementById('options.nsfw').checked;
		}
	}
	else if(type=='email'){
		var optype=changeobjectid.slice(-4); //rfrm cfrm rsnd csnd
		parameters+="&changeobjectid="+changeobjectid+"&optype="+optype;
		finishingcode='email';
		if(optype=='rfrm'){
			document.getElementById('pupcontents').innerHTML = 'Processing...';
			showPopUp('fixed');
		}
		else if(optype=='rsnd'){
			var reason=whichRadioSelected("email.rfrm.reason", "Please select a reason for your report.", 'email.rfrm.error');
			if (!reason) return false;
			var details=encodeURIComponent(document.getElementById('email.rfrm.details').value);
			if(reason=='other' && details==''){
				document.getElementById('email.rfrm.error').innerHTML = 'Please enter some details so we can better process this report.<br><br>';
				document.getElementById('email.rfrm.error').className="caution";
				return false;
			}
			parameters+='&reason='+reason+'&details='+details;
		}
		else if(optype=='cfrm'){
			var theform='<span id="email.cfrm.error" class="nodisplay"></span>'+
				'<form id="email.cfrm.form" onSubmit="return controller(\'email\', \'email.csnd\');">';
			if(!document.getElementById('urlform')) theform+='<input id="email.cfrm.name" type="text" size="30" maxlength="60"/> Your Name*<br><br>'+
				'<input id="email.cfrm.email" type="text" size="30" maxlength="60"/> Your E-mail Address*<br><br>';
			theform+='Tell us what\'s on your mind?<br><textarea id="email.cfrm.details" cols="60" rows="4"/></textarea><br>'+
			'<span onclick="controller(\'email\', \'email.csnd\');" class="clickable_span participate ">SUBMIT</span><br>'+
			'<input class="nodisplay" id="email.cfrm.submit" type="submit" value="Report" class="submit"/></form>';
			document.getElementById('pupcontents').innerHTML=theform;
			showPopUp('fixed');
			return false;
		}
		else if(optype=='csnd'){
			if(!document.getElementById('urlform')){
				var name=encodeURIComponent(document.getElementById('email.cfrm.name').value);
				var email=encodeURIComponent(document.getElementById('email.cfrm.email').value);
				if(name=='' || email==''){
					document.getElementById('email.cfrm.error').innerHTML = 'Please include your name and email address.';
					document.getElementById('email.cfrm.error').className="caution";
					return false;
				}
				parameters+='&name='+name+'&email='+email;
			}
			var details=encodeURIComponent(document.getElementById('email.cfrm.details').value);
			if(details==''){
				document.getElementById('email.cfrm.error').innerHTML = 'Please tell us what\'s on your mind before submitting.';
				document.getElementById('email.cfrm.error').className="caution";
				return false;
			}
			parameters+='&details='+details;
		}
	}
	else if(type=='search'){
		var optype=changeobjectid.slice(-4); //form find
		parameters+="&changeobjectid="+changeobjectid+"&optype="+optype;
		finishingcode='search';
		if(optype=='form'){
			showPopUp('absolute');
		}
		else if(optype=='find'){
			var searchfor = whichRadioSelected("sfor", "You must search for either stories or comments.<br><br>", 'searchform.error');
			if(!searchfor)return false;
			
			//makesure they've selected either stories or comments to search if they're searching for
			var searchby='contents';
			if(searchfor=='stories')searchby = whichRadioSelected("sby", "You must choose to search by contents or tags.<br><br>", 'searchform.error');
			if(!searchby)return false;
			
			var tagsall = encodeURIComponent(document.getElementById("tall").value);
			var tagsany = encodeURIComponent(document.getElementById("tany").value);
			var tagsnone = encodeURIComponent(document.getElementById("tnone").value);
			if(tagsall=="" && tagsany==""){
				document.getElementById('searchform.error').innerHTML = 'Please select something to find.<br><br>';
				document.getElementById('searchform.error').className="caution";
				return false;
			}
			var contentsmine = document.getElementById('cmine').checked;
			var contentshidden = document.getElementById('chide').checked;
			var tagsmine = document.getElementById('tmine').checked;
			
			parameters='?sfor='+searchfor+'&sby='+searchby;
			if(tagsall!='')parameters+='&tall='+tagsall;
			if(tagsany!='')parameters+='&tany='+tagsany;
			if(tagsnone!='')parameters+='&tnone='+tagsnone;
			if(contentsmine)parameters+='&cmine='+contentsmine;
			if(tagsmine)parameters+='&tmine='+tagsmine;
			if(contentshidden)parameters='hidden/'+parameters;
			else window.location="http://collabor8r.com/search/"+parameters;
			return false;
		}
	}
	else if(type=='submitforclass'){
		var optype=changeobjectid.slice(-4); //form save
		var tempid=changeobjectid.slice(0,-5);
		parameters+="&changeobjectid="+changeobjectid;
		finishingcode='submitforclass';
		if(optype=='save'){
			var selectedbefore = document.getElementById(tempid+'.options.currentchoice').value;
			var selectednow = document.getElementById(tempid+'.options').value;
			if(selectedbefore==selectednow){
				document.getElementById(tempid+'.error').innerHTML = "You need to pick a different option if you want to submit changes.<br><br>";
				return false;
			}
			parameters+="&submitfor="+selectednow+"&submittedfor=";
		}
		if(optype=='form' || optype=='save'){
			document.getElementById('pupcontents').innerHTML = 'Processing...';
			showPopUp('fixed');
		}
		else{
			document.getElementById(tempid+'.error').innerHTML = "We didn't understand your request, please let us know if the problem continues.<br><br>";
			setTimeout("location.reload(true)", 200);
			return false;
		}
	}
	else if(type=='changeclassmembername'){
		var optype=changeobjectid.slice(-4); //form save
		var tempid=changeobjectid.slice(0,-5);
		if(optype=='form'){
			document.getElementById(tempid+'.info').className="nodisplay";
			document.getElementById(tempid+'.form').className="inline";
			return false;
		}
		else if(optype=='save'){
			var oldname = document.getElementById(tempid+'.oldname').value;
			var newname = document.getElementById(tempid+'.newname').value;
			if(newname==oldname || newname=='' || newname=='There was a problem, try again.') return false;
			document.getElementById(tempid+'.info.name').innerHTML="Processing...";
			document.getElementById(tempid+'.info.but').className="nodisplay";
			document.getElementById(tempid+'.form').className="nodisplay";
			document.getElementById(tempid+'.info').className="inline";
			parameters+="&changeobjectid="+changeobjectid+"&newname="+newname;
			finishingcode='changeclassmembername';
		}
		else return false;
	}
	else{//don't have a known type
		document.getElementById('pupcontents').innerHTML = 'Unknown Operation.';
		showPopUp('fixed');
		return false;
	}

		//alert users of missing fields missmatched passwords etc.
	switch(contentscode){
		case 'missingdata':
			alert('Please fill in all required fields!');
			return false;
		case 'passwordsdontmatch':
			 alert('The passwords you provided do not match!');
			return false;
		case 'badgrade':
			alert('Grades must be between 0 and 100, inclusive.');
			return false;
		//case 'debug':
		//	alert(parameters);
		//	return false;
		default:
			break;
	}
	
	//alert users of potentially dangerous actions they might not want to do
	switch (type){
		case 'deleteclass':
			if(!confirm("Are you sure you'd like to delete this class?	This action cannot be undone.")) return false;
			break;
		case 'disbandclass':
			if(!confirm('Are you sure you\'d like to drop everyone from this class?	This action cannot be undone.')) return false;
			break;
		case 'dropclass':
			if(!confirm('Are you sure you\'d like to drop this class?	This action cannot be undone.')) return false;
			break;
	}
	
	//If were changing the popup, adjust the CSS appropriately
	if(changeobjectid=="pupcontents"){
		switch(type){
			case 'submitstory':
			case 'getsubmitstoryform':
				document.getElementById('pupcontents').innerHTML = 'Loading...';
				showPopUp('absolute');
			default:
				break;
		}
	}
	parameters='action='+type+parameters;
	//alert("the parameters are " +parameters);
	dynamicXMLPost('/php/controller.php', parameters, changeobjectid, finishingcode);
	return false;
}

function changeCSS (id, property, setting){
	document.getElementById(id).style[property]=setting;
}

function dynamicXMLPost(url, parameters, objectId, whenfinishedcode){
	//Set the request to false so it's only true if we've been successful
	XMLrequest = false;

	//for native XMLHttpRequest support
	changeobjectid = objectId;

	if(window.XMLHttpRequest){																														 
		try{XMLrequest=new XMLHttpRequest();}
		catch (e){XMLrequest=false;}

	}else if (window.ActiveXObject){//for IE
		try{XMLrequest=new ActiveXObject('Msxml2.XMLHTTP');}
		catch (e){
			try{XMLrequest=new ActiveXObject('Microsoft.XMLHTTP');}
			catch (e){XMLrequest=false;}
		}
	}
	if (XMLrequest){
		//alert('Had a successful request whenfinishedcode is '+whenfinishedcode);
		XMLrequest.onreadystatechange = function(){
			if(XMLrequest.readyState==4 && XMLrequest.status==200){
				//don't do anything unless the request is "ok" see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
				if(whenfinishedcode=='login'){
					var optype=changeobjectid.slice(-4);//open, shut, join, rfrm, lfrm, efrm
					var tempid='pupcontents';
					var responsecode=XMLrequest.responseText.slice(0,7);//success or failure
					if(optype=='shut'){
						if(responsecode=='success'){
							document.getElementById('pupcontents').innerHTML = 'Logging out.';
							setTimeout("location.reload(true)", 200);
						}
						else document.getElementById('pupcontents').innerHTML = XMLrequest.responseText.slice(7);
					}
					else if(optype=='lfrm' || optype=='rfrm'){
						if(responsecode=='success'){
							document.getElementById('pupcontents').innerHTML = XMLrequest.responseText.slice(7);
						}
						else document.getElementById('pupcontents').innerHTML = XMLrequest.responseText.slice(7);
					}
					else if(optype=='open'){
						if(responsecode=='success' || responsecode=='doerror'){
							document.getElementById('pupcontents').innerHTML = XMLrequest.responseText.slice(7);
							if(responsecode=='success')setTimeout("location.reload(true)", 200);
						}
						else{
							document.getElementById('login.error').innerHTML = XMLrequest.responseText.slice(7);
							document.getElementById('login.error').className="caution";
						}
					}
					else if(optype=='join'){
						if(responsecode=='success'){
							document.getElementById('pupcontents').innerHTML = XMLrequest.responseText.slice(7);
						}
						else{
							document.getElementById('register.error').innerHTML = XMLrequest.responseText.slice(7);
							document.getElementById('register.error').className="caution";
						}
					}
					else if(optype=='efrm'){
						var etype=document.getElementById('efrm.type').value;
						if(etype=='newuser'){
							if(responsecode=='success'){
								document.getElementById('pupcontents').innerHTML = XMLrequest.responseText.slice(7);
								setTimeout("location.reload(true)", 200);
							}
							else{
								document.getElementById('efrm.error').innerHTML = XMLrequest.responseText.slice(7);
								document.getElementById('efrm.error').className="caution";
							}
							return false;
						}
						else{
							document.getElementById('efrm.error').innerHTML = 'We\'re sorry, but we\'re not sure what to do with the information you provided.  Please, try again and contact us if the problem continues.<br><br>';
							document.getElementById('efrm.error').className="caution";
							return false;
						}
					}
					else if(optype=='funm' || optype=='fpwd'){
						if(responsecode=='success'){
							document.getElementById('pupcontents').innerHTML = XMLrequest.responseText.slice(7);
						}
						else{
							document.getElementById('login.error').innerHTML = XMLrequest.responseText.slice(7);
						}
						return false;
					}
					else if(optype=='sunm' || optype=='spwd'){
						if(responsecode=='success'){
							document.getElementById('pupcontents').innerHTML = XMLrequest.responseText.slice(7);
						}
						else{
							var tempid = (optype=='sunm')? 'sendusername' : 'sendpassword';
							document.getElementById(tempid+'.error').innerHTML = XMLrequest.responseText.slice(7);
						}
						return false;
					}
					else{
						document.getElementById('pupcontents').innerHTML = 'Unknown Login Operation.';
						showPopUp('absolute');
						return false;
					}
				}
				else if(whenfinishedcode=='class'){
					var optype=changeobjectid.slice(-4);//drop, join, open, form
					var tempid=changeobjectid.slice(0,-5);
					var responsecode=XMLrequest.responseText.slice(0,7);//success or failure
					if(optype=='form'){
						if(responsecode=='success')document.getElementById('pupcontents').innerHTML = XMLrequest.responseText.slice(7);
						else document.getElementById('pupcontents').innerHTML = 'There was a problem processing your request. Please try again and alert us if the problem continues';
					}
					else if(optype=='drop'){
						if(responsecode=='success'){
							document.getElementById('pupcontents').innerHTML = XMLrequest.responseText.slice(7);
							setTimeout("location.reload(true)", 200);
						}
						else document.getElementById('pupcontents').innerHTML = 'There was a problem processing your request. Please try again and alert us if the problem continues';
					}
					else if(optype=='open'){
						if(responsecode=='success'){
							document.getElementById('pupcontents').innerHTML = 'Congratulations, the class was successfully started.';
							setTimeout("location.reload(true)", 200);
						}
						else{
							document.getElementById('classform.error').innerHTML = 'There was a problem processing your request. Please try again and alert us if the problem continues';
							document.getElementById('classform.error').className="caution";
						}
					}
					else if(optype=='join'){
						if(responsecode=='success'){
							document.getElementById('pupcontents').innerHTML = 'Congratulations, you successfully joined the class.';
							setTimeout("location.reload(true)", 200);
						}
						else{
							document.getElementById('classform.error').innerHTML = XMLrequest.responseText.slice(7);
							document.getElementById('classform.error').className="caution";
						}
					}
					return false;
				}
				else if(whenfinishedcode=='commentsshow'){
					document.getElementById(changeobjectid+".contents").innerHTML = XMLrequest.responseText;
				}
				else if(whenfinishedcode=='commentsubmit'){
					//checkifsubmission was success or failure
					var returncode=XMLrequest.responseText.slice(0,7);
					var theresponse=XMLrequest.responseText.slice(7);
					//alert(returncode);
					//alert(theresponse);
					if(returncode=="success"){
						//put the comment at the end of the containing div
						document.getElementById(changeobjectid+".contents").innerHTML+=theresponse;
						//change the reply div to show success and add another comment
						var replyObjectId=changeobjectid+".reply";
						var message=(replyObjectId.split(".").length==4)? "Submit A Comment" : "Reply";
						message="<span onclick=\"controller('commentform', '"+replyObjectId+"')\" class=\"clickable_span participate author\">"+message+"</span>";
						message+=" <span id=\""+changeobjectid+".hidet\" class=\"clickable_span participate\" onclick=\"controller('hide', '"+changeobjectid+".hidet')\">Hide</span>";
						document.getElementById(replyObjectId).innerHTML=message;
					}
					else if(returncode=="failure"){
						document.getElementById(changeobjectid+'.commentform.error').innerHTML = '<br>'+XMLrequest.responseText.slice(7);
						document.getElementById(changeobjectid+'.commentform.error').className="caution";
					}
				}
				else if(whenfinishedcode=='authanon'){
					//figure out what type of operation it is
					var optype=changeobjectid.slice(-4);
					changeobjectid=changeobjectid.slice(0,-5);
					var oldHTML=document.getElementById(changeobjectid).innerHTML;
					var parloc=oldHTML.indexOf("(");
					if(XMLrequest.responseText.slice(0,7)=="success"){
						if(optype=="hide"){
							document.getElementById(changeobjectid).innerHTML="Submitted By You Anonymously "+oldHTML.slice(parloc);
							document.getElementById(changeobjectid+".anon").innerHTML="Reclaim";
							document.getElementById(changeobjectid+".anon").onclick = function()
							{
								controller('authanon', changeobjectid+'.show');
							}
						}
						else if(optype=="show"){
							document.getElementById(changeobjectid).innerHTML="Submitted By You "+oldHTML.slice(parloc);
							document.getElementById(changeobjectid+".anon").innerHTML="Anonymize";
							document.getElementById(changeobjectid+".anon").onclick = function()
							{
								controller('authanon', changeobjectid+'.hide');
							}
						}
					}
					else{
						document.getElementById(changeobjectid+".opt").innerHTML="Operation Failed, Try Again.";
					}
				}
				else if(whenfinishedcode=='follow'){
					//figure out what type of operation it is
					var optype=changeobjectid.slice(-5);
					changeobjectid=changeobjectid.slice(0,-6);
					if(XMLrequest.responseText.slice(0,7)=="success"){
						if(optype=="cease"){
							document.getElementById(changeobjectid+".follow").innerHTML="Follow";
							document.getElementById(changeobjectid+".follow").onclick = function()
							{
								controller('follow', changeobjectid+'.start');
							}
						}
						else if(optype=="start"){
							document.getElementById(changeobjectid+".follow").innerHTML="Stop Following";
							document.getElementById(changeobjectid+".follow").onclick = function()
							{
								controller('follow', changeobjectid+'.cease');
							}
						}
					}
					else{
						document.getElementById(changeobjectid+".opt").innerHTML="Operation Failed, Try Again.";
					}
				}
				else if(whenfinishedcode=='grade'){
					if(XMLrequest.responseText.slice(0,7)=="success"){
						document.getElementById(changeobjectid+".button").innerHTML="Change";
						document.getElementById(changeobjectid).className="grade";
					}
					else{
						document.getElementById(changeobjectid+".button").innerHTML="Try Again.";
					}
				}
				else if(whenfinishedcode=='vote'){
					if(XMLrequest.responseText.slice(0,7)=="success"){
						var optype=changeobjectid.slice(-3);
						var tempid=changeobjectid.slice(0,-4);
						var btype=tempid.slice(-3);
						if(optype=='del'){
							document.getElementById(tempid).className="votefor"+btype;
							document.getElementById(tempid).onclick = function(){controller('vote', tempid+'.'+btype);}
						}
						else{
							document.getElementById(tempid).className="voted"+btype;
							document.getElementById(tempid).onclick = function(){controller('vote', tempid+'.del');}
							var otype=(btype=='sig')? 'nsg' : 'sig';
							diffid=tempid.slice(0,-3)+otype;
							document.getElementById(diffid).className="votefor"+otype;
							document.getElementById(diffid).onclick = function(){controller('vote', diffid+'.'+otype);}
						}
					}
					else alert('We\'re sorry, but there was a problem processing your request, please try again.');
					return false;
				}
				else if(whenfinishedcode=='hide'){
					if(XMLrequest.responseText.slice(0,7)=="success"){
						var optype=changeobjectid.slice(-5);
						var tempid=changeobjectid.slice(0,-6);
						if(optype=="showp"){
							document.getElementById(tempid+'.hidep').innerHTML = "Remove-From-Feed";
							document.getElementById(tempid+'.hidep').onclick = function()
							{
								controller('hide', tempid+'.hidep');
							}
						}
						else if(optype=="hidep"){
							changeCSS(tempid+'.contents', "display", "none");
							changeCSS(tempid+'.alt', "display", "inline");
							document.getElementById(tempid+'.hidep').innerHTML = "Add-Back-To-Feed";
							document.getElementById(tempid+'.hidep').onclick = function()
							{
								controller('hide', tempid+'.showp');
							}
						}
					}
					return false;
				}
				else if(whenfinishedcode=='tag'){
					var optype=changeobjectid.slice(-4);//add1, adds, rem1, grab
					var tempid=changeobjectid.slice(0,-5);
					var responsecode=XMLrequest.responseText.slice(0,7);//success or failure
					if(optype=="add1" && responsecode=="success"){
						changeCSS(tempid, "color", "#01747A");
						document.getElementById(tempid).onclick = function()
							{
								controller('tag', tempid+'.rem1');
							}
					}
					else if(optype=="rem1" && responsecode=="success"){
						changeCSS(tempid, "color", "black");
						document.getElementById(tempid).onclick = function()
							{
								controller('tag', tempid+'.add1');
							}
					}
					else if(optype=="grab" && responsecode=="success"){
						document.getElementById(tempid).innerHTML = XMLrequest.responseText.slice(7);//put tags in the tag div
						changeCSS(tempid, "display", "inline");//show the tagdiv
						changeCSS(tempid+'.tagbutton', "display", "none");//hide the button to show tags
						document.getElementById(tempid+'.tagbutton').onclick = function() //change the buttons action so we don't grab tags again
							{
								controller('tag', tempid+'.show');
							}
					}
					else if(optype=="adds"){
						//we may have additional information for both success and failure codes so check
						var extrachunk=XMLrequest.responseText.indexOf("<!#-jscollabor8iveextra-#!>");
						var response;
						if(extrachunk>=0){
							response=(responsecode=="success")? XMLrequest.responseText.slice(7, extrachunk) : document.getElementById(tempid).innerHTML;
							extrachunk=XMLrequest.responseText.slice(extrachunk+27);
							var brloc = response.indexOf("<br>");
							var formloc = response.indexOf("<form id=");
							if(brloc>0 && formloc>brloc){
								document.getElementById(tempid).innerHTML = response.slice(0, brloc+4)+extrachunk+response.slice(formloc);
							}
							else document.getElementById(tempid).innerHTML = response;
							//var openchunk = document.getElementById(tempid).innerHTML.slice(0, tloc+4);
							//tloc = document.getElementById(tempid).innerHTML.indexOf("<form id=");
							//var closechunk = document.getElementById(tempid).innerHTML.slice(tloc);
							//document.getElementById(tempid).innerHTML = openchunk+extrachunk+closechunk;
						}
						else if(responsecode=="success") document.getElementById(tempid).innerHTML =  XMLrequest.responseText.slice(7);
					}
					return false;
				}
				else if(whenfinishedcode=='story'){
					var responsecode=XMLrequest.responseText.slice(0,7);//success or failure
					var optype=changeobjectid.slice(-4);
					var tempid=changeobjectid.slice(0,-5);
					if(optype=="save"){
						if(responsecode=="success"){
							document.getElementById('pupcontents').innerHTML = XMLrequest.responseText.slice(7);
						}
						else{
							document.getElementById(tempid+'.error').innerHTML = XMLrequest.responseText.slice(7);
							document.getElementById(tempid+'.error').className="caution";
							document.getElementById(tempid).className="inline";
						}
					}
					if(optype=="form"){
						document.getElementById('pupcontents').innerHTML = XMLrequest.responseText.slice(7);
						if(responsecode=="tempban"){
							document.getElementById('pupx').onclick = function()
							{
								controller('refresh', 'pupcontents');
							}
						}
					}
				}
				else if(whenfinishedcode=='options'){
					var optype=changeobjectid.slice(-3);//unm unf dnm pwf pwd eml eok emf aff sho sfw
					var responsecode=XMLrequest.responseText.slice(0,7);//success or failure
					if(responsecode=='failure'){
						document.getElementById('options.error').innerHTML = XMLrequest.responseText.slice(7);
						document.getElementById('options.error').className="caution";
						return false;
					}
					if(optype=='unm'){
						document.getElementById('options.usernamechange.form').className="nodisplay";
						document.getElementById('options.usernameshow.span').className="";
					}
					else if(optype=='pwd'){
						document.getElementById('options.passwordchange.form').className="nodisplay";
						document.getElementById('options.passwordshow.span').className="";
					}
					else if(optype=='eml'){
						document.getElementById('options.emailconfirm.form').className="";
						document.getElementById('options.emailchange.form').className="nodisplay";
					}
					else if(optype=='eok'){
						document.getElementById('options.emailchange.form').className="";
						document.getElementById('options.emailchange.div').className="nodisplay";
						document.getElementById('options.emailconfirm.form').className="nodisplay";
						document.getElementById('options.emailshow.div').className="";
					}
					document.getElementById('options.error').innerHTML = XMLrequest.responseText.slice(7);
					document.getElementById('options.error').className="";
				}
				else if(whenfinishedcode=='email'){
					var optype=changeobjectid.slice(-4);//unm unf dnm pwf pwd eml eok emf aff sho sfw
					var responsecode=XMLrequest.responseText.slice(0,7);//success or failure
					if(optype=='rfrm'){
						document.getElementById('pupcontents').innerHTML = XMLrequest.responseText.slice(7);
						return false;
					}
					if(optype=='rsnd'){
						if(responsecode=='success') document.getElementById('pupcontents').innerHTML = XMLrequest.responseText.slice(7);
						else{
							document.getElementById('email.rfrm.error').innerHTML = XMLrequest.responseText.slice(7);
							document.getElementById('email.rfrm.error').className="caution";
						}
						return false;
					}
					if(optype=='csnd'){
						if(responsecode=='success') document.getElementById('pupcontents').innerHTML = XMLrequest.responseText.slice(7);
						else{
							document.getElementById('email.cfrm.error').innerHTML = XMLrequest.responseText.slice(7);
							document.getElementById('email.cfrm.error').className="caution";
						}
						return false;
					}
				}
				else if(whenfinishedcode=='search'){
					var optype=changeobjectid.slice(-4);//unm unf dnm pwf pwd eml eok emf aff sho sfw
					var responsecode=XMLrequest.responseText.slice(0,7);//success or failure
					if(optype=='form'){
						if(responsecode=='success')document.getElementById('pupcontents').innerHTML = XMLrequest.responseText.slice(7);
						else document.getElementById('pupcontents').innerHTML = 'There was a problem processing your request, please try again.<br><br>';
					}
					return false;
				}
				else if(whenfinishedcode=='submitforclass'){
					var optype=changeobjectid.slice(-4);//form save
					if(optype=='form')document.getElementById('pupcontents').innerHTML = XMLrequest.responseText;
					else{
						if(XMLrequest.responseText=='success'){
							document.getElementById('pupcontents').innerHTML = 'Congratulations, your submission has been successfully changed.';
							setTimeout("location.reload(true)", 200);
						}
						else{
							document.getElementById('pupcontents').innerHTML = 'There was some problem processing your request. Please verify whether the desired changes have occured and try again if they have not.';
							setTimeout("location.reload(true)", 1000);
						}
					}
					return false;
				}
				else if(whenfinishedcode=='changeclassmembername'){
					var optype=changeobjectid.slice(-4);//form save
					var tempid=changeobjectid.slice(0,-5);
					var newname = document.getElementById(tempid+'.newname').value;
					var oldname = document.getElementById(tempid+'.oldname').value;
					var responsecode=XMLrequest.responseText.slice(0,7);//success or failure
					if(optype=='save' && responsecode=='success'){
						document.getElementById(tempid+'.info.name').innerHTML=newname;
						document.getElementById(tempid+'.oldname').value=newname;
						document.getElementById(tempid+'.info.but').className="clickable_span participate inline";
					}
					else{
						document.getElementById(tempid+'.info.name').innerHTML=oldname;
						document.getElementById(tempid+'.newname').value='There was a problem, try again.';
						document.getElementById(tempid+'.info').className="nodisplay";
						document.getElementById(tempid+'.form').className="inline";
					}
					return false;
				}
				else{
					document.getElementById(changeobjectid).innerHTML = XMLrequest.responseText;
					return false;
				}//ifs																																																	
			}//end if(XMLrequest.readyState==4 && XMLrequest.status==200)		
		}
		XMLrequest.open("POST", url, true);
		//Send the header information //needed for POSTING our data
		XMLrequest.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		//XMLrequest.setRequestHeader('Content-length', parameters.length);										 
		//XMLrequest.setRequestHeader('Connection', 'close');																	 
		XMLrequest.send(parameters);																													
		//alert('Made it to sending parameters');																						 
	}
}

function mySearch(){
	//This function just verifies that we have the contents that we need to do the search
	//It returns true so we can GET the appropriate search page
	
	//makesure they've selected either stories or comments to search
	var searchfor = whichRadioSelected("sfor", "You must search for either stories or comments.");
	if(!searchfor)return false;
	
	//makesure they've selected either stories or comments to search
	var searchby = whichRadioSelected("sby", "You must choose to search by contents or tags.");
	if(!searchby)return false;
	
	if(searchby=="contents"){
		var fulltextsearch = document.getElementById("sfull").value;
		if(fulltextsearch==""){
			alert("Please enter some criteria to search.");
			return false;
		}
	}else if(searchby=="tags"){
		var tagsall = document.getElementById("tall").value;
		var tagsany = document.getElementById("tany").value;
		var tagsnone = document.getElementById("tnone").value;
		if(tagsall=="" && tagsany=="" && tagsnone==""){
			alert("You must include some tags.");
			return false;
		}
	}
	else return true;
}

function popDown (){
	changeCSS("pmask", "display", "none");
	changeCSS("pup", "display", "none");
	document.getElementById('pupcontents').innerHTML = 'Loading...';
}	

function showPopUp (position){
	changeCSS("pmask", "display", "inline");
	if(position=='absolute')changeCSS('pup', 'position', 'absolute');
	else if(position=='fixed')changeCSS('pup', 'position', 'fixed');
	changeCSS("pup", "display", "inline");
}


function searchToggle(togglewhat){
	switch(togglewhat){
		case "contents":
			document.getElementById("sbytags").className="nodisplay";
			document.getElementById("sbycontents").className="visible";
			break;
		case "tags":
			document.getElementById("sbycontents").className="nodisplay";
			document.getElementById("sbytags").className="visible";
			break;
		case "stories":
			document.getElementById("sbylang").className="visible";
			break;
		case "comments":
			document.getElementById("sbylang").className="nodisplay";
			break;
		default:
			break;
	}//end switch	
	return true;
}

function whichRadioSelected(radioname, message, errorid){
	var radioarray = document.getElementsByName(radioname);
	var radlen=radioarray.length;
	var radsel="";
	if(radlen>0){
		for (var i = 0; i <radlen; i++) {
			if (radioarray[i].checked) {
				radsel = radioarray[i].value;
				break;
			}
		}
		if(radsel==""){
			if(errorid=='alert')alert(message);
			else{
				document.getElementById(errorid).innerHTML = message;
				document.getElementById(errorid).className="caution";
			}
			return false;
		}else return radsel;
	}else{
		if(errorid=='alert')alert("We were unable to process your request.  Please contact us if the problem continues.");
		else{
			document.getElementById(errorid).innerHTML = "We were unable to process your request.  Please contact us if the problem continues.<br><br>";
			document.getElementById(errorid).className="caution";
		}
		return false;
	}
}

function urlfromstring (urlstring){
	//strip spaces
	urlstring=urlstring.replace(/ /g,"");
	var urlregex = /(?:(https?):\/\/)?(?:([^:@\s]+)(?::([^:@\s]+)?)?@)?([a-z\d](?:[a-z\d-]*[a-z\d])?(?:\.[a-z\d](?:[a-z\d-]*[a-z\d])?)+)(?::(\d*))?(?:\/((?:[\w-\._~:@!\$\^'\(\)\*\+,;=%]+\/?)*)?)?(?:\?([\w-\._~:@!\$\^'\(\)\*\+,;=%\?\/]*)?)?(?:#([\w-\._~:@!\$\^'\(\)\*\+,;=%\?\/]*)?)?/i;
	//if match array is returned
	//          0=whole  1=protocol         2=username    3=password      4=host                                                              5=port      6=path                                          7=query                                    8=fragment
	//else false is returned
	var urlcoms = urlstring.match(urlregex);
	if(urlcoms){//the pattern matched so we have a hostname
		//if we don't have a protocol add http://
		if(typeof urlcoms[1]==="undefined") return "http://"+urlstring;
		else return urlstring;
	}
	else return false;
}

function clearonclick(id, defaulttext){
	if(id.value==defaulttext)id.value='';
}

function restoreonblur(id, defaulttext){
	if(id.value=='')id.value=defaulttext;
}