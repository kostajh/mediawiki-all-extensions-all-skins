# Wikispeech version changelog

## About

Wikispeech use [Semantic versioning](https://semver.org/). Major.Minor.Patch.

## Versioning

Add new entries to the top of current -SNAPSHOT section,
i.e. in reversed chronological order.

Annotate your code with @since using the current -SNAPSHOT version.
E.g. when the current is 0.1.2-SNAPSHOT, use @since 0.1.2 in the code.

## On release

Remove -SNAPSHOT, set date and create a new -SNAPSHOT section.

If version bump is greater than originally expected,
e.g. from 0.1.2-SNAPSHOT to 0.2.0,
then replace all @since 0.1.2 tags in the code to 0.2.0 using a new task.

Update [mediawiki.org documentation](https://www.mediawiki.org/wiki/Extension:Wikispeech)
the match the new release version.

Update version in extension.json.

## Versions

### 0.1.8-SNAPSHOT
YYYY-MM-DD
Speechoid: 0.1.2

* [T264403](https://phabricator.wikimedia.org/T264403) Speechoid/Wikispeech benchmark maintenance script.
* [T263112](https://phabricator.wikimedia.org/T263112) Listening action should not be available in non-read view
* [T261288](https://phabricator.wikimedia.org/T261288) Add option to deactivate and reactivate player in menu

### 0.1.7
2020-10-19
Speechoid: 0.1.2

* [T265335](https://phabricator.wikimedia.org/T265335) Introduce PHP namespaces.
* [T264899](https://phabricator.wikimedia.org/T264899) Introduce configurable response timeout in SpeechoidConnector.
* [T263115](https://phabricator.wikimedia.org/T263115) Break out getDefaultVoice() to separate class.
* [T260905](https://phabricator.wikimedia.org/T260905) Refactored (shortened) name of flushUtterancesByExpirationDateOnFileFromFileBackend.
  @since tags on renamed methods and classes is bumped from 0.1.5 to 0.1.7,
  but due to the pre-release state of the project
  we did not keep and deprecate the previous method names.
* [T251261](https://phabricator.wikimedia.org/T251261) Mechanisms for executing flushing of utterances from the store.
* [T254737](https://phabricator.wikimedia.org/T254737) Rename APIs to wikispeech-listen and wikispeech-segment.

### 0.1.6
2020-09-22
Speechoid: 0.1.2

* [T262387](https://phabricator.wikimedia.org/T262387) Update to use Speechoid 0.1.2.
* [T262948](https://phabricator.wikimedia.org/T262948) Request default voice per language from Speechoid.
* [T261753](https://phabricator.wikimedia.org/T261753) Allow listening to page even if edited.
* [T262655](https://phabricator.wikimedia.org/T262655) Versioning of Speechoid.
* [T257078](https://phabricator.wikimedia.org/T257078) Trigger Wikispeech UI on PageContentLanguage, not interface language.

### 0.1.5
2020-09-03
Speechoid: 0.1.1

* [T248162](https://phabricator.wikimedia.org/T248162) Use revision and segment ID as input for synthesizing speech.
* [T260891](https://phabricator.wikimedia.org/T260891) Allow historical revisions to be accessed in Segmenter cache.
* [T260875](https://phabricator.wikimedia.org/T260875) Enable using default parameters for segmenting.
* [T255001](https://phabricator.wikimedia.org/T255001) Schedulable cleanup job of orphaned utterance files in file backend.
* [T257571](https://phabricator.wikimedia.org/T257571) Extension is owner of default voice per language logic.
* [T243579](https://phabricator.wikimedia.org/T243579) Add a pre-loaded trigger for Wikispeech.
* [T199414](https://phabricator.wikimedia.org/T199414) Config variable `WikispeechServerUrl` renamed `WikispeechSpeechoidUrl`.
* [T247395](https://phabricator.wikimedia.org/T247395) Limit input length in Speechoid requests.
* [T248825](https://phabricator.wikimedia.org/T248825) Clean up segmenting.
* [T248469](https://phabricator.wikimedia.org/T248469) Create database for utterance data.
* [T181780](https://phabricator.wikimedia.org/T181780) Use OOUI for the player controls.

### 0.1.4
2020-05-19
Speechoid: 0.1.0

* [T248472](https://phabricator.wikimedia.org/T248472) Create segment hasher.
* [T246079](https://phabricator.wikimedia.org/T246079) Add cache to segmenter.
* [T249198](https://phabricator.wikimedia.org/T249198) Version changelog file introduced.

### 0.1.3
2018-09-12
Speechoid: 0.1.0

* Version 0.1.3 and earlier is not documented in this file.
