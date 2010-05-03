# Sassy Plugin for CakePHP 1.3+

Sassy plugin monitors folders for [Sass][1] files and compiles them into CSS.

## Installation

* Download the plugin

        $ cd /path/to/your/app/plugins && git clone git://github.com/joebeeson/sassy.git

* Add the component to your `AppController`

        public $components = array('Sassy.SassMonitor');

## Configuration

* **`Sassy.Recompile.Percentage`** - The chance percentage that each request has of invoking a check for updated files. Valid values are an integer between 0 and 100. This defaults to `5`

* **`Sassy.Recompile.Parameter`** - The named parameter to look for in the request that will force a recompile check. This defaults to `sassy`

* **`Sassy.Recompile.Folders`** - An array of folders to monitor for Sass files. 
 This defaults to `app/webroot/css`

## Usage

On any given request the `SassMonitor` component will determine if it should execute based off the `Sassy.Recompile.Percentage` value. It will check every folder in the `Sassy.Recompile.Folders` value for any files that end in `.sass` and are newer than their `.css` counterpart. If any are found it will parse the file and write the corresponding `.css` file. 

  [1]: http://sass-lang.com/
