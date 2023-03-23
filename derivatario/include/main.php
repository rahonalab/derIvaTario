<?php
require('connect.php');
/*Classe azioni: descrive le principali azioni del programma. */


class Azioni {

protected $join_condition;

############################# CONSTRUCTOR ##########################################

public function __construct() {
#Join with itforms
	$this->join_condition = "join itforms on (itforms.nLem = derivatario.derived_id)";
	$this->select_what = 'derivatario.derived_id, derivatario.derived, base, wfp1,wfp2,wfp3,wfp4,wfp5,wfp6,fqTot,word,gramCat,phoneSyll';
	$this->alpha= "ORDER BY `derived` ASC";
	}



	public function writeSearchDerivative() {
echo"<FORM ENCTYPE = 'multipart/form-data'  id='searchDerivative' >";
echo"<p>Derivative?";
echo"<p align=center>";

echo"<INPUT TYPE='TEXT'  id='derivative' />";
echo"<BUTTON TYPE='button' onclick='searchDerivative()'>Show me</BUTTON></p>";
echo"</FORM>";
}



//###### feccia_array: ARRAY ####### 
//Most important array - it contains query results.
//Structure [0] = $row
//Array ( [0] => Array ( [derived_id] => 6946 [derived] => AMICIZIA [base] => AMICO:root [wfp1] => IZIA:izia:mt1:ms1 [wfp2] => [wfp3] => [wfp4] => [wfp5] => [wfp6] => [fqTot] => 1 [word] => amicizie [gramCat] => S IN E@ [phoneSyll] => a.mi.ciZ.Zje [wfp1_sem] => IZIA:regular [wfp2_sem] => [wfp3_sem] => [wfp4_sem] => [wfp5_sem] => [wfp6_sem] => ) 

############################# PRIVATE FUNCTIONS ##########################################

//###### exec_sql: function #######
//This function executes the mysql query and return the variable $risultato
	private function exec_sql($sqlcmd) {
$risultato= mysqli_query($GLOBALS['connection'],$sqlcmd);
		// Check result
// This shows the actual query sent to MySQL, and the error. Useful for debugging.
		if (!$risultato) {
    $message  = 'Invalid query: ' . mysqli_error($GLOBALS['connection']) . "\n";
    $message .= 'Whole query: ' . $sqlcmd;
    die($message);
		} else
		{
return $risultato;
		}
	}

//###### orderFeccia: function #######
//It takes feccia_array as input and produces hier_array as output. You have two main slices: "N", containing the total number of tokens and "der", containing forms.
private function orderFeccia($feccia_array) {
//Set the total number of tokens
$hier_array["N"] = 0;
foreach ($feccia_array as $row) {
$derived_id = $row["derived_id"];
$derived = $row["derived"];
$word = $row["word"];
$wordclass = $row["gramCat"];

//Base
$hier_array["der"]["$derived_id"]["$derived"]["base"] = $row["base"]; 

//Up to six cycles
for ($i=1; $i<=6; $i++) {
$hier_array["der"]["$derived_id"]["$derived"]["wfp$i"] = $row["wfp$i"]; 
//Polysemy
$wfp_sem = "wfp".$i."_sem";
if (array_key_exists("$wfp_sem",$row)) {
$hier_array["der"]["$derived_id"]["$derived"]["$wfp_sem"] = $row["$wfp_sem"]; 
}
}

//Get the total number of tokens
$hier_array["N"] = $hier_array["N"] + $row["fqTot"];

$hier_array["der"]["$derived_id"]["$derived"]["wc"]["$wordclass"]["$word"]["phoneSyll"] = $row["phoneSyll"]; 
$hier_array["der"]["$derived_id"]["$derived"]["wc"]["$wordclass"]["$word"]["fqTot"] = $row["fqTot"]; 


}
return($hier_array);

}
//###### hier_array: ARRAY #######
//feccia_array ordered:
// ["der"] => derived_id -> 
//            		 derived  -> 
//                       		base-> string
//                       		wfp1-> string
//                       		wfpN-> string
//		         		wfp1_sem-> string
//		         		wfpN_sem-> string
//			 		["wc"] -> wordclass -> word ->
//                                      phoneSyll -> string
//                                      fqTot     -> integer
// ["N"] => integer
//E.g.: in order to access fqTot, you call $hier_array[derived_id]["wc"][wordclass][word]["fqTot"]


//###### writeTabella: FUNCTION #######
//It writes query result table. Invoked by public function Cerca()
//Array of Array, NULL/!NULL, NULL/!NULL
private function writeTabella($hier_array,$element) {
$der_array = $hier_array["der"];
//Delete slice if we are searching for stem allomorphy
$wfp = $element["wfp"];
$stem = $element['stemallomorphy'];
$typebase = $element['typebase'];

$value = explode(":",$element["wfp"]);

if ($value[0] == '.*') {
$affix_msg = 'all affixes';
}
else {
$affix_msg = $value[0];
}
$affix = $value[0];
$query = "/$affix/";
if ($value[1] == '.*') {
$allomorph = 'all allomorphs';
}
else {
$allomorph = $value[1];
}


$mt = $value[2];
if ($mt == "mt[1-8]") {
$mt = "any degree";
}

$ms = $value[3];
if ($ms == "ms[123].?") {
$ms = "any level";
}



if ($stem != "any" || $typebase != "any") {
$todelete = array();
foreach ($der_array as $id => $derivative) {
foreach ($derivative as $key1 => $v) {
if (!(preg_match($query,$v["wfp1"]))) {
$todelete[] = $id;
	}
}
}
foreach ($todelete as $delete ) {
unset($der_array[$delete]);
}
}

//Get total tokens
$N = $hier_array["N"];
//Get total types
$types = count($der_array);
//Open switch array
//$polysemy = $element["polysemy"];
$frequency = $element["relfreq"];

echo "<H3>At a glance</H3>";
echo "There are <B>$types</B> lemmas, for a total of <B>$N</B> tokens.";
if ($stem != 'any' || $typebase != 'any') {
	echo " Since you have searched for base features, only derivatives whose <B>affix is next to the base</B> are shown.";
	echo "<BR />";
	}

echo "<BR />";

echo "<H3>More in details</H3>";
//BUTTONS:
echo "<BUTTON id='toggler' class='press'>Show/Hide derivatives</BUTTON>";
//Open div table inside main
echo "<DIV id='main_table'>";
echo "<BUTTON id='hider' class='press'>Hide derivative table</BUTTON>";
echo "<P><B>Tip</B>: Click on derivatives to show/hide wordforms...</P>";
echo "<P align='center'>" . "<TABLE border=\"5\" class='main'>";
echo "<TR>";
echo "<TH>" . "Derivative" . "</TH>";
echo "<TH>" . "Wordstructure" . "</TH>";
echo "<TH>" . "Wordclass"."</TH>";
echo "<TH>" . "Frequency"."</TH>";
echo "</TR>";


foreach ($der_array as $id => $derivative) {




#id


echo "<TR><TD><a href='#' onclick=\"wordForm('#$id'); return false\">".key($derivative)."</a>";
#Derivative cycle
foreach ($derivative as $key1 => $v) {



//Set $wc array
$wc = array();

//Reset single derivative's tokens to zero
$NS = 0;
//Wordform cycle: "wc" label in $v array
	echo "<DIV id='$id' class='wordform'>";
		echo "<BR />";
	foreach ($v["wc"] as $key2 => $wordform) {
//Populate an array with wordclass in order to lighten the following search
#Remember! $key is the array containing wordform
		echo "<TABLE border=\"1\">";
		echo "<TR><TH>wordform</TH>";
		echo "<TH>phonetic transcription</TH>";
		echo "<TH>frequency</TH>";
		echo "<TH>wordclass</TH></TR>";
		foreach ($wordform as $key3 => $values) {
			echo "<TR><TD>$key3</TD>";
		echo "<TD>".$values["phoneSyll"]."</TD>"; 
		echo "<TD>".$values["fqTot"]."</TD>"; 
		echo "<TD>".$key2."</TD>"; 
		#Frequency stuff
		$NS = $NS + $values["fqTot"];
		}
		echo "</TR>";
		echo "</TABLE>";
		echo "<BR />";
//Close the cycle
$wc[] = $key2;
}	
	echo "</DIV>";	
	//Close the derivative cell
	echo "</TD>";
//OPEN WORDCLASS TD
echo "<TD>";
//Collect information on stem allomorphy iff the affix is next to the base
if ((preg_match($query,$v["wfp1"]))) {
$value= explode(":",$v["base"]);
	$gstats["base"][] = $value[1];
	}


$id_ws = $id."_ws";
echo "<DIV id='$id_ws' class='wordstructure'>";
//Let's go back to the Derivative cycle
echo "<TABLE border=\"0\">";
echo "<TR><TH>Base:</TH><TD>" .$v["base"]."</TD></TR>";


//Up to six wfps:
for ($i=1; $i<=6; $i++) {
$wfp_label = "wfp".$i;
//Open table row:
echo "<TR>";
//Check if the wfp is not null
if (isset($v["$wfp_label"]) && $v["$wfp_label"] != '') {
echo "<TH>Word-Formation Process #$i:</TH><TD>" .$v["$wfp_label"];

/*

//Polysemy information?
		if ($polysemy == "yes") {
		$query_polysemy = "/$affix/";
		echo "<BR />";
		$wfp_sem = $wfp."_sem";
			if (preg_match($query_polysemy,$v["$wfp_sem"])) {
			//Split polysemy information
			$value= explode(":",$v["$wfp_sem"]);
			echo "Polysemy: " . $value[1] . "</TD>";
			//Collect stats on polysemy
			$pstats[] = $value[1];
			unset($value);
				}
		}
			else {
			//No polysemy?
			echo "</TD>";
			}
*/



		


//Collect stats. If we are searching for all affixes, there's a lot of stuff to skim
$collect = TRUE;

if ($affix = "all affixes") {
//Stem
if ($stem != "any" && $i > 1) {
$collect = FALSE;
}

if ($element["position"] != "all" && $i != $element["position"]) {
$collect = FALSE;
}
}


	if ($collect == TRUE) {

	if (preg_match($query,$v["$wfp_label"])) {
	$v["$wfp_label"] = preg_replace('/-[EPG]$/', '', $v["$wfp_label"]);
	$value= explode(":",$v["$wfp_label"]);
	$gstats["affix"][] = $value[0];
	$gstats["allomorph"][] = $value[1];
	if (array_key_exists(2,$value)) {
	$gstats["mt"][] = str_replace("mt","",$value[2]);
	}
	if (array_key_exists(3,$value)) {
	$gstats["ms"][] = str_replace("ms","",$value[3]);
	}
	} //end if pregmatch query
	}
} //end if isset
else {
echo "<TR><TH>Word-Formation Process #$i:</TH><TD> -</TD>";
}
//Close table row
	echo "</TR>";
//end for cycle
}

//Close wordstructure table and div
	echo "</TABLE></DIV>";
	echo "</TD>";	
//Open the wordclass cycle
echo "<TD>";
//Open the $wc array
foreach ($wc as $wordclass) {
echo $wordclass." ";
}
echo "</TD>";
//OK, done with wordclass

//Now td frequency
//Let's check the $frequency switch

if ($frequency == "yes") {

//Frequency:
$id_freq = $id."_freq";
echo "<TD class='frequency'><a href='#' onclick=\"wordForm('#$id_freq'); return false\"><b>Derivative Frequency:</b> ".$NS."</a>";

echo "<DIV id='$id_freq'>";
	$array_freq = $this->calculateRelFreq($derivative,$NS);
	echo "<b>Base/inner cycle frequency:</b> ".$array_freq[0]." ($array_freq[2])";
	echo "<br />";
	echo "<b>Relative Frequency </b>".$array_freq[1];

echo "</TD>";

}

else {
echo "<TD class='frequency'>".$NS."</TD>";

}


//Ok, done with all

//End derivative cycle
}
//Close table row
	echo "</TR>";
//End id && if isset derivative cycle
}
echo "</TABLE></P>";
//CLOSE DIV TABLE
echo "</DIV>";



//Statistics
	
echo "<DIV id='stats'>";
echo "<H3>To sum up</H3>";
echo "<DIV id='stats_inner'>";

//Affix
echo "<TABLE border=\"5\" class=\"tabelline\">";
echo "<TR><TH colspan='2'>Affix(es)</TH></TR>";


if (array_key_exists("affix",$gstats)) {
$stats_affix = array_count_values($gstats["affix"]);
$total_affix = count($gstats["affix"]);
arsort($stats_affix);
foreach ($stats_affix as $key => $value) {
echo "<TR>";
echo "<TH>" . "$key:" . "</TH>";
echo "<TD>" . $value . "/$total_affix</TD>";
echo "</TR>";
}
}
else {
echo "<TR><TH>No information available</TH></TR>";
}
echo "</TABLE>";



//Affix allomorphy
echo "<TABLE border=\"5\" class=\"tabelline\">";
echo "<TR><TH colspan='2'>Affix Allomorphy</TH></TR>";


if (array_key_exists("allomorph",$gstats)) {
$stats_allomorph = array_count_values($gstats["allomorph"]);
$total_allomorph = count($gstats["allomorph"]);
arsort($stats_allomorph);
foreach ($stats_allomorph as $key => $value) {
echo "<TR>";
echo "<TH>" . "$key:" . "</TH>";
echo "<TD>" . $value . "/$total_allomorph</TD>";
echo "</TR>";
}
}
else {
echo "<TR><TH>No information available</TH></TR>";
}
echo "</TABLE>";


//Stem allomorphy
echo "<TABLE border=\"5\" class=\"tabelline\">";
echo "<TR><TH colspan='2'>Stem Allomorphy<B>*</B></TH></TR>";



if (array_key_exists("base",$gstats)) {
$stats_base = array_count_values($gstats["base"]);
$total_base = count($gstats["base"]);
arsort($stats_base);
foreach ($stats_base as $key => $value) {
echo "<TR>";
echo "<TH>" . "$key:" . "</TH>";
echo "<TD>" . $value . "/$total_base</TD>";
echo "</TR>";
}


}
else {
echo "<TR><TH>No information available</TH></TR>";
}
echo "<CAPTION align='bottom'><B>*</B>It only applies when $affix is the first word-formation cycle</CAPTION>";

echo "</TABLE>";


echo "<TABLE border=\"5\" class=\"tabelline\">";
echo "<TR><TH colspan='2'>Transparencies</TH></TR>";

if (array_key_exists("mt",$gstats)) {
//Generic

$total_mt = count($gstats["mt"]);
$total_ms = count($gstats["ms"]);
$stats_mt = array_count_values($gstats["mt"]);
$stats_ms = array_count_values($gstats["ms"]);


ksort($stats_mt);
ksort($stats_ms);



echo "<TR><TH colspan='2'>Morphotactic Transparency</TH></TR>";
foreach ($stats_mt as $key => $value) {
echo "<TR>";
echo "<TH>" . "Degree $key:" . "</TH>";
echo "<TD>" . $value . "/$total_mt</TD>";
echo "</TR>";
}


echo "<TR><TH colspan='2'>Morphosemantic Transparency</TH></TR>";
foreach ($stats_ms as $key => $value) {
echo "<TR>";
echo "<TH>" . "Level $key:" . "</TH>";
echo "<TD>" . $value . "/$total_ms</TD>";
echo "</TR>";
}
}
else {
echo "<TH>No information available</TH>";

}
echo "</TABLE>";



/*

//Polysemy
if ($polysemy == "yes" && ($_POST["affisso"])) {
echo "<TABLE border=\"5\" class=\"tabelline\">";
$total_ps= count($pstats);
$stats = array_count_values($pstats);


arsort($stats);

echo "<TR><TH colspan='2'>Polysemy</TH></TR>";

foreach ($stats as $key => $value) {
echo "<TR>";
echo "<TH>Meaning: " . $key . "</TH>";
echo "<TD>" . $value . "/$total_ps</TD>";
echo "</TR>";
}
unset($total);
unset($stats);
echo "</TABLE>";
}
*/
//Close stats div
echo "</DIV>";
echo "</DIV>";

echo "<H3>Your query settings</H3>";
echo "<H3>Base and affixes</H3>";
echo " You have searched for <B>$stem</B> base allomorphy and for <B>$affix_msg</B> (allomorph: <B>$allomorph</B>) with a morphotactic transparency of <B>$mt</B> and a morphosemantic transparency of <B>$ms</B>.";
echo "<BR />";


echo "<H3>Affix ordering</H3>";
echo "Only outest position? ". $element['outest'];
echo "<BR />";
echo "Affix position: ". $element['position'];
echo "<BR />";
echo"Include multiple, parasynthetic processes? ". $element["includep"];
echo "<BR />";
echo"Only parasynthetic processes? ". $element["p"];
echo "<BR />";
echo"Include non-linear, 'graph' processes? ". $element['includeg'];
echo "<BR />";
echo"Only non-linear, 'graph' processes? ". $element['g'];




}


//###### frugaDb: FUNCTION #######
//It gets from the database the entire list of affixes, allomorphs, transparencies features. It is employed to build the main form (function menuPrincipale()). It should be run offline, periodically (once in a day/week).
//String (a SQL select)
private function frugaDb($sqlcmd) {

$risultato = $this->exec_sql($sqlcmd);
while ($row=mysqli_fetch_array($risultato)) {

for ($i=1;$i<6;$i++) {
$wfp = "wfp".$i;
if ($row["$wfp"] != NULL) {

$feature = preg_split('/:/',$row["$wfp"]);

// Catch error
/*if (preg_match('/^ms3$/',$feature[3])) {
$d = $row['derived'];
$this->exec_sql("INSERT into catch_error (derived) value $d");
}
*/

$affix_array[]=$feature[0];
$allomorph_array[]=$feature[1];
if (array_key_exists(2,$feature)) {
$mt_array[]=$feature[2];
/*
//Catch error
		if (preg_match('/ms2b-P/',$feature[2])) {
		$this->exec_sql("INSERT into catch_error (derived) value ".$row['derived_id']);
		}

*/
}

if (array_key_exists(3,$feature)) {
$ms_array[]=$feature[3];
}

}



}

if ($row['gramCat'] != NULL) {
$wc_array[] = $row['gramCat'];

}

//Derivative
if ($row["base"] != NULL) {
	$derivative_array[] = $row['derived_id'];


//Base
$feature= preg_split('/:/',$row['base']);
if (array_key_exists(1,$feature)) {
	$stem_allomorphy_array[]=$feature[1];
}
if (array_key_exists(2,$feature)) {
	$type_base_array[]=$feature[2];
//Store in a dedicated array paradigmatic bases, e.g. aggress in aggression, aggressive...
if (preg_match('/^parad/',$feature[2])) {
$paradigmatic_base_array[]=$feature[0];
}
else {
//Other bases: compounds, acronyms, ...
$other_base_array[]=$feature[0];
}


}
else {
//Lexical bases
	$lexical_base_array[]=$feature[0];

}

}
}

//Cleaning, sorting and uniquing to get the final array

sort($affix_array);
$affix_array=array_unique($affix_array);

$allomorph_array=preg_replace('/[-EPG]/', '', $allomorph_array);
sort($allomorph_array);
$allomorph_array=array_unique($allomorph_array);

sort($stem_allomorphy_array);
$stem_allomorphy_array=array_unique($stem_allomorphy_array);

sort($type_base_array);
$type_base_array=array_unique($type_base_array);

sort($wc_array);
$wc_array=array_unique($wc_array);

//preg_replace('/[^\x09\x0A\x0D\x20-\x7F\xC0-\xFF]/', '', $str);
$mt_array=preg_replace('/mt/', '', $mt_array);
sort($mt_array);
$mt_array=array_unique($mt_array);

$ms_array=preg_replace('/ms/', '', $ms_array);
$ms_array=preg_replace('/[-EPG]/', '', $ms_array);
sort($ms_array);
$ms_array=array_unique($ms_array);
sort($paradigmatic_base_array);
$paradigmatic_base_array=array_unique($paradigmatic_base_array);
sort($other_base_array);
$other_base_array=array_unique($other_base_array);

sort($lexical_base_array);
$lexical_base_array=array_unique($lexical_base_array);

sort($derivative_array);
$derivative_array=array_unique($derivative_array);

$reserved = "Laziness, what a virtue!";

return array($affix_array,$allomorph_array,$mt_array,$ms_array,$stem_allomorphy_array,$type_base_array,$wc_array,$reserved,$paradigmatic_base_array,$other_base_array,$lexical_base_array,$derivative_array);

}

private function wfpMenu($whole_array) {
echo "Affix";
echo "<select NAME='affisso' id='affisso'>";
echo "<option value='' selected>All</option>";
$affix_array=$whole_array[0];

foreach ($affix_array as $value) {
echo  "<option value ='$value'>$value</option>";
}
echo "</select>";
echo "&nbsp";
echo "&nbsp";
echo "Allomorph";
echo "<select NAME='allomorfo' id='allomorfo'>";

echo "<option value='' selected>All</option>";
$allomorph_array=$whole_array[1];

foreach ($allomorph_array as $value) {
echo  "<option value ='$value'>$value</option>";
}

echo "</select>";
echo "&nbsp";
echo "&nbsp";
echo "&nbsp";
echo "Transparencies: ";
echo "&nbsp";
echo "&nbsp";
echo "Morphotactics <select name='mt' id='mt'>";
echo "<option value ='0'>All</option>";
$mt_array=$whole_array[2];
foreach ($mt_array as $value) {
echo  "<option value ='$value'>$value</option>";
}
echo "</select>";
echo "&nbsp";
echo "&nbsp";

echo"Morphosemantics<select name='ms' id='ms'>";
echo  "<option value ='0'>All</option>";
$ms_array=$whole_array[3];

foreach ($ms_array as $value) {
echo  "<option value ='$value'>$value</option>";
}

echo "</select>";
}

private function skimDerivatives($derivatives) {
echo "<TABLE>";
//Filter the derivative to get unique results
$i=0;
	foreach ($derivatives as $value) {
	if ($i == 0) {
		echo "<TR>";
		}
	echo "<TD>".$value."</TD>";
	if ($i == 7) {
		echo "</TR>";
	$i=0;
	}
	$i++;
}

echo "</TABLE>";
}


private function calculateRelFreq($derivative_array,$freq_derived) {

$derivative= key($derivative_array);

	foreach ($derivative_array as $key1 => $v) {
	//Check whether we have more than one cycle...
	$len_wfp2 = strlen($v["wfp2"]);
	//Calculate the base frequency
	$base_feat= preg_split('/:/',$v['base']);
	$freq_base = 0;
	if (array_key_exists(0,$base_feat)) {
			$freq_innest = 0;

		if (!preg_match('/^BASELESS$/',$base_feat[0])) {
		$lexical_base=$base_feat[0];
		$ris_base=	$this->exec_sql("SELECT * from itforms where lemma like '$lexical_base'");
		//Counter to determine whether there are more than one form
		$i=0;
			while ($row_base=mysqli_fetch_array($ris_base)) {
			$freq_base = $freq_base + $row_base["fqTot"];
		}
		if ($len_wfp2 >= 1) {
		//Guess the innest cycle
			for ($i=2; $i<=6; $i++) {	
			$l = $i -1;
			if (($v["wfp$i"] != NULL) && $v["wfp$l"] != NULL ) {
		//Drop trailing characters from wfp annotation
		 	$wfp= preg_replace('/-[EPG]$/', '',$v["wfp$l"]);	
			$inner_wfp = "wfp$l like '".$wfp."' and wfp$i =''";
		//Query to get the inner cycle with base
		$query = "SELECT * from derivatario where $inner_wfp and base like '%$lexical_base%' LIMIT 1";
					}
			}		
		$inner_query = $this->exec_sql($query);
			//return $inner_query;
			if (mysqli_num_rows($inner_query) >= 1) {
			$inner = mysqli_fetch_array($inner_query);
			$inner_derived = $inner["derived"];
			$ris_innest=	$this->exec_sql("SELECT * from itforms where lemma like '$inner_derived'");
			while ($row_innest=mysqli_fetch_array($ris_innest)) {
			$freq_innest = $freq_innest + $row_innest["fqTot"];
			}
			}
			else {
			$inner_derived = "N/A";
			}
		}
		else {
		$freq_innest = 0;	
		$inner_derived= $base_feat[0];
		}
		}
		else {
		$lexical_base = "N/A";
		$inner_derived= $lexical_base;
				}
		}
				

	//Now, let's calculate the relative frequency. Four cases:
	//First: one cycle, baseless
	if ($freq_base == 0 && $len_wfp2 == 0) {
		//Let's add the number of syllables of the base and the stress locus, for derived and base
		//$numsylls_base = "N/A";
		//$stress_base = "N/A";
		$relfreq = "N/A";
		$freq_base = "0";
	}
	//Second: >1 cycle, baseless
	if ($freq_base == 0 && $len_wfp2 >=1) {
	$freq_base = $freq_innest;
	//Let's add the number of syllables of the base and the stress locus, for derived and base
	//$numsylls_base = "N/A";
	//$stress_base = "N/A";
		if ($freq_innest == 0) {
		$relfreq = "N/A";
		$freq_base= "0";
		} 
		else 
		{
		$relfreq = $freq_derived/$freq_innest;
		}
	}
	//Third: >1 cycle, not baseless
	if ($freq_base != 0 && $len_wfp2 >=1) {
		if ($freq_innest == 0) {
		$relfreq = "N/A";
		$freq_innest = "0";
		} 
		else {
		$relfreq = $freq_derived/$freq_innest;
		}
	$freq_base = $freq_innest;
	}
	//Fourth: 1 cycle, not baseless
	if ($freq_base != 0 && $len_wfp2 ==0) {
	$relfreq = $freq_derived/$freq_base;
	}

}

	return array($freq_base,$relfreq,$inner_derived);
}



############################# PUBLIC FUNCTIONS ##########################################
public function menuPrincipale() {
	if (!isset($_SESSION["whole_array"])) {
$sqlcmd = "SELECT $this->select_what from derivatario $this->join_condition where base != ''";
//Get the features from frugaDb	
global $_SESSION;	
$_SESSION["whole_array"]=$this->frugaDb($sqlcmd);
file_put_contents('array.bin', serialize($_SESSION["whole_array"]));
	}
$whole_array = $_SESSION["whole_array"];
$affix_array=$whole_array[0];
//Close button

		//Writing code...
echo"<FORM ENCTYPE = 'multipart/form-data'  METHOD='POST' ACTION='main.php' NAME='scegli'>";
echo"<H3>Base features:</H3>";
echo"Base Allomorphy <select name='stemallomorphy'>";
echo" <option value ='any'>any</option>";
$stem_allomorphy_array=$whole_array[4];
foreach ($stem_allomorphy_array as $value) {
echo  "<option value ='$value'>$value</option>";
}
echo "</select>";
echo "&nbsp";
echo "&nbsp";
echo "Base word type <select name='typebase'>";
echo "<option value ='any'>any</option>";
$type_base_array=$whole_array[5];
foreach ($type_base_array as $value) {
echo  "<option value ='$value'>$value</option>";
}
echo "</select>";
echo"<H3>Affix features:</H3>";
$this->wfpMenu($whole_array);
echo"<H3>Affix ordering:</H3>";
echo "Only outest position? ";
echo"(default: no) <select name='outer'>";
echo"<option value='no'>No</option>";
echo"<option value='yes'>Yes</option>";
echo"</select>";
echo "&nbsp";
echo"Select the affix position: (default: any) <select name='position'>";
$i = 1;
echo"<option value='all'>Any</option>";
for ($i; $i<7; $i++) {
echo "<option value='$i'>$i</option>";
}
echo"</select>";
echo"<H3>Parasynthetic process</H3>";
echo "&nbsp";
echo "&nbsp";
echo"Include parasynthetic processes? (default: yes) <select name='includep'>";
echo"<option value='yes'>Yes</option>";
echo"<option value='no'>No</option>";
echo"</select>";
echo "&nbsp";
echo"Only parasynthetic processes? (default: no) <select name='p'>";
echo"<option value='no'>No</option>";
echo"<option value='yes'>Yes</option>";
echo"</select>";
echo"<H3>Graph processes</H3>";
echo "&nbsp";
echo "&nbsp";
echo"Include non-linear, 'graph' processes? (default: yes) <select name='includeg'>";
echo"<option value='yes'>Yes</option>";
echo"<option value='no'>No</option>";
echo"</select>";
echo "&nbsp";
echo"Only non-linear, 'graph' processes? (default: no) <select name='g'>";
echo"<option value='no'>No</option>";
echo"<option value='yes'>Yes</option>";
echo"</select>";
echo"<br/>";
echo"<H3>Wordclass Output</H3>";
echo"Derivative word class:";
echo"<select name='lc' id='lc'>";
$wc_array=$whole_array[6];
echo "<option value ='0'>All</option>";
foreach ($wc_array as $value) {
echo  "<option value ='$value'>$value</option>";
}
echo "</select>";
echo"<br/>";
echo"<H3>Relative Frequency</H3>";
echo"Include information on relative frequency? (default: no)  <select name='relfreq'>";
echo"<option value='no'>No</option>";
echo"<option value='yes'>Yes</option>";
echo"</select>";
echo "WARNING! It could be slow for very productive affixes.";
echo"<br/>";
echo"<br/>";


/*

echo"<H3>Polysemy</H3>";
echo"include information on polysemy? (default: no) <select name='polysemy'>";
echo"<option value='no'>No</option>";
echo"<option value='yes'>Yes</option>";
echo"</select>";

*/


echo"<CENTER><INPUT TYPE='SUBMIT' VALUE='Query derIvaTario'  /></CENTER>";
echo"</FORM>";




/* Work in Progress 
echo "<H2>or by lexical base...</H2>";
echo"<FORM ENCTYPE = 'multipart/form-data'  METHOD='POST' ACTION='main.php' NAME='scegli1'>";
echo"<p align=center>";

echo "Lexical Base <select name='base'>";
echo "<option value =''>Select me</option>";
foreach ($lexical_base_array as $value) {
echo  "<option value ='$value'>$value</option>";
}
echo "</select>";
echo "&nbsp";
echo "&nbsp";
echo "Paradigmatic Base <select name='paradigm_base'>";
echo "<option value =''>Select me</option>";
foreach ($paradigmatic_base_array as $value) {
echo  "<option value ='$value'>$value</option>";
}
echo "</select>";



echo"<INPUT TYPE='SUBMIT' VALUE='Find!'  /></p>";
echo"</FORM>";


echo"<H2>...or enter a derivative:</H2>";
echo "<DIV id='miniquerier'>";
$this->writeSearchDerivative();


echo '</DIV>';
*/
}



###Please describe me, I'm so important!
public function Cerca($id) {
	//Public function called from within the main.php.  If 'main' or 'leftbox' set the response accordingly.
	//Set $sqlcmd to NULL 
	$sqlcmd = NULL;

//Search for affix, allomorph or transparencies


if ((isset($_POST['affisso']) && $_POST['affisso'] != NULL) || (isset($_POST['mt']) && $_POST['mt'] != NULL)  || (isset($_POST['ms']) && $_POST['ms'] != NULL)) {
//Setto null il derived
$_POST['derived'] = NULL;


if (isset($_POST['affisso']) && $_POST['affisso'] == NULL) {

$_POST['affisso'] = '.*';

}

//Solo per gli affissi
//Affissi con opacità morfotattica e/o morfosemantica
if ((isset($_POST['mt']) && $_POST['mt'] >= '1') || ((isset($_POST['ms']) && $_POST['ms'] >= '1')))   {
$mt = $_POST['mt'];
$ms = $_POST['ms'];

if ($mt >= '1') {
$mt = "mt$mt";
}
else {
$mt = "mt[1-8]";
}

if ($ms >= '1') {
$ms = "ms$ms";
}
else {
$ms = "ms[123].?";
}
}

//Affissi senza opacità
else {
$mt = "mt[1-8]";
$ms = "ms[123].?";
}

//Variabile affisso
if (isset($_POST['affisso']) && $_POST["affisso"] != NULL) {
	$affix="^".$_POST['affisso'].":";
	$_POST['base'] = NULL;
	$_POST['paradigm_base'] = NULL;
}



//Allomorfo
if (isset($_POST["allomorfo"]) && $_POST["allomorfo"] != NULL) {
if (isset($_POST['affisso']) && $_POST["affisso"] != NULL) {
$allomorph=$_POST["allomorfo"].":";
}
else {
$affix=".*";
$allomorph=":".$_POST["allomorfo"].":";
}
}
else {
$allomorph=".*:";
}



//Creo la variabile finale, gestendo il caso speciale della conversione e dell'accorciamento (greetz to Francesca Masini)
if (preg_match('/^CONVERSION/', $_POST["affisso"]) || preg_match('/^SHORTENING/', $_POST["affisso"])) {
if  (isset($_POST["allomorfo"]) && $_POST["allomorfo"] != NULL) {

$allomorph = $_POST["allomorfo"];

}

else

{
	$allomorph = ".*";
}

if (preg_match('/^SHORTENING/', $_POST["affisso"])) {
$affix=$_POST["affisso"];
}

//final wfp for conversion/shortening case
$wfp =$affix.$allomorph;
}
else {
$wfp = "$affix$allomorph$mt:$ms";
}

//Query con processi P e G
//Trail, default P and G. Include P e Include G sono settati entrambi a yes.
$trail = "-[PG]$";


//Query senza processi parasintetici e 'grafici'
//Senza nessun trail:
if ($_POST['includeg'] != "yes" && $_POST['g']!= "yes" && $_POST['includep'] != "yes" && $_POST['p']!= "yes")  {
$trail = NULL;
}
//Solo con graph
if ($_POST['includep'] != "yes" && $_POST['includeg']!= "no")  {
$trail = "-G$";
}
//Solo con parasintesi
if ($_POST['includeg'] != "yes" && $_POST['includep']!= "no")  {
$trail = "-P$";
}


//Solo Parasintesi. p è settato su yes
if ($_POST['p'] != "no") {
$trail = "-P$";
}
//Solo Graph. p è settato su yes
else if ($_POST['g'] != "no") {
$trail = "-G$";
}






//Aggiungo i tipi di base e di lemma

if ((isset($_POST["stemallomorphy"]) && $_POST["stemallomorphy"] != "any") || (isset($_POST["typebase"]) && $_POST["typebase"] != "any"))
{
//Tipo di base
if (isset($_POST["stemallomorphy"]) && $_POST["stemallomorphy"] != "any") {
$stemallomorphy=$_POST["stemallomorphy"];
}
else {
$stemallomorphy=".*";
}

//Tipo di lemma
if (isset($_POST["typebase"]) && $_POST["typebase"] != "any") {
$typebase=":".$_POST["typebase"];
}
if (isset($_POST["typebase"]) && $_POST["typebase"] == "word") {
$typebase="$";
}
if (isset($_POST["typebase"]) && $_POST["typebase"] == "any") {
$typebase=".*";
}
if (isset($_POST["typebase"]) && $_POST["typebase"] == "phrase") {
$typebase=".*_phrase";
}

$fullbase=":$stemallomorphy$typebase";
//Creo un pezzo della query per base

$base = "(base REGEXP BINARY '$fullbase') and";

}
else
{
$base= "(base !='') and";
}

//Derivative Word Class
if ($_POST['lc'] != "0") {
$wordclass = $_POST['lc'];
$wc = "and gramCat like '$wordclass'";
}
else
$wc = "";

/*
	if (isset($_POST['outer']) && $_POST['outer'] != "no") {
$sqlcmd= "SELECT * FROM derivatario $this->join_condition WHERE (base REGEXP BINARY '$fullbase' and wfp1 REGEXP BINARY '$wfp$trail' and wfp2 = '') or (base REGEXP BINARY '$fullbase' and wfp1 REGEXP BINARY '$wfp$trail' and wfp2 $otherwfp and wfp3 ='') or (base REGEXP BINARY '$fullbase' and wfp1 REGEXP BINARY '$wfp$trail' and wfp2 $otherwfp and wfp3 $otherwfp and wfp4 ='') ";
echo '<H3>(outer cycle only)</H3>';

}
else  {
$sqlcmd= "SELECT * FROM derivatario $this->join_condition WHERE base REGEXP BINARY '$fullbase' and wfp1 REGEXP BINARY '$wfp$trail'";
echo '<H3>Derived words in '. $_POST["affisso"] .' that show a <b>morphotactic opacity</b> of '. $mt .' and a <b>morphosemantic opacity</b> of ' . $ms . 'are :</H3>';
}

else
 */

//Main selects
//Set first part:
$first = "$this->select_what FROM derivatario $this->join_condition WHERE";

/*
##Polysemy? If yes, add the join condition.
if ($_POST["polysemy"]== "yes") {
$polysemy = $_POST["polysemy"];
$first= "$this->select_what, wfp1_sem,wfp2_sem,wfp3_sem,wfp4_sem,wfp5_sem,wfp6_sem FROM derivatario $this->join_condition join itDerPolysemy on (derivatario.derived_id = itDerPolysemy.derived_id) WHERE";
}
else {
$polysemy = "no";
}
*/

//Con outer:
if (isset($_POST['outer']) && $_POST['outer'] != "no") {
	
//Solo parasintetico o grafico	
if (($_POST['g'] != "no" && $_POST['includeg']!= "no") || ($_POST['p'] != "no" && $_POST['includep']!= "no")) {
$sqlcmd= "SELECT $first $base (( wfp1 REGEXP BINARY '$wfp$trail' and wfp2 = '') or ( wfp2 REGEXP BINARY '$wfp$trail' and wfp3 = '') or ( wfp3 REGEXP BINARY '$wfp$trail' and wfp4 = '') or ( wfp4 REGEXP BINARY '$wfp$trail' and wfp5 = '') or ( wfp5 REGEXP BINARY '$wfp$trail' and wfp6 = '') or ( wfp1 REGEXP BINARY '$wfp$trail' and wfp2 REGEXP BINARY '.*$trail' and wfp3 ='') or ( wfp2 REGEXP BINARY '$wfp$trail' and wfp3 REGEXP BINARY '.*$trail' and wfp4 ='') or ( wfp3 REGEXP BINARY '$wfp$trail' and wfp4 REGEXP BINARY '.*$trail' and wfp5 ='') or ( wfp4 REGEXP BINARY '$wfp$trail' and wfp5 REGEXP BINARY '.*$trail' and wfp6 ='') or ( wfp5 REGEXP BINARY '$wfp$trail' and wfp6 REGEXP BINARY '.*$trail') or ( wfp1 REGEXP BINARY '$wfp$trail' and wfp2 REGEXP BINARY '.*$trail' and wfp3 REGEXP BINARY '.*$trail' and wfp4 ='') or ( wfp2 REGEXP BINARY '$wfp$trail' and wfp3 REGEXP BINARY '.*$trail' and wfp4 REGEXP BINARY '.*$trail' and wfp5 ='') or ( wfp3 REGEXP BINARY '$wfp$trail' and wfp4 REGEXP BINARY '.*$trail' and wfp5 REGEXP BINARY '.*$trail' and wfp6 ='')) $wc $this->alpha";
}

//Senza nessun trail
else if ($_POST['includeg'] != "yes" && $_POST['g']!= "yes" && $_POST['includep'] != "yes" && $_POST['p']!= "yes") 
{
$sqlcmd= "SELECT $first $base ((wfp1 REGEXP BINARY '$wfp$' and wfp2 = '') or (wfp2 REGEXP BINARY '$wfp$' and wfp3 = '') or (wfp3 REGEXP BINARY '$wfp$' and wfp4 = '') or (wfp4 REGEXP BINARY '$wfp$' and wfp5 = '') or (wfp5 REGEXP BINARY '$wfp$' and wfp6 = '')) $wc $this->alpha";
}

	//Con trail e senza: includo anche il caso in cui escluda uno dei due trail
else if (($_POST['includeg'] != "no" && $_POST['g']!= "yes" && $_POST['includep'] != "no" && $_POST['p']!= "yes") || ($_POST['includeg'] != "yes" && $_POST['g']!= "yes") || ($_POST['includep'] != "yes" && $_POST['p']!= "yes"))   {
$sqlcmd= "SELECT $first $base (( wfp1 REGEXP BINARY '$wfp$trail' and wfp2 = '') or ( wfp2 REGEXP BINARY '$wfp$trail' and wfp3 = '') or ( wfp3 REGEXP BINARY '$wfp$trail' and wfp4 = '') or ( wfp4 REGEXP BINARY '$wfp$trail' and wfp5 = '') or ( wfp5 REGEXP BINARY '$wfp$trail' and wfp6 = '') or ( wfp1 REGEXP BINARY '$wfp$trail' and wfp2 REGEXP BINARY '.*$trail' and wfp3 ='') or ( wfp2 REGEXP BINARY '$wfp$trail' and wfp3 REGEXP BINARY '.*$trail' and wfp4 ='') or ( wfp3 REGEXP BINARY '$wfp$trail' and wfp4 REGEXP BINARY '.*$trail' and wfp5 ='') or ( wfp4 REGEXP BINARY '$wfp$trail' and wfp5 REGEXP BINARY '.*$trail' and wfp6 ='') or ( wfp5 REGEXP BINARY '$wfp$trail' and wfp6 REGEXP BINARY '.*$trail') or ( wfp1 REGEXP BINARY '$wfp$trail' and wfp2 REGEXP BINARY '.*$trail' and wfp3 REGEXP BINARY '.*$trail' and wfp4 ='') or ( wfp2 REGEXP BINARY '$wfp$trail' and wfp3 REGEXP BINARY '.*$trail' and wfp4 REGEXP BINARY '.*$trail' and wfp5 ='') or ( wfp3 REGEXP BINARY '$wfp$trail' and wfp4 REGEXP BINARY '.*$trail' and wfp5 REGEXP BINARY '.*$trail' and wfp6 ='') or ( wfp1 REGEXP BINARY '$wfp$trail' and wfp2 = '') or ( wfp2 REGEXP BINARY '$wfp$trail' and wfp3 = '') or ( wfp3 REGEXP BINARY '$wfp$trail' and wfp4 = '') or ( wfp4 REGEXP BINARY '$wfp$trail' and wfp5 = '') or ( wfp5 REGEXP BINARY '$wfp$trail' and wfp6 = '') or (wfp1 REGEXP BINARY '$wfp$' and wfp2 = '') or (wfp2 REGEXP BINARY '$wfp$' and wfp3 = '') or (wfp3 REGEXP BINARY '$wfp$' and wfp4 = '') or (wfp4 REGEXP BINARY '$wfp$' and wfp5 = '') or (wfp5 REGEXP BINARY '$wfp$' and wfp6 = '')) $wc $this->alpha";
}
}
//Fine con outer
//Senza outer:
else  {
// Same as above, but includes all cycles
if (($_POST['g'] != "no" && $_POST['includeg']!= "no") || ($_POST['p'] != "no" && $_POST['includep']!= "no"))	{
$sqlcmd= "SELECT $first $base ((wfp1 REGEXP BINARY '$wfp$trail' or wfp2 REGEXP BINARY '$wfp$trail' or wfp3 REGEXP BINARY '$wfp$trail' or wfp4 REGEXP BINARY '$wfp$trail' or wfp5 REGEXP BINARY '$wfp$trail' or wfp6 REGEXP BINARY '$wfp$trail')) $wc $this->alpha";
}
else if  ($_POST['includeg'] != "yes" && $_POST['g']!= "yes" && $_POST['includep'] != "yes" && $_POST['p']!= "yes") 
{
$sqlcmd= "SELECT $first $base ((wfp1 REGEXP BINARY '$wfp$' or wfp2 REGEXP BINARY '$wfp$' or wfp3 REGEXP BINARY '$wfp$' or wfp4 REGEXP BINARY '$wfp$' or wfp5 REGEXP BINARY '$wfp$' or wfp6 REGEXP BINARY '$wfp$')) $wc $this->alpha";
}
else if (($_POST['includeg'] != "no" && $_POST['g']!= "yes" && $_POST['includep'] != "no" && $_POST['p']!= "yes") || ($_POST['includeg'] != "yes" && $_POST['g']!= "yes") || ($_POST['includep'] != "yes" && $_POST['p']!= "yes"))
{
	$sqlcmd= "SELECT $first $base ((wfp1 REGEXP BINARY '$wfp$trail' or wfp2 REGEXP BINARY '$wfp$trail' or wfp3 REGEXP BINARY '$wfp$trail' or wfp4 REGEXP BINARY '$wfp$trail' or wfp5 REGEXP BINARY '$wfp$trail' or wfp6 REGEXP BINARY '$wfp$trail' or wfp1 REGEXP BINARY '$wfp$' or wfp2 REGEXP BINARY '$wfp$' or wfp3 REGEXP BINARY '$wfp$' or wfp4 REGEXP BINARY '$wfp$' or wfp5 REGEXP BINARY '$wfp$' or wfp6 REGEXP BINARY '$wfp$')) $wc $this->alpha";
}
}//fine senza outer

//Con la posizione

if (isset($_POST['position']) && $_POST['position'] != 'all') {
$n = $_POST['position'];
if (($_POST['g'] != "no" && $_POST['includeg']!= "no") || ($_POST['p'] != "no" && $_POST['includep']!= "no"))	{

$sqlcmd= "SELECT $first $base (wfp$n REGEXP BINARY '$wfp$trail') $wc $this->alpha";
}
else if  ($_POST['includeg'] != "yes" && $_POST['g']!= "yes" && $_POST['includep'] != "yes" && $_POST['p']!= "yes") 
{
$sqlcmd= "SELECT $first $base (wfp$n REGEXP BINARY '$wfp$') $wc $this->alpha";
}
else if (($_POST['includeg'] != "no" && $_POST['g']!= "yes" && $_POST['includep'] != "no" && $_POST['p']!= "yes") || ($_POST['includeg'] != "yes" && $_POST['g']!= "yes") || ($_POST['includep'] != "yes" && $_POST['p']!= "yes"))
{
	$sqlcmd= "SELECT $first $base (wfp$n REGEXP BINARY '$wfp$trail' or wfp$n REGEXP BINARY '$wfp$') $wc $this->alpha";
}
}

}
//Fine parte relativa agli affissi



//Cerco il derivato

if (isset($_POST['derived']) && $_POST["derived"] != NULL) {
$_POST['affisso'] = NULL;
$_POST['base'] = NULL;
$_POST['paradigm_base'] = NULL;
$derived = $_POST['derived'];
//Select principale
$sqlcmd= "SELECT derivatario.derived_id, derivatario.derived, base, wfp1,wfp2,wfp3,wfp4,wfp5,wfp6,fqTot,word,gramCat,phoneSyll,numSylls,stressedSyllable FROM derivatario $this->join_condition WHERE derived LIKE '$derived' and base !='' $this->alpha" ;

}

	

//Inizio parte relativa ai risultati

if ($sqlcmd) {
$risultato = $this->exec_sql($sqlcmd);
$this->Sistema($sqlcmd);



//Format the response accordingly to the environment
//Main? Table/h3 character is smaller...
if (preg_match('/^main/', $id)) {
	echo "<style type='text/css'>
#miniquerier h3 {font-size: 12px;}
#miniquerier table {font-size: 12px;}
</style>";

}	

//Leftbox? Table/h3 character is tiny...
if (preg_match('/^leftbox/', $id)) {
	echo "<style type='text/css'>
#miniquerier h3 {font-size: 11px;}
#miniquerier table {font-size: 11px;}
</style>";
}	


if ((mysqli_num_rows($risultato) >= 1) && (mysqli_num_rows($risultato) <= 3000)) {

/*

//Head messages:
	if ($_POST["affisso"] != NULL) {
	echo '<H3>Derived words in '. $_POST["affisso"] .' showing a <b>morphotactic opacity</b> of '. $mt .' and a <b>morphosemantic opacity</b> of ' . $ms . ' are:</H3> ';
}


if ($_POST["base"] != NULL) {
	echo '<H3>Derived words containing ' . $_POST["base"] . ' are:</H3>';




}

if ($_POST["paradigm_base"] != NULL) {
	echo '<H3>Derived words containing ' . $_POST["paradigm_base"] . ' are:</H3>';

}

*/



//Feccia l'array

$feccia_array = array();
while($row = mysqli_fetch_assoc($risultato))
{
    $feccia_array[] = $row;
}



//Write table in main.php: array of array, array, $affix string
$hier_array = $this->orderFeccia($feccia_array);

$element = array();

//Pass to writeTabella a series of $_POST elements
$element["wfp"]= $_POST["affisso"].":".$allomorph.$mt.":".$ms;
$element["stemallomorphy"] = $_POST["stemallomorphy"];
$element["outest"] = $_POST["outer"];
$element["position"] = $_POST["position"];
$element["relfreq"] = $_POST["relfreq"];
$element["typebase"] = $_POST["typebase"];
$element["includep"] = $_POST["includep"];
$element["includeg"] = $_POST["includeg"];
$element["p"] = $_POST["p"];
$element["g"] = $_POST["g"];



$this->writeTabella($hier_array,$element);







}//Fine mysqli_num_rows
//Non trovo nulla:
else {
if (mysqli_num_rows($risultato) < 1) {
echo "Sorry, no form(s) was/were found.";
}
if (mysqli_num_rows($risultato) >= 3000) {
echo "Too many results. Sorry, I am little bit lazy, may I ask you to redefine your query?";
}

mysqli_close($GLOBALS['connection']);
}


}//Fine sqlcmd
else {
echo "<H3>You should provide at least one of these features: affix, base or derivative...</H3>";
}
//World ((the time has come)), my finger is on the button (Chemical Brothers tm). Display the close button if we are in the leftbox or in the main evn.
if ((preg_match('/^main/', $id)) || (preg_match('/^leftbox/', $id)))   {
	echo "<BUTTON type='button' onclick='closeWindow()'>Close</BUTTON>";
}
	}//Chiude cerca





public function menuPrincipaleEdita() {
echo"<H2>EDIT</H2>";
echo"<H3>By string:</H3>";
echo"<FORM ENCTYPE = 'multipart/form-data'  METHOD='POST' ACTION='edit.php' NAME='scegli3'>";
echo"<p align=center>";

echo"<INPUT TYPE='TEXT' NAME='string_edit' />";
echo"<select name='affix_type'>
  <option value ='suffix'>suffix</option>
  <option value ='prefix'>prefix</option>
</select>
How many results?
<INPUT TYPE='TEXT' NAME='limit' />
Morphosyntatic category?
<INPUT TYPE='TEXT' NAME='ms_category' />
<INPUT TYPE='SUBMIT' VALUE='edit!'  /></p>
</FORM>
<BR>
<H3>By affix:</H3>
<FORM ENCTYPE = 'multipart/form-data'  METHOD='POST' ACTION='edit.php' NAME='scegli4'>
<p align=center>
<select NAME='affix_edit' >";
foreach ($affix_array as $value) {
echo  "<option value ='$value'>$value</option>";
}

echo"</select>
How many results?
<INPUT TYPE='TEXT' NAME='limit' />
<INPUT TYPE='SUBMIT' VALUE='edit!'  /></p>
</FORM>
<BR>
<H3>By derivative:</H3>
<FORM ENCTYPE = 'multipart/form-data'  METHOD='POST' ACTION='edit.php' NAME='scegli5'>
<INPUT TYPE='TEXT' NAME='derived_edit' />
<INPUT TYPE='SUBMIT' VALUE='edit!'  /></p>
</FORM>
<BR>
</div>";



}



	public function Sistema($sqlcmd) {
//System message box
echo "<DIV id=\"sysmess\", class=\"floatingbox\">";
echo "***SYSTEM MESSAGES***";
echo "<br>";
echo $sqlcmd;
echo "<br>";
echo  "***END OF SYSTEM MESSAGES***";
echo "</DIV>";


}

public function guessWc() {
	if (isset($_POST['derived_id'])) {

$id = $_POST['derived_id'];

$risultato = $this->exec_sql("SELECT * from itforms where nLem='$id' LIMIT 1");

while ($row = mysqli_fetch_array($risultato)) {

$wordclass = $row['gramCat'];
echo "Wordclass is $wordclass";
}

}

else {
echo "Fatal Error: Wordclass not found!";
}

}




	public function Analizza() {
 // Is there a posted query string?
if(isset($_POST['derivedString']) || isset($_POST['baseString'])) {
	
//Man on a string (Shackleton TM): set the basestring id	
if (isset($_POST['idbaseString'])) {
$idbaseString = $_POST['idbaseString'];
//Javascript embedded
echo "<script type='text/javascript'>";
echo	"function fillBase(thisValue) {";
echo		"$('#$idbaseString').val(thisValue);";
echo		"setTimeout('$(\'#suggestions\').hide();', 200);";
echo	"}";
echo "</script>";
}

if (isset($_POST['idwfp1String'])) {
$idwfp1String = $_POST['idwfp1String'];
//Javascript embedded
echo "<script type='text/javascript'>";
echo	"function fillWfp1(thisValue) {";
echo		"$('#$idwfp1String').val(thisValue);";
echo		"setTimeout('$(\'#suggestions\').hide();', 200);";
echo	"}";
echo "</script>";
}

if (isset($_POST['idwfp2String'])) {
$idwfp2String = $_POST['idwfp2String'];
//Javascript embedded
echo "<script type='text/javascript'>";
echo	"function fillWfp2(thisValue) {";
echo		"$('#$idwfp2String').val(thisValue);";
echo		"setTimeout('$(\'#suggestions\').hide();', 200);";
echo	"}";
echo "</script>";
}

// We set the string accordingly:
$pseudoaffix = strlen($_POST['pseudoM']);
$pseudobase = strlen($_POST['derivedString']);



if($_POST['baseString'] == NULL && $_POST['derivedString'] != NULL) {
$pseudoderived = substr($_POST['derivedString'],0,($pseudobase-$pseudoaffix));
	$sqlcmd= "SELECT * FROM derivatario where (derived like '%$pseudoderived%' and wfp1 !='') or (base like '%$pseudoderived%' and wfp2 ='') LIMIT 7";
	}

if($_POST['baseString'] != NULL && $_POST['derivedString'] != NULL) {
$pseudoderived = substr($_POST['derivedString'],0,($pseudobase-$pseudoaffix));
	$basefeature = preg_split('/:/',$_POST['baseString']);
	$second = "or (base regexp '^$basefeature[0]:')";
if (preg_match('/^BASELESS/', $basefeature[0])) {
	$second = "";
	}
	$sqlcmd= "SELECT * FROM derivatario where (base regexp '^$basefeature[0]:' and derived like '%$pseudoderived%') $second LIMIT 7";
}

if($_POST['baseString'] != NULL  && $_POST['wfp1String'] != NULL) {
$basefeature = preg_split('/:/',$_POST['baseString']);
$wfp1feature = preg_split('/:/',$_POST['wfp1String']);
 $sqlcmd= "SELECT * FROM derivatario where (base regexp '$basefeature[0]' and wfp1 regexp '$wfp1feature[1]' and wfp2 !='') LIMIT 7";
	}

//Main query
$risultato= $this->exec_sql($sqlcmd);
echo $sqlcmd."\n";
//Main IF
if (mysqli_num_rows($risultato) >= 1) {
		echo "<H3>Suggestions</H3>" ;
//Cycle on derivedString
	if($_POST['baseString'] != NULL && $_POST['wfp1String'] == NULL) {
		while ($row = mysqli_fetch_array($risultato)) {
			if (strlen($row['wfp1']) > 4 ) {
			echo "<b>in the present (first) cycle:</b>";
			echo '<li onClick="fillWfp1(\''.$row['wfp1'].'\');">'.$row['wfp1']." (".$row['derived'].")".'</li>';
			}		
			if (strlen($row['wfp2']) > 4 ) {
			echo '<b>in outer cycles:</b>';
			echo '<li onClick="fillWfp1(\''.$row['wfp2'].'\');">'.$row['wfp2']." (".$row['derived'].")".'</li>';
		}
	}
	}
	else if($_POST['wfp1String']!= NULL && $_POST['baseString'] != NULL) {
		while ($row = mysqli_fetch_array($risultato)) {
			if (strlen($row['wfp2']) > 4 ) {
			echo "<b>in the present (second) cycle:</b>";
			echo '<li onClick="fillWfp2(\''.$row['wfp2'].'\');">'.$row['wfp2']." (".$row['derived'].")".'</li>';
			}		
			if (strlen($row['wfp3']) > 4 ) {
			echo '<b>in outer cycles:</b>';
			echo '<li onClick="fillWfp2(\''.$row['wfp3'].'\');">'.$row['wfp3']." (".$row['derived'].")".'</li>';
		}
	}


	}
	else if($_POST['baseString'] == NULL) {
		while ($row = mysqli_fetch_array($risultato)) {
	if (strlen($row['base']) > 4 )	{
		}
	echo '<li onClick="fillBase(\''.$row['base'].'\');">'.$row['base'].' ('.$row['derived'].')'.'</li>';
}
	}
    else {
    }
} //end if risultato
else
{
echo "Sorry, I have no suggestions yet...";
}
mysqli_close();
    }
}


public function Aggiungi() {
	//Counter used to count added forms.
	$i = 0;
		//Walking through the multidimensional array $_POST[field][n], where n is the number of each derived form (total n = total derived forms).
	foreach ($_POST['field'] as $field) {
		//Setting some variables... I use preg_replace to avoid whitespace within the string.
		$id = $field['id'];
		$derived = preg_replace( '/\s/', '', $field['derived']);

$wfp1="";
$wfp2="";
$wfp3="";
$wfp4="";
$wfp5="";
$wfp6="";

//Sanity check: i.control whether the wfp exists ii. control if everything is ok
if (strlen($field['wfp6']) >1) {

$feature = preg_split('/:/',$field['wfp6']);

if (!array_key_exists(0,$feature)) {
echo "<H2>Hey, you miss the affix for derivative $derived</H2>";
}
else if (!array_key_exists(1,$feature)) {
echo "<H2>Hey, you miss the allomorph for derivative $derived</H2>";

}
else {
$wfp6 = preg_replace( '/\s/', '', $field['wfp6']);
}

}

if  (strlen($field['wfp5']) >1) {

$feature = preg_split('/:/',$field['wfp5']);

if (!array_key_exists(0,$feature)) {
echo "<H2>Hey, you miss the affix for derivative $derived</H2>";
}
else if (!array_key_exists(1,$feature)) {
echo "<H2>Hey, you miss the allomorph for derivative $derived</H2>";

}
else {
$wfp5 = preg_replace( '/\s/', '', $field['wfp5']);
}

}

if  (strlen($field['wfp4']) >1) {

$feature = preg_split('/:/',$field['wfp4']);

if (!array_key_exists(0,$feature)) {
echo "<H2>Hey, you miss the affix for derivative $derived</H2>";
}
else if (!array_key_exists(1,$feature)) {
echo "<H2>Hey, you miss the allomorph for derivative $derived</H2>";

}
else {
$wfp4 = preg_replace( '/\s/', '', $field['wfp4']);
}

}

if (strlen($field['wfp3']) >1) {

$feature = preg_split('/:/',$field['wfp3']);

if (!array_key_exists(0,$feature)) {
echo "<H2>Hey, you miss the affix for derivative $derived</H2>";
}
else if (!array_key_exists(1,$feature)) {
echo "<H2>Hey, you miss the allomorph for derivative $derived</H2>";

}
else {
$wfp3 = preg_replace( '/\s/', '', $field['wfp3']);
}

}

if (strlen($field['wfp2']) >1) {

$feature = preg_split('/:/',$field['wfp2']);

if (!array_key_exists(0,$feature)) {
echo "<H2>Hey, you miss the affix for derivative $derived</H2>";
}
else if (!array_key_exists(1,$feature)) {
echo "<H2>Hey, you miss the allomorph for derivative $derived</H2>";

}
else {
$wfp2 = preg_replace( '/\s/', '', $field['wfp2']);
}

}

if (strlen($field['wfp1']) >1) {



$feature = preg_split('/:/',$field['wfp1']);

if (!array_key_exists(0,$feature)) {
echo "<H2>Hey, you miss the affix for derivative $derived</H2>";
}
else if (!array_key_exists(1,$feature)) {
echo "<H2>Hey, you miss the allomorph for derivative $derived</H2>";

}
else {
$wfp1 = preg_replace( '/\s/', '', $field['wfp1']);
}

}

		$base = preg_replace( '/\s/', '', $field['base']);
		if ($wfp1 != NULL) {
			//Main query: insert derived forms in the table derivatario
		$i++;
		$sqlupdate = "UPDATE colfis.derivatario SET base = '$base', wfp1 = '$wfp1', wfp2 = '$wfp2', wfp3 = '$wfp3', wfp4 = '$wfp4',  wfp5 = '$wfp5',  wfp6 = '$wfp6'  WHERE derivatario.derived_id = '$id'";
		$risultato=$this->exec_sql($sqlupdate);
		echo "<H3>Successfully added " . $derived . " with the following annotation: ".$wfp1.", ".$wfp2.", ".$wfp3."($sqlupdate)<BR /></H3>";
		}
		}
		echo "<H2>Grand Total: $i</H2>";
		}



public function Edita($affix) {
	//Setting some variables
	$pseudoM = "$";
//Get methods:
if ($affix != NULL) {
$queryAffix = "(wfp1 regexp binary '".$affix."' or wfp2 regexp binary '".$affix."' or wfp3 regexp binary '".$affix."')  and base != ''";
$pseudoM = $affix;
$limit = 30;

}

else

//Post methods:

if ($_POST['string_edit'] != NULL || $_POST['affix_edit'] != NULL || $_POST['derived_edit'] != NULL)   {
		
		
//String_edit
		if ((isset($_POST['string_edit']) && $_POST['string_edit'] != NULL))   {
					if ((isset($_POST['ms_category']) && $_POST['ms_category'] != NULL)) {
$ms_category = "and gramCat ='" . $_POST['ms_category']."'";
	}
			else {
				$ms_category= "";
	}

$queryAffix = "derived regexp binary '".$_POST['string_edit']."$' and base = ''";
if (isset($_POST['affix_type']) && $_POST['affix_type'] == "prefix")	{
$queryAffix = "derived regexp binary '"."^".$_POST['string_edit']."' and base = ''";
}
$affix_type = $_POST['affix_type'];
$pseudoM = $_POST['string_edit'];
		}

//Affix_edit
	if ((isset($_POST['affix_edit']) && $_POST['affix_edit'] != NULL))   {

		$affix = "wfp1 regexp binary '^".$_POST['affix_edit'].":'" . " or " . " wfp2 regexp binary '^".$_POST['affix_edit'].":'" . " or " . "wfp3 regexp binary '^".$_POST['affix_edit'].":'" . " or " . "wfp4 regexp binary '^".$_POST['affix_edit'].":'" . " or " . "wfp5 regexp binary '^".$_POST['affix_edit'].":'" . " or " . " wfp6 regexp binary '^".$_POST['affix_edit'].":'";
$pseudoM = $_POST['affix_edit'];
}

//Derived or part-of-, edit
if ((isset($_POST['derived_edit']) && $_POST['derived_edit'] != NULL))   {
$queryAffix = "derived regexp '".$_POST['derived_edit']."'";
}



//Define limit
			if ((isset($_POST['limit']) && $_POST['limit'] != NULL)) {
$limit = $_POST['limit'];
			}
			else {
$limit = 30;
			}


}

if ($queryAffix != NULL) {

//Main select
//We work on the derivatario table only.
$sqlcmd= "SELECT * FROM derivatario  WHERE $queryAffix LIMIT $limit";
$risultato = $this->exec_sql($sqlcmd);
echo $sqlcmd;
if (mysqli_num_rows($risultato) >= 1) {
	echo "<H2>Annotation</H2>";
echo "<FORM ENCTYPE = \"multipart/form-data\"  METHOD=\"POST\" ACTION=\"add.php\" id='$pseudoM'  >";
//Counter, useful to handle several forms with the same fields.
$i=0;
while ($row=mysqli_fetch_array($risultato)) {
	//I increase the value each cycle.
	$i++;
$derived_id = $row['derived_id'];
echo "<H3>Now editing ".$row['derived']." (".$row['derived_id'].") <BUTTON id=$derived_id type='button' onclick=lookupWc($derived_id) >Wordclass?</BUTTON></H3>";
	$baseString = "baseString".$i;
	$derivedString = "derivedString".$i;
	$wfp1String = "wfp1String".$i;
	$wfp2String = "wfp2String".$i;
	$wfp3String = "wfp3String".$i;
	$wfp4String = "wfp4String".$i;
	$wfp5String = "wfp5String".$i;
	$wfp6String = "wfp6String".$i;
	$idString = "idString".$i;

	$insert = "insert".$i;

	echo "<p>";


echo "Derivative: <INPUT TYPE=\"TEXT\" name=\"field[".$i."][derived]\"  id=\"$derivedString\" value=\"".$row['derived']."\"  />";
echo "<p>";
echo "Base: <INPUT  TYPE=\"TEXT\" name=\"field[".$i."][base]\"  id=\"$baseString\" value=\"".$row['base']."\" onclick=\"lookupDerived($derivedString.value,$baseString,$pseudoM);\" />";
echo "<p>";
echo "Wfp1: <INPUT TYPE=\"TEXT\" name=\"field[".$i."][wfp1]\" id=\"$wfp1String\" value=\"".$row['wfp1']."\" onclick=\"lookupBase($baseString.value,$derivedString.value,$wfp1String,$pseudoM);\"/>";
echo "<p>";
echo "Wfp2: <INPUT TYPE=\"TEXT\" name=\"field[".$i."][wfp2]\" id=\"$wfp2String\"  value=\"".$row['wfp2']."\" onclick=\"lookupWfp1($baseString.value,$derivedString.value,$wfp1String.value,$wfp2String);\"  />";
echo "<p>";
echo "Wfp3: <INPUT TYPE=\"TEXT\" name=\"field[".$i."][wfp3]\" id=\"$wfp3String\"  value=\"".$row['wfp3']."\" />";
echo "<p>";
echo "Wfp4: <INPUT TYPE=\"TEXT\" name=\"field[".$i."][wfp4]\" id=\"$wfp4String\"  value=\"".$row['wfp4']."\" />";
echo "<p>";
echo "Wfp5: <INPUT TYPE=\"TEXT\" name=\"field[".$i."][wfp5]\" id=\"$wfp5String\"  value=\"".$row['wfp5']."\" />";
echo "<p>";
echo "Wfp6: <INPUT TYPE=\"TEXT\" name=\"field[".$i."][wfp6]\" id=\"$wfp6String\"  value=\"".$row['wfp6']."\" />";
echo "<INPUT TYPE=\"HIDDEN\" name=\"field[".$i."][id]\" value=\"".$row['derived_id']."\" id=\"$idString\" />";
echo "<p>";
echo "<BR>";
} //end while cycle
echo "<INPUT TYPE=\"SUBMIT\" VALUE=\"Insert\"  /></p>";
echo "</FORM>";
} //end if risultato


//Let's construct the right box
echo "<div class=\"floatingbox\" id=\"rightbox\" >";
echo "<H3>Sandbox</H3>";
//$sqlcmd = "SELECT * from derivatario $this->join_condition where base != ''";
//$whole_array = $this->frugaDb($sqlcmd);

	if (!isset($_SESSION["whole_array"])) {
$sqlcmd = "SELECT $this->select_what from derivatario $this->join_condition where base != ''";
//Get the features from frugaDb	
global $_SESSION;	
$_SESSION["whole_array"]=$this->frugaDb($sqlcmd);
	}


$this->wfpMenu($_SESSION["whole_array"]);
echo "<button type='button' onclick='generateWfp()'>Generate!</button>";
echo "<br>";
echo "<div id='wfparea'>";
echo "<br>";

echo "You have created the following annotations:";
echo "<UL id=wfplist>";
echo "</UL>";
echo "</div>";
echo "</div>";

//Let's construct the mini-querier
echo "<div class=\"floatingbox\" id=\"leftbox\" >";
echo "<DIV id='miniquerier'>";
$this->writeSearchDerivative();
echo "</div>";
echo "</div>";
//Let's construct the suggestion box

echo "<div class='floatingBox' id='suggestionsBox' style='display: none;'>
                                <div class='floatingBox' id='autoSuggestionsList'>
                                        &nbsp;
                                </div>
                        </div>";

	}

else


{

echo "Try it again, Sam!";
}

}




	}//chiude la classe azioni
