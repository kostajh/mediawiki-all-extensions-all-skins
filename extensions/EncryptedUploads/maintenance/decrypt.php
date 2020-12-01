<?php
#require_once('defuse-crypto.phar');
require_once '../vendor/autoload.php';

Defuse\Crypto\File::decryptFileWithPassword( 'sample.encrypted.txt', 'sample.txt', '1234567890' );
unlink('sample.encrypted.txt');
