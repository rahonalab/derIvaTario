<HTML>
<HEAD><TITLE>PHPCoLFIS - Main</TITLE>
   <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="TEXT/HTML; CHARSET=UTF-8">
<link rel="stylesheet" type="text/css" href="simple.css">
<script type="text/javascript" src="ajax/ajax.js"></script>
</HEAD>
<BODY>

<div id="external">
<DIV id="header">
<H1>PHPCoLFIS</H1></DIV>
<H2>Choose an affix...</H2>
<DIV id="main">
<FORM ENCTYPE = "multipart/form-data"  METHOD="POST" ACTION="main.php" NAME="scegli">
<p>UPPERCASE for AFFIXES.
<p align=center>
<INPUT TYPE="TEXT" NAME="affisso" />
mt<select name="mt">
  <option value ="0">All</option>
  <option value ="1">1</option>
  <option value ="2">2</option>
  <option value ="3">3</option>
  <option value ="4">4</option>
  <option value ="5">5</option>
  <option value ="6">6</option>
  <option value ="7">7</option>
  <option value ="8">8</option>
</select>
ms<select name="ms">
  <option value ="0">All</option>
  <option value ="1">1</option>
  <option value ="2a">2a</option>
  <option value ="2b">2b</option>
  <option value ="3">3</option>
</select>
only outer cycle <select name="outer">
<option value="no">No</option>
<option value="yes">Yes</option>
</select>

<INPUT TYPE="SUBMIT" VALUE="Find!"  /></p>
</FORM>

<H2>...or a base</H2>

<FORM ENCTYPE = "multipart/form-data"  METHOD="POST" ACTION="main.php" NAME="scegli1">
<p>search a BASE.
<p align=center>
<INPUT TYPE="TEXT" NAME="base" />
type <select name="typebase">
  <option value ="any">any</option>
  <option value ="root">root</option>
  <option value ="semiword">semiword</option>
  <option value ="baseless">baseless</option>
  <option value ="vt">verbal theme</option>
  <option value ="ppres">present participle</option>
  <option value ="irrpp">irregular past participle</option>
  <option value ="latpp">latinate past participle</option>
</select>

<INPUT TYPE="SUBMIT" VALUE="Find!"  /></p>
</FORM>

</div>
</div>
<?php
include_once('vanity.php');
?>
</BODY>
</HTML>

