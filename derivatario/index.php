<HTML>
<HEAD><TITLE>derIvaTario - Welcome!</TITLE>
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
<H2>Welcome!</b></H2>


<p>From these pages you can query or download derIvaTario, an annotated lexicon of about 11,000 Italian derivatives.</p>

<p>derIvaTario is based upon CoLFIS, a 3 million token corpus established in the nineties with the specific aim of representing the written language perceived by the average Italian reader. See the website <a href="http://esploracolfis.sns.it">http://esploracolfis.sns.it</a> for more information.</p>

<p>derIvaTario was created by manually segmenting into derivational cycles each of the 11,000 derivatives and annotating them with a wide array of features: information on affix and base allomorphy, the nature of morphotactic encountering between base and affix, the morphosemantic transparency of base and affix. Follow the link below to read the online documentation.</p>

<p>By query derIvaTario through the interface, you can browse it interactively and combine its unique morphological features with quantitative information present in the original <a href="http://linguistica.sns.it/colfis/home.htm">CoLFIS project</a> and with phonological representation provided by the <a href="http://www.phonitalia.org">Phonitalia project</a>. By downloading it as a CSV file, you can employ derIvaTario to automatically tag existent corpora with relevant morphological information; you can also use derIvaTario as a gold standard for morphologically-related NLP tasks.</p>

<CENTER>
<p><a href="derivatario.php">Query the lexicon</a></p>
<p><a href="derivatario.csv">Download the lexicon in comma-separated value format</a></p>
<p><a href="derivatario.sql">Download the lexicon as a SQL dump (contains part of itforms 1.10)</a></p>
<p><a href="derivatario.pdf">Read the online documentation (in Italian)</a></p>
</CENTER>

</DIV>
<?php
include_once('vanity.php');
?>
</BODY>
</HTML>

