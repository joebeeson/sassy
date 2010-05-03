# Sassy Plugin for CakePHP 1.3+

This plugin monitors folders for [Sass][1] files and compiles them into CSS. It uses [PHamlP][2] to perform the compiling.

> "*Sass is a meta-language on top of CSS that’s used to describe the style of a document cleanly and structurally, with more power than flat CSS allows. Sass both provides a simpler, more elegant syntax for CSS and implements various features that are useful for creating manageable stylesheets.*"

## Installation

* Download the plugin

        $ cd /path/to/your/app/plugins && git clone git://github.com/joebeeson/sassy.git

* Add the helper to your `AppController`

        public $helpers = array('Sassy.Monitor');

## Configuration

* **`Sassy.Recompile.Percentage`** - The chance percentage that each request has of invoking a check for updated files. Valid values are an integer between 0 and 100. This defaults to `10`

* **`Sassy.Recompile.Parameter`** - The named parameter to look for in the request that will force a recompile check. This defaults to `sassy`

* **`Sassy.Recompile.Folders`** - An array of folders to monitor for Sass files. 
 This defaults to `app/webroot/css`

## Usage

Start making Sass files and make sure their extension is `.sass` -- that's it. The helper will periodically check for any files that need (re)compiling and handle everything for you.

  [1]: http://sass-lang.com/
  [2]: http://code.google.com/p/phamlp/
