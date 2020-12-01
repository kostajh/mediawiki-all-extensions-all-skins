DELETE FROM /*$wgDBprefix*/bs_expiry WHERE /*$wgDBprefix*/bs_expiry.expires = 0;
UPDATE /*$wgDBprefix*/bs_expiry
JOIN /*$wgDBprefix*/bs_reminder ON /*$wgDBprefix*/bs_expiry.rem_id = /*$wgDBprefix*/bs_reminder.rem_id
SET
    /*$wgDBprefix*/bs_expiry.exp_date = /*$wgDBprefix*/bs_reminder.rem_date,
    /*$wgDBprefix*/bs_expiry.exp_page_id = /*$wgDBprefix*/bs_reminder.rem_page_id;