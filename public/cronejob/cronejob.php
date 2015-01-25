<?php

$date = date('Y-m-d H:i:s')."\r\n";
error_log("$date", 3, "cronejob.log");