# mediawiki-extensions-BlueSpicePageTree
make <subpagetree root="Namespace:Rootpage" /> tag available and show extjs tree panel with subpage structure

Dependencies:

	BlueSpiceFoundation

Activate extension in LocalSettings.php

	echo "wfLoadExtension(\"BlueSpiceSubPageTree\");" >> LocalSettings.php

Usage in WikiText

	<subpagetree root=":"/> or <subpagetree root="Template:"/> or <subpagetree root="Main Page"/>