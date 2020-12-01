CREATE TABLE /*_*/uifeedback (
  uif_id         INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  uif_type       INT(1), -- 0 Questionnaire, 1 Screenshot
  uif_created    TIMESTAMP DEFAULT NOW(), -- Timestamp
  uif_url        VARCHAR(255), -- URL where that feedback was given
  uif_task       VARCHAR(255), --
  uif_done       INT(1), -- 0 no, 1 yes, '' undefined
  uif_text1      TEXT, -- free text (to be defined)
  uif_importance INT(1), -- 0 unknown, 1 critical, 2 serious, 3 cosmetic
  uif_happened   INT(1), -- 0 unknown, 1 not expected, 2 confused, 3 missing feature, 4 other
  uif_username   VARCHAR(255), -- Username of the reporter or anonymous
  uif_notify     INT(1), -- 0 User dont want a notification on status-change, 1 Notify
  uif_useragent  VARCHAR(255), -- UserAgent (Browser/OS identification)
  uif_status     INT(1), -- actual status
  uif_comment    TEXT -- actual comment
)/*$wgDBTableOptions*/;

CREATE INDEX /*i*/uifeedback_type ON /*_*/uifeedback (uif_type);
CREATE INDEX /*i*/uifeedback_status ON /*_*/uifeedback (uif_status);
CREATE INDEX /*i*/uifeedback_importance ON /*_*/uifeedback (uif_importance);
CREATE INDEX /*i*/uifeedback_username ON /*_*/uifeedback (uif_username);
CREATE INDEX /*i*/uifeedback_created ON /*_*/uifeedback (uif_created);

CREATE TABLE /*_*/uifeedback_reviews (
  uifr_id          INT           NOT NULL PRIMARY KEY AUTO_INCREMENT,
  uifr_feedback_id INT           NOT NULL, -- ID of the FeedbackItem
  uifr_created     TIMESTAMP DEFAULT NOW(), -- Timestamp
  uifr_reviewer    VARCHAR(256)  NOT NULL, -- username of the reviewer
  uifr_status      INT(1)        NOT NULL, -- 0 open, 1 in review, 2 closed, 3 declined
  uifr_comment     VARCHAR(2000) NOT NULL   -- comment for actual status, e.g. a reason for rejection or a link to bugzilla-bug
)/*$wgDBTableOptions*/;

ALTER TABLE /*_*/uifeedback_reviews
ADD CONSTRAINT FOREIGN KEY
  (uifr_feedback_id) REFERENCES uifeedback (uif_id);

CREATE INDEX /*i*/uifeedback_reviews_id ON /*_*/uifeedback_reviews (uifr_feedback_id);
CREATE INDEX /*i*/uifeedback_reviews_created ON /*_*/uifeedback_reviews (uifr_created);


CREATE TABLE /*_*/uifeedback_stats (
  uifs_type    INT(1) PRIMARY KEY, -- 0 dynamic request (popup), 1 questionnaire-button, 2 screenshot-button
  uifs_shown   INT NOT NULL, -- number of views
  uifs_clicked INT NOT NULL, -- number of clicks
  uifs_sent    INT NOT NULL     -- number of sent forms
)/*$wgDBTableOptions*/;

INSERT INTO /*_*/uifeedback_stats (
  uifs_type, uifs_shown, uifs_clicked, uifs_sent
) VALUES (0, 0, 0, 0);

INSERT INTO /*_*/uifeedback_stats (
  uifs_type, uifs_shown, uifs_clicked, uifs_sent
) VALUES (1, 0, 0, 0);

INSERT INTO /*_*/uifeedback_stats (
  uifs_type, uifs_shown, uifs_clicked, uifs_sent
) VALUES (2, 0, 0, 0);
