<?php

G::loadClass('pmFunctions');
G::LoadClass("case");
header("Content-Type: text/plain");
$thematique = $_REQUEST['thematique'];
limousinProject_getAffilieAqoba($thematique);