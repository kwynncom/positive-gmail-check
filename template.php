<!DOCTYPE html>
<html lang='en'>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
<meta name='viewport' content='width=device-width, initial-scale=1.0' />

<title>quick email count</title>

<link rel='stylesheet' type='text/css' href='qemail.css'>

<script src='/opt/kwynn/js/utils.js'></script>

<script> // 2022/01/28 02:14

var KWPGO = false;

window.addEventListener('DOMContentLoaded', function() { KWPGO = new kwpemck(); });

class kwpemck {
	
	config() { 
		this.serverURL = 'server.php'; 
		this.plimms = 5000;
	}
	
	constructor() {
		this.config();
		this.defineELs();
		this.pemckpost();
		this.setWin('up');
	}
	
	defineELs() {
		const self = this;
		this.el10 = function() { self.pemckpost(); };
	}
	
	setWin(status) {
	
		const evs = ['DOMContentLoaded', 'focus', 'hashchange'];

		for (let i=0; i < evs.length; i++) 
			if (status === 'up') window.addEventListener   (evs[i], this.el10); // MDN says not added a second time if already
			else				 window.removeEventListener(evs[i], this.el10);
	}
	
	overLim() {
		
		const now = time();
		
		if (!this.lpo) {
			this.lpo = now;
			return;
		}
		
		const okin = this.plimms - (now - this.lpo);
		
		if (okin > 0) return true;
		
		this.setrbs(false);
		const self = this;
		setTimeout(function() { self.setrbs(true); }, this.plimms);
			
		this.lpo = now;
		return false;
	}
	
	setrbs(sin) {
		const b = byid('redoBtn');
		b.disabled = !sin;		
	}
	
	getrbs() { return !(byid('redoBtn').disabled); }
	
	pemckpost(pparam) {

		const revoke = pparam === 'revoke' ? '?revoke=Y' : '';

		if (!revoke) {
			if (!this.getrbs()) return;
			if (this.overLim()) return;
			inht('msgtxt', '?');
			inht('dates', '');
		}
		if (revoke) this.setWin('down');
		var req = new XMLHttpRequest();
		req.open('GET', this.serverURL + (revoke));
		req.send();

		const self = this;
		req.onload    = function() {  self.process(this.responseText, revoke); }
	}
	
	process(rt, revoke) {

		if (!rt) return;

		if (rt.length > 500) {
			this.doerr(rt);
			return;
		}
		else this.doerr(false);

		try { var res = JSON.parse(rt); } catch(error) { 
			this.doerr(rt);
			return; 
		}

		if (res.url) {
			this.setWin('down');
			window.location = res.url;
			return;
		}

		if (!revoke) this.setWin('up');

		inht('msgtxt', res.msgtxt);
		inht('usage' , res.glt);
		inht('dates' , res.dates);

		this.doerr(res.emsg);
	 }

	 doerr(emsg) {
		if (emsg) {
			this.setWin('down');
			inht('errmsg', emsg);
			byid('errparent').style.display = 'block';
		}
		else      byid('errparent').style.display = 'none';  
	}	

	 usageExpandF() {
		byid('usage'      ).style.visibility = 'visible';
		byid('usageExpand').style.visibility = 'hidden';
		byid('revokeBtn1' ).style.display = 'block';
	}

	 confirmRevoke() {
		this.setrbs(false);
		byid('confirmRevokeDiv').style.display = 'block';
		byid('revokeBtn1').style.display = 'none';
	}

	 noRevoke() {
		this.setrbs(true);
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
			<div class='btn'><button  class='btn' onclick='KWPGO.pemckpost();' id='redoBtn'>redo</button></div>
		</div>
		<p id='dates'></p>
	</div>

	<p class='usageExpand'><span id='usageExpand' onclick='KWPGO.usageExpandF();'>+</span>
		<span id='usage' style='visibility: hidden'></span>
	</p>

	<div>
		<button id='revokeBtn1' style='display: none; margin-top: 4ex' onclick='KWPGO.confirmRevoke();'>revoke access</button>

		<div style='display: none; ' id='confirmRevokeDiv'>    
			<div><label>Revoke Access - You Sure?</label></div>
			<div style='margin-top: 2ex'>
				<button id='revokeBtnN'  onclick='KWPGO.noRevoke();' style='display: block; margin-bottom: 9ex'>No</button>
				<button id='revokeBtnY'  onclick='KWPGO.pemckpost("revoke");' style='display: block'>Yes</button>
			</div>
		</div>
	</div>
</body>
</html>
