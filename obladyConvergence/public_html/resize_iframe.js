var ORIGIN = swHostName;
// var ORIGIN = 'http://'+location.host+':8084/';
//var ORIGIN ='http://belim.adequation.com/';
//var ORIGIN ='http://localhost:8081';

function sendHeight(){
 var isFirefox = typeof InstallTrigger !== 'undefined'; 

 if(document.getElementsByTagName('table')[0]){
	 //document.getElementsByTagName('table')[0].style.height='0%';
	if(window.parent){
		if (isFirefox == true){
			window.parent.postMessage({'newHeight': document.body.scrollHeight+10},ORIGIN);
		}else{
			window.parent.postMessage(document.body.scrollHeight+10,ORIGIN);
		}
	}
 }
 else{
	if (isFirefox == true){
			window.parent.postMessage({'newHeight': document.body.scrollHeight+500},ORIGIN);	
		}else{
			window.parent.postMessage( document.body.scrollHeight+500,ORIGIN);	
		}
 	
 } 	
	
}

if (window.addEventListener) {
  window.addEventListener('load', sendHeight, false);
}
else if (window.attachEvent) {

  window.attachEvent('onload',sendHeight);
}
