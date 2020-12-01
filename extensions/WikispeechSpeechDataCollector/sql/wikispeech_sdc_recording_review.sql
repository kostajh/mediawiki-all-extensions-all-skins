-- This is the relational database mapping of class RecordingReview.
-- For details on fields, see RecordingReview.php
-- For details on mapping, see RecordingReviewCRUD.php
CREATE TABLE IF NOT EXISTS /*_*/wikispeech_sdc_recording_review(
    wssdcrr_identity varbinary(32) NOT NULL PRIMARY KEY,
    wssdcrr_created varbinary(14) NOT NULL default '',
    wssdcrr_value int unsigned NOT NULL default 0,
    wssdcrr_reviewer varbinary(32) NOT NULL default '',
    wssdcrr_recording varbinary(32) NOT NULL default ''
)/*$wgDBTableOptions*/;

CREATE INDEX /*i*/list_by_reviewer ON /*_*/wikispeech_sdc_recording_review (wssdcrr_reviewer);
CREATE INDEX /*i*/list_by_recording ON /*_*/wikispeech_sdc_recording_review (wssdcrr_recording);
CREATE INDEX /*i*/get_by_recording_and_reviewer ON /*_*/wikispeech_sdc_recording_review (wssdcrr_recording, wssdcrr_reviewer);
