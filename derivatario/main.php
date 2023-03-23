<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
require_once('include/connect.php');
require_once('include/main.php');
session_start();
?>
<HTML>
<HEAD><TITLE>derIvaTario - Query Results</TITLE>
   <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="TEXT/HTML; CHARSET=UTF-8">
<link rel="stylesheet" type="text/css" href="simple.css">
<script src="https://code.jquery.com/jquery-3.6.3.js"></script>
<script type="text/javascript" src="js/nugae.js"></script>
</HEAD>
<BODY>
<DIV id="external">
<DIV ID="header">derIvaTario</div>
<DIV id="derivatario">An annotated lexicon of Italian derivatives</DIV>
<div id="internal">
<H2>Query the lexicon: results</H2>
<?php

$actio = new Azioni();
$actio->Cerca("general");
?>
</DIV>
</DIV>
</DIV>
<?php
include_once('vanity.php');
?>
</BODY>
</HTML>
