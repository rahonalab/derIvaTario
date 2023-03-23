<?php
session_start();
ini_set('display_errors', 'On');
error_reporting(E_ALL);
require_once('include/connect.php');
require_once('include/main.php');
?>

<HTML>
<HEAD><TITLE>derIvaTario - Main</TITLE>
   <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="TEXT/HTML; CHARSET=UTF-8">
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/nugae.js"></script>
<link rel="stylesheet" type="text/css" href="simple.css">
</HEAD>
<BODY>
<div id="external">
<DIV id="header">derIvaTario</DIV>
<DIV id="derivatario">An annotated lexicon of Italian derivatives</DIV>
<DIV id="internal">
<H2>Query the lexicon</H2>
<?php
if (file_exists("array.bin")) {
global $_SESSION;
$_SESSION["whole_array"]= unserialize(file_get_contents('array.bin'));
}

$actio = new Azioni();
$actio->menuPrincipale();
?>
</div>
</div>
<?php
if (isset($_SESSION["whole_array"])) {
$whole_array = $_SESSION["whole_array"];
$lexical_base_array=$whole_array[10];
$paradigmatic_base_array=$whole_array[8];
$lexical_base_number=count($lexical_base_array)+count($paradigmatic_base_array)+count($whole_array[9]);
$affix_number=count($whole_array[0]);
$derivative_number=count($whole_array[11]);
echo "<DIV id='bottom'>";
echo "Stats: $derivative_number derivatives, $lexical_base_number lexical bases, $affix_number affixes in the database.";
echo "</div>";
}

include_once('vanity.php');
?>
</BODY>
</HTML>

