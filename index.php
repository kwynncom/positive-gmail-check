<?php
    require_once('/opt/kwynn/kwutils.php');
    sslOnly(1);
?>
<!DOCTYPE html>
<html lang='en'>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
<meta name='viewport' content='width=device-width, initial-scale=1.0' />

<title>quick email count</title>

<link rel='stylesheet' type='text/css' href='qemail.css'>

<script src='/opt/kwynn/js/utils.js'></script>

<script> // 19:08

window.addEventListener('DOMContentLoaded', () => {  post(); });

function setWin(status) {
	
	const evs = ['DOMContentLoaded', 'focus', 'hashchange'];
	let func = myNull;
	
    if (status === 'up') func = post;
		
	for (let i=0; i < evs.length; i++) window.addEventListener(evs[i], () => {  func(); });
//		window.addEventListener('focus', () => {  post(); });
//		window.addEventListener('hashchange', () => {  post(); });

		//	window.onload = window.onfocus = window.onhashchange = post;
		
	// }
    // else  window.onload = window.onfocus = window.onhashchange = myNull;
}

function myNull() {}

var SERVER_FILE_G_CONST = 'server.php';

function post(pparam) {
    
    const revoke = pparam === 'revoke' ? '?revoke=Y' : '';
    
    byid('msgtxt').innerHTML = '?';
    
    var req = new XMLHttpRequest();
    req.open('GET', SERVER_FILE_G_CONST + (revoke));
    if (revoke) setWin('down');
    req.send();
    
    req.onload    = function() {  
        process(this.responseText, revoke); 
    }
}


function process(rt, revoke) {
	
	inht('dates', '');
    
    if (!rt) return;
    
    if (rt.length > 500) {
        doerr(rt);
        return;
    }
    else doerr(false);
    
    try { var res = JSON.parse(rt); } catch(error) { 
		doerr(rt);
		return; 
	}
    
    if (res.url) {
        setWin('down');
        window.location = res.url;
        return;
    }
    
    if (!revoke) setWin('up');
    
    byid('msgtxt').innerHTML = res.msgtxt;
    byid('usage' ).innerHTML = res.glt;
    byid('dates' ).innerHTML = res.dates;
    
    doerr(res.emsg);
 }

function doerr(emsg) {
    if (emsg) {
		setWin('down');
        byid('errmsg').innerHTML = emsg;
        byid('errparent').style.display = 'block';
    }
    else      byid('errparent').style.display = 'none';  
}

    
function usageExpandF() {
    byid('usage'      ).style.visibility = 'visible';
    byid('usageExpand').style.visibility = 'hidden';
    byid('revokeBtn1' ).style.display = 'block';
}

function confirmRevoke() {
    byid('confirmRevokeDiv').style.display = 'block';
    // byid('revokeBtn1').style.visibility = 'hidden';
    byid('revokeBtn1').style.display = 'none';
}

function noRevoke() {
      byid('confirmRevokeDiv').style.display = 'none';  
      byid('revokeBtn1').style.display = 'block';
}

</script>
</head>
    <body>
        
        <div id='errparent' style='display: none'>
            <p id='errmsg'></p>
            <p>Previous result below</p>
        </div>
        		
	<div class='countParent'>
	    <span class='count' id='msgtxt'>?</span>
	    <div class='btn'><button  class='btn' onclick='post()' >redo</button></div>
	</div>

        <p id='dates'></p>
        
        <p class='usageExpand'><span id='usageExpand' onclick='usageExpandF()'>+</span>
            <span id='usage' style='visibility: hidden'></span>
        </p>
        <button id='revokeBtn1' style='display: none; margin-top: 4ex' onclick='confirmRevoke()'>revoke access</button>
        <div style='display: none; ' id='confirmRevokeDiv'>    
            <div><label>Revoke Access - You Sure?</label></div>
            <div style='margin-top: 2ex'>
        <button id='revokeBtnN'  onclick='noRevoke()' style='display: block; margin-bottom: 9ex'>No</button>
                    <button id='revokeBtnY'  onclick='post("revoke")' style='display: block'>Yes</button>
            </div>
            
        </div>


    </body>
</html>
