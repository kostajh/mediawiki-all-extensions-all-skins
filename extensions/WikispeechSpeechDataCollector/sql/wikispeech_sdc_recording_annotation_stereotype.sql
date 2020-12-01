-- This is the relational database mapping of class RecordingAnnotationStereotype.
-- For details on fields, see RecordingAnnotationStereotype.php
-- For details on mapping, see RecordingAnnotationStereotypeCRUD.php
CREATE TABLE IF NOT EXISTS /*_*/wikispeech_sdc_recording_annotation_stereotype(
    wssdcras_identity varbinary(32) NOT NULL PRIMARY KEY,
    wssdcras_value_class varchar(32) NOT NULL default '',
    wssdcras_description varchar(256) NOT NULL default ''
)/*$wgDBTableOptions*/;
