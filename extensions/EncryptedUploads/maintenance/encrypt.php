<?php
#require_once('defuse-crypto.phar');
require_once '../vendor/autoload.php';

Defuse\Crypto\File::encryptFileWithPassword( 'sample.txt', 'sample.encrypted.txt', '1234567890' );
unlink('sample.txt');
