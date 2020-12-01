# BlueSpiceCalumma Skin
Default Skin for BlueSpice 3

## Setup development environment

### Requirements

* Extension:Bootstrap
* Skin:Chameleon

    cd /mediawikiroot/skins
    git clone git@github.com:hallowelt/chameleon.git

Use [MediaWiki Composer Merge Plugin](https://www.mediawiki.org/w/index.php?title=Composer&oldid=2513147#Using_composer-merge-plugin) to install dependencies:

    cd /mediawikiroot
    cp composer.local.json-sample composer.local.json
    vim composer.local.json
    cat composer.local.json

    {
	    "extra": {
		    "merge-plugin": {
			    "include": [
				    "extensions/*/composer.json",
				    "skins/*/composer.json"
			    ]
		    }
	    }
    }

    composer install

### LocalSettings.php
    wfLoadSkin( 'BlueSpiceCalumma' );
    $wgDefaultSkin = "bluespicecalumma";
