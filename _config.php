<?php
//Rename CMC_BULKUPDATER_MODULE_DIR to avoid naming collisions with other modules
if ( ! defined('CMC_BULKUPDATER_MODULE_DIR')) {
     define('CMC_BULKUPDATER_MODULE_DIR', basename(dirname(__FILE__)));
}