# Figures
A MediaWiki extension to define figures and refer them elsewhere in the wiki.
Figures can be images, tables or other kinds of media.

#Installation

For installation of this extension you need to have ssh access to your server.

* To install the extension, place the entire 'Figures' directory within your MediaWiki 'extensions' directory
* Just enter the following command in the 'extensions' directory: 'git clone https://bitbucket.org/wikiworksdev/figures.git Figures'
* Add the following line to your LocalSettings.php file: 'wfLoadExtension( 'Figures' );'
* Verify you have this extension installed by visiting the /Special:Version page on your wiki.

#Usage
To create a figure define it using the figure parser function. See examples below.

{{#figure:
|label=Figure 1.2
|content=[[File:wiki.png]]
}}

To refer to the figure elsewhere in the wiki use:

{{#xref:
|page=Main Page
|label=Figure 1.2
}}

The xref parser function will create an anchor tag which takes you to the figure on clicking.


#Credits
This extension has been written by Nischay Nahata for wikiworks.com and is sponsored by NATO
