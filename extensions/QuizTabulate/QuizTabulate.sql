-- SQL for database schema for the QuizTabulate extension.
CREATE TABLE IF NOT EXISTS /*_*/quiz_tabulate (
  quiz_tabulate_id INT unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  quiz_rev_id INT(10) unsigned NOT NULL,
  question_id INT(10) unsigned NOT NULL,
  answer_id INT(10) unsigned NOT NULL,
  answer_text BLOB NOT NULL,
  count_attempt INT(10) unsigned NOT NULL
) /*$wgDBTableOptions*/;
CREATE TABLE IF NOT EXISTS /*_*/quiz_tabulate_questions (
  quiz_tabulate_questions_id INT unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  quiz_rev_id INT(10) unsigned NOT NULL,
  question_id INT(10) unsigned NOT NULL,
  question_text BLOB NOT NULL
) /*$wgDBTableOptions*/;
