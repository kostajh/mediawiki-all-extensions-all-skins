#Setup

In the `LocalSettings.php` file set:

    wfLoadSkin( 'Vector' );
    wfLoadExtension( 'EventLogging' );
    wfLoadExtension( 'UniversalLanguageSelector' );

    wfLoadExtension( 'EUCopyrightCampaign' );
    wfLoadSkin( 'EUCopyrightCampaignSkin' );


Choose a page on the Wiki and set it's content to:

    <skin>eucopyrightcampaign</skin>
    {{Special:ContactYourMEP}}