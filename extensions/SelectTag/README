MediaWiki Extension
Name: SelectTag
Author: Khaled El Mansoury

Example:

---LocalSettings.php---
$wgSelectTag[ 'examplesource' ][ '_dbname' ] = 'exampletbl';
$wgSelectTag[ 'examplesource' ][ 'attr1' ] = 'field1';
$wgSelectTag[ 'examplesource' ][ 'attr2' ] = 'field2';
$wgSelectTag[ 'examplesource' ][ 'attr3' ] = 'field3';
$wgSelectTag[ 'examplesource' ][ '_show' ][ 'show1' ] = 'field4';
$wgSelectTag[ 'examplesource' ][ '_showDefault' ] = 'show1';

---Page---
<select _source="examplesource" arr1="value1" arr2="value2" arr3="value3" _show="show1" />

---Resulting SQL query---
SELECT field1, field2, field3 FROM exampletbl
WHERE arr1='value1' AND arr2='value2' AND arr3='value3';

The full documentation for the SelectTag extension can be found at the following address:
http://www.expressprogs.com/products/mwext/selecttag/
The MediaWiki extension page can be found at this address:
https://www.mediawiki.org/wiki/Extension:SelectTag
