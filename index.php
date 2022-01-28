<!DOCTYPE html>
<html lang='en'>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
<meta name='viewport' content='width=device-width, initial-scale=1.0' />

<title>quick email count</title>

<link rel='stylesheet' type='text/css' href='qemail.css'>

<script src='/opt/kwynn/js/utils.js'></script>

<script> // 19:49
	
window.addEventListener('DOMContentLoaded', function() { new kwpemck(); });

class kwpemck {
	
	static getServerURL() { return 'server.php'; }
	constructor() {
		kwpemck.pemckpost();
		kwpemck.setWin('up');
	}
	
	static setWin(status) {
	
		const evs = ['DOMContentLoaded', 'focus', 'hashchange'];

		for (let i=0; i < evs.length; i++) 
			if (status === 'up') window.addEventListener   (evs[i], kwpemck.pemckpost); // MDN says not added a second time if already
			else				 window.removeEventListener(evs[i], kwpemck.pemckpost);
	}
	
	static pemckpost(pparam) {

		const revoke = pparam === 'revoke' ? '?revoke=Y' : '';

		inht('msgtxt', '?');
		inht('dates', '');

		var req = new XMLHttpRequest();
		req.open('GET', kwpemck.getServerURL() + (revoke));
		if (revoke) kwpemck.setWin('down');
		req.send();

		req.onload    = function() {  kwpemck.process(this.responseText, revoke); }
	}
	
	static process(rt, revoke) {

		if (!rt) return;

		if (rt.length > 500) {
			kwpemck.doerr(rt);
			return;
		}
		else kwpemck.doerr(false);

		try { var res = JSON.parse(rt); } catch(error) { 
			kwpemck.doerr(rt);
			return; 
		}

		if (res.url) {
			kwpemck.setWin('down');
			window.location = res.url;
			return;
		}

		if (!revoke) kwpemck.setWin('up');

		inht('msgtxt', res.msgtxt);
		inht('usage' , res.glt);
		inht('dates' , res.dates);

		kwpemck.doerr(res.emsg);
	 }

	static doerr(emsg) {
		if (emsg) {
			kwpemck.setWin('down');
			inht('errmsg', emsg);
			byid('errparent').style.display = 'block';
		}
		else      byid('errparent').style.display = 'none';  
	}	

	static usageExpandF() {
		byid('usage'      ).style.visibility = 'visible';
		byid('usageExpand').style.visibility = 'hidden';
		byid('revokeBtn1' ).style.display = 'block';
	}

	static confirmRevoke() {
		byid('confirmRevokeDiv').style.display = 'block';
		byid('revokeBtn1').style.display = 'none';
	}

	static noRevoke() {
		  byid('confirmRevokeDiv').style.display = 'none';  
		  byid('revokeBtn1').style.display = 'block';
	}
}

</script>
</head>
    <body>
        
		<div id='errparent' style='display: none'>
            <p id='errmsg'></p>
            <p>Previous result below</p>
        </div>
		
		<div>
			<div class='countParent'>
				<span class='count' id='msgtxt'>?</span>
				<div class='btn'><button  class='btn' onclick='kwpemck.pemckpost();' >redo</button></div>
			</div>
			<p id='dates'></p>
		</div>
        
		<p class='usageExpand'><span id='usageExpand' onclick='kwpemck.usageExpandF();'>+</span>
            <span id='usage' style='visibility: hidden'></span>
        </p>
        
		<div>
			<button id='revokeBtn1' style='display: none; margin-top: 4ex' onclick='kwpemck.confirmRevoke();'>revoke access</button>

			<div style='display: none; ' id='confirmRevokeDiv'>    
				<div><label>Revoke Access - You Sure?</label></div>
				<div style='margin-top: 2ex'>
					<button id='revokeBtnN'  onclick='noRevoke()' style='display: block; margin-bottom: 9ex'>No</button>
					<button id='revokeBtnY'  onclick='kwpemck.pemckpost("revoke");' style='display: block'>Yes</button>
				</div>
			</div>
		</div>
    </body>
</html>
