# Requirements

- Mediawiki 1.30+
- Composer installed

# Setup

- Clone repository into `extensions` directory of Mediawiki
- `cd` into extension directory and run `composer install`
- Add `wfLoadExtension('EncryptedUploads');` line to the bottom of your `LocalSettings.php`
- In the Mediawiki root run `php maintenance/update.php --quick`

# Use

- Navigate to `Special:Upload` page as a user with an `upload` permission
- Select file to upload and check `Encrypt upload` checkbox under Summary field
- Submit upload. File will be processed, encrypted and you'll see a secret key (visible only for you) to decrypt & download it

# Integration with UploadWizard

- Integrates with patched version of UploadWizard ( find patch in `patch` folder of the repository )

# Configuration

- `$wgEncryptedUploadsSendMail` could be set to true to also email secret key to the uploader
