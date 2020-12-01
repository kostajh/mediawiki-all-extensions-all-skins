-- This is the relational database mapping of class RecordingAnnotation.
-- For details on fields, see RecordingAnnotation.php
-- For details on mapping, see RecordingAnnotationCRUD.php
CREATE TABLE IF NOT EXISTS /*_*/wikispeech_sdc_recording_annotation(
    wssdcra_identity varbinary(32) NOT NULL PRIMARY KEY,
    wssdcra_recording varbinary(32) NOT NULL default '',
    wssdcra_start int unsigned NOT NULL default 0,
    wssdcra_end int unsigned NOT NULL default 0,
    wssdcra_stereotype varbinary(32) NOT NULL default '',
    wssdcra_value varbinary(64)
)/*$wgDBTableOptions*/;

CREATE INDEX /*i*/list_by_recording ON /*_*/wikispeech_sdc_recording_annotation (wssdcra_recording);
CREATE INDEX /*i*/list_by_recording_and_stereotype ON /*_*/wikispeech_sdc_recording_annotation (wssdcra_recording, wssdcra_stereotype);
