# ImageCompare
A MediaWiki extension to compare two images using an interactive slider.

This extension adds the `<imgcomp>` tag which displays an interactive slider for comparing two images side by side.

## Installation
- Place the files in a directory called `ImageCompare` in your `extensions/` folder.

- Add the following code at the bottom of your LocalSettings.php:

`require_once "$IP/extensions/ImageCompare/ImageCompare.php";`

- **Done.** Navigate to Special:Version on your wiki to verify that the extension is successfully installed.

## Usage
To add two images to compare, use the `<imgcomp>` tag with these attributes (bold ones are required):

- **img1**: The title of the image to the left ("before").
- **img2**: The title of the image to the right ("after").
- width: A number specifying the width for compared images in pixels. By default, images are added with their highest resolution.

The titles must not have the **File:** namespace. The inner content of the tag will be ignored.

Ideally, the two images should have the same width and height.

### Examples
To compare **JPEG example.jpg** with **PNG example.png**:

`<imgcomp img1='JPEG example.jpg' img2='PNG example.png' />`

To compare **Draft.png** and **Colored.png**, with each image having the width of 400px:

`<imgcomp img1='Draft.png' img2='Colored.png' width=300 />`