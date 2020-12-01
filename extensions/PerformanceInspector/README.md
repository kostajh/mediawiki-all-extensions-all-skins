# PerformancInspector

The PerformanceInspector is a extension for mediawiki, to make it easier for editors to find potential performance problems. You can read more about the extension at  https://www.mediawiki.org/wiki/Extension:PerformanceInspector.


## How it works
The extension has a startup module; when activated it adds a link to the tools menu.
When you start the tool it will run the collectors, parse the data against the view templates and display everything in a dialog.

### The details
The most important part of the inspector is the collectors. A collector collects metrics/data from different part of the system and makes it available in a dialog for the user.

### A collector
There's a couple of things a collector needs to do.

The collector needs to add a function to the collectors array, that will run when the data is collected. It happens when the user activates the inspector.

<pre>
module.exports.collectors.push( myFunction );
</pre>

That function needs to return a Promise/value that have the following structure:

<pre>
{
  view: {
    name: '', // The mw key name for the name of the collector, displayed as a heading in the text
    label: '', // The mw key name for the label of the collector, displayed as the label listing
    template: '', // The mustache template to parse with the view
    data: {...} // The data/metrics that will be merged with the template
    postProcess: postProcess, // Optional function to be run after the template and data is merged
  },
  summary {
    // arbitrary data that will be matched against the summary template. Keep it empty your collector will not add anything to the summary section
  }

}
</pre>

There's one extra bonus thing. The **postProcess** function that if it exists, will run after the template has been merged, passing the parsed HTML as a parameter, making it possible to manipulate the outcome by Javascript (for graphs etc).

### Summary section
The summary data will be merged together with the summary.mustache template and put on top of the result page. Every collector can add to the summary and it's all merged with the same template. If you want to add information to the summary section, you change the template.

### Message handling
The mustache templates are parsed together with the data passed by the collector and the mw.msg function. In the mustache template you can get a message by using the key *msg*:

<pre>
	{{#msg}}performanceinspector-imagesize-column-size{{/msg}}
</pre>

## Adding a new collector
There's three steps that you need to do:

* Create your collector in modules/collectors/ (and make sure you follow the structure of a collector).
* Add the template for the collector in modules/templates
* If you want add data to the summary section, change the *summary.mustache* template to include the data you pass.
* Add your script to **extension.js** in the analyze module *ext.PerformanceInspector.analyze*
