var fn = function(e) {
    if (!e) {
        var e = window.event;
    }

    var keycode = e.keyCode;

    if (e.which) {
        keycode = e.which;
    }

    var src = e.srcElement;

    if (e.target) {
        src = e.target;
    }

    // 116 = F5
    if (116 == keycode) {
        if (e.preventDefault) {
            e.preventDefault();
            e.stopPropagation();
        } else if (e.keyCode) {
            e.keyCode = 0;
            e.returnValue = false;
            e.cancelBubble = true;
        }
        //window.location = 'http://pm2046/test.php';
        return false;
    }
};

document.onkeydown = fn;

function removeAllRequiredFields(){
      var forms =document.getElementsByTagName('form');
      for(i=0;i<forms.length;i++){
        forms[i].onsubmit=function(){return true}
          }
      
      var inputFields = document.getElementsByTagName('input');
      var selectFields = document.getElementsByTagName('select');
      var textareaFields = document.getElementsByTagName('textarea');
      
      var fields = new Array(inputFields ,selectFields ,textareaFields );
      for(j=0;j<fields.length;j++){
        
        for(i=0;i<fields[j].length;i++){
          
          var  nameField = fields[j][i].getAttribute('name');
          if(nameField != null)
          {
        	  if(nameField.search(/form\[/)!=-1){
        		  var n =nameField.split('[');
        		  var name = n[1].split(']');
        		  nameField =name[0];
            
        	  }
        	  removeRequiredById(nameField);
        	  // supprime le requis
        	  fields[j][i].removeAttribute('pm:required');
        	  // supprime le non modifiable
        	  fields[j][i].removeAttribute('pm:readonly');
        	  fields[j][i].removeAttribute('readOnly');
        	  fields[j][i].className = 'module_app_input___gray';
          }
        }
        
      }
      var requiredStar=document.getElementsByTagName('font');
      for(i=0;i<requiredStar.length;i++){
        
        if(requiredStar[i].innerHTML.match(/\* /)){
          requiredStar[i].innerHTML='';
        }
      }
      document.getElementById('DynaformRequiredFields').value='[]';
}

function removeAllAccents(s)
{
	var __r = 
	{
		'À':'A','Á':'A','Â':'A','Ã':'A','Ä':'A','Å':'A','Æ':'E',

		'È':'E','É':'E','Ê':'E','Ë':'E',

		'Ì':'I','Í':'I','Î':'I',

		'Ò':'O','Ó':'O','Ô':'O','Ö':'O',

		'Ù':'U','Ú':'U','Û':'U','Ü':'U',

		'Ñ':'N'
	};
    return s.replace(/[ÀÁÂÃÄÅÆÈÉÊËÌÍÎÏÕÒÓÔÖÙÚÛÜÑ]/gi, function(m)
    {
        var ret = __r[m.toUpperCase()];
        if (m === m.toLowerCase())
            ret = ret.toLowerCase();
        return ret;
    });

}

function ValidateFields(variable, message)
{  
  var classIN = 'module_app_input___gray';
  var value_form = new input(getField(variable));
  
  value_form.onclick=function()
  {
      value_form.className=classIN;
  }
  if (getField(variable).value == '')
  {
    value_form.failed();
    value_form.title=message;
    alert(message);
    var AprOk = false; 
  }
  else
  {
     value_form.passed();
     value_form.title="";
    var AprOk = true;
  }
  
  return AprOk;
}

/*Valida campos dropdwon cuando es requerido*/
function ValidateDropdown(variable, message)
{  
  var oObjeto = new input(getField(variable));
  
  if (getField(variable).value == 0)
  {    
    oObjeto.failed();
    var rpta = false;
    alert(message);
  }
  else  
  {    
    oObjeto.passed();
    var rpta = true;
  }    
  return rpta;      
}
