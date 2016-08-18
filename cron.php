<?php

require_once('curl.php');
require_once('postImageToTwitter.php');

$initiate = new postImageToTwitter();
$run = $initiate->runProgram();