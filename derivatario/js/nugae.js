//Basically, we've got two types of function:
//searchX, which searches linguistic data to complete
//lookupX, which searches linguistic data to propose an annotation

//Toggle with jQuery

jQuery(document).ready(function() {
  jQuery("#main_table").hide();
  jQuery(".wordform").hide();
  //toggle the componenet with toggler
  jQuery(".press").click(function()
  {
    $("#main_table").toggle("slow");
  });
});

function wordForm(derivative) {
$(derivative).toggle("slow");
}
//Get the selected values and construct an wfp.
function generateWfp() {

var affix = document.getElementById("affisso");
var allomorph = document.getElementById("allomorfo");
var mt = document.getElementById("mt");
var ms = document.getElementById("ms");

//get the selected option
var selectedAffix = affix.options[affix.selectedIndex].text;
var selectedAllomorph = allomorph.options[allomorph.selectedIndex].text;
var selectedMt = mt.options[mt.selectedIndex].text;
var selectedMs = ms.options[ms.selectedIndex].text;

var wfp = selectedAffix+":"+selectedAllomorph+":mt"+selectedMt+":ms"+selectedMs;

$('#wfplist').append('<LI>'+wfp+'</LI>');

//document.getElementById("wfp").innerHTML=wfp;

				}


function searchDerivative() {
var derived = document.getElementById("derivative").value;
var miniq = document.getElementById("miniquerier");
var whichid = miniq.parentNode.id;
//Miniquery.php expects $_POST["derived"]
$.post("miniquery.php", {derived: ""+derived+"", whichid: ""+whichid+""}, function(data){
				if(data.length >0) {
					$('#searchDerivative').html(data);
				}

			});

}

function lookupWc(id) {
//Miniquery.php expects $_POST["derived"]
var button_id = document.getElementById(id);
$.post("guesswc.php", {derived_id: ""+id+""}, function(data){
				if(data.length >0) {
					$(button_id).html(data);
				}

			});

}


function closeWindow() {
var close = true;
//Miniquery.php expects $_POST["close"]
$.post("miniquery.php", {close: ""+close+""}, function(data){
				if(data.length >0) {
					$('#searchDerivative').html(data);
				}

			});

}


//edit: $affix_edit, the affix to edit. type: affix_type, suffix or prefix
function lookupDerived(derivedString,baseString,pseudoM) {
		if(derivedString.length == 0) {
			// Hide the suggestion box.
		$('#suggestionsBox').hide();
}
else  
 {
			$.post("analyze.php", {derivedString: ""+derivedString+"", idbaseString: ""+baseString.id+"", pseudoM: ""+pseudoM.id+""}, function(data){
				if(data.length >0) {
					$('#suggestionsBox').show();
					$('#autoSuggestionsList').html(data);
				}

			});
		}
	} // lookupDerived
	

//We take only the id for wfp1String, the whole value for derivedString and baseString (note the trailing .id)...

function lookupBase(baseString,derivedString,wfp1String,pseudoM) {
		if(baseString.length == 0) {
			// Hide the suggestion box.
			$('#suggestionsBox').hide();
		} else {
			$.post("analyze.php", {baseString: ""+baseString+"", derivedString: ""+derivedString+"", idwfp1String: ""+wfp1String.id+"", pseudoM: ""+pseudoM.id+""}, function(data){
				if(data.length >0) {
					$('#suggestionsBox').show();
					$('#autoSuggestionsList').html(data);
				}
				
			});
		}
	} // lookup

//Same for the following function: we just add the wfp2String's ID.

function lookupWfp1(baseString,derivedString,wfp1String,wfp2String) {
		if(baseString.length == 0) {
			// Hide the suggestion box.
			$('#suggestionsBox').hide();
		} else {
			$.post("analyze.php", {baseString: ""+baseString+"", derivedString: ""+derivedString+"", wfp1String: ""+wfp1String+"", idwfp2String: ""+wfp2String.id+""}, function(data){
				if(data.length >0) {
					$('#suggestionsBox').show();
					$('#autoSuggestionsList').html(data);
				}
				
			});
		}
	} // lookup
