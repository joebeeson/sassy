<?php

	/**
	 * SassMonitorComponent
	 * -------------------------------------------------------------------------
	 * Monitors folders and checks if any Sass files in them are newer than 
	 * their CSS counterpart and recompiles them if they are.
	 * 
	 * To try and keep down our overhead we only execute on a percentage of the
	 * requests. This defaults to 10% but can be changed by setting the value of
	 * 'Sassy.Recompile.Percentage' in your configuration.
	 * 
	 * We will force a regeneration if a named parameter that matches the value
	 * for the 'Sassy.Recompile.Parameter' configuration is seen. If this value
	 * isn't set we will bypass that functionality entirely.
	 * -------------------------------------------------------------------------
	 * @author Joe Beeson <jbeeson@gmail.com>
	 */
	class SassMonitorComponent extends Object {
		
		/**
		 * Holds the expected name for our configuration setting which tells us
		 * our probability for executing a recompile check.
		 * @var string
		 * @const
		 */
		const RECOMPILE_PERCENTAGE = 'Sassy.Recompile.Percentage';
		
		/**
		 * Holds our default recompile percentage if no configuration has been
		 * set for us.
		 * @var integer
		 * @const
		 */
		const RECOMPILE_PERCENTAGE_DEFAULT = 10;
		
		/**
		 * Holds the expected name for our named parameter to monitor which will
		 * force a recompile if it is passed in a request.
		 * @var string
		 * @const
		 */
		const RECOMPILE_PARAMETER = 'Sassy.Recompile.Parameter';
		
		/**
		 * Holds our default recompile parameter if no configuration has been set
		 * for us already.
		 * @var string
		 * @const
		 */
		const RECOMPILE_PARAMETER_DEFAULT = 'sassy';
		
		/**
		 * Holds the expected name for our configuration option for which folders
		 * we should be watching for Sass files.
		 * @var string
		 * @const
		 */
		const RECOMPILE_FOLDERS = 'Sassy.Recompile.Folders';
		
		/**
		 * Initialization method, executed before the controller's beforeFIlter 
		 * but after the models have been constructed.
		 * @param Controller $controller
		 * @return null
		 * @access public 
		 */
		public function initialize($controller) {
			$this->controller = $controller;
			if ($this->_shouldRun()) {
				foreach ($this->_getEligibleFiles() as $source=>$compile) {
					file_put_contents(
						$compile,
						$this->_parseFile($source)
					);
				}
			}
		}
		
		/**
		 * Returns an array of files that are due for recompiling. The key of the
		 * array represents the current source file and the value is where the 
		 * source should be compiled to.
		 * @return array
		 * @access protected
		 */
		protected function _getEligibleFiles() {
			$return = array();
			foreach ($this->_getMonitorFolders() as $from=>$to) {
				$from = new Folder($from);
				foreach ($from->find('.+\.sass') as $file) {
					$source   = $from->path . $file;
					$compiled = $to . substr($file, 0, -4) . 'css';
					if (!file_exists($compiled) or filemtime($source) > filemtime($compiled)) {
						$return[$source] = $compiled;
					}
				}
			}
			return $return;
		}
		
		/**
		 * Returns an array of folders that are to be monitored. Our key is the
		 * folder we should monitor and the value is the folder we should write
		 * the parsed files to.
		 */
		protected function _getMonitorFolders() {
			$return  = array();
			$folders = (is_null(Configure::read(self::RECOMPILE_FOLDERS))
				? array(CSS) 
				: Configure::read(self::RECOMPILE_FOLDERS)
			);
			$folders = array_flip($folders);
			foreach ($folders as $to=>&$from) {
				if (substr($to, -1) != DS) {
					$to .= DS;
				}
				if (substr($from, -1) != DS) {
					$from .= DS;
				}
				$return[$from] = $to;
			}
			return $return;
		}
		
		/**
		 * Parses the passed $file and returns the compiled CSS. Will catch any
		 * errors thrown by the SassParser -- if one occurs we will return a
		 * boolean false value to indicate a failure.
		 * @param string $file
		 * @return mixed
		 */
		protected function _parseFile($file = '') {
			$return = false;
			if (file_exists($file)) {
				try {
					$return = $this->_getParser()->toCss($file);
				} catch (SassContextException $exception) {
					$this->log('SassMonitor::_parseFile() was unable to parse "'. $file .'" because it contains errors: '. $exception->getMessage());
				}
			}
			return $return;
		}
		
		/**
		 * Convenience method for retrieving a new SassParser instance with the
		 * given style and options. Defaults to the 'expanded' style.
		 * @param string $style
		 * @return SassRenderer
		 * @access protected
		 */
		protected function _getParser($style = 'expanded', $options = array()) {
			$cache = 'parser' . md5(serialize(func_get_args()));
			if (!isset($this->cache[$cache])) {
				if (!class_exists('SassParser')) {
					require($this->_sassPath() . 'SassParser.php');
				}
				$this->cache[$cache] = new SassParser(
					compact('style') + am(array('cache' => false), $options)
				);
			}
			return $this->cache[$cache];
		}
		
		/**
		 * Convenience method for returning our Sass vendor library path.
		 * @return string
		 * @access protected
		 */
		protected function _sassPath() {
			return App::pluginPath('sassy') . 'vendors' . DS . 'sass' . DS;
		}
		
		/**
		 * Convenience method for determining if we should execute by randomly
		 * picking a number.
		 * @return boolean
		 * @access protected
		 */
		protected function _shouldRun() {
			return array_key_exists(
				$this->_recompileParameter(), 
				$this->controller->params['named']
			) or (mt_rand(1, 100) <= $this->_recompilePercentage());
		}
		
		/**
		 * Convenience method for retrieving the configuration for our recompile
		 * named parameter we should watch for.
		 * @return mixed
		 * @access protected
		 */
		protected function _recompileParameter() {
			if (!$this->_hasRecompileParameter()) {
				return self::RECOMPILE_PARAMETER_DEFAULT;
			} else {
				return Configure::read(self::RECOMPILE_PARAMETER);
			}
		}
		
		/**
		 * Convenience method for retrieving our recompile percentage from the
		 * configuration, if it's set, or falls back to our default.
		 * @return integer
		 * @access protected
		 */
		protected function _recompilePercentage() {
			if (!$this->_hasRecompilePercentage()) {
				return self::RECOMPILE_PERCENTAGE_DEFAULT;
			} else {
				return min(100, max(0, Configure::read(self::RECOMPILE_PERCENTAGE)));
			}
		}
		
		/**
		 * Convenience method for checking if we have a configuration setting
		 * for the RECOMPILE_PARAMETER value.
		 * @return boolean
		 * @access protected
		 */
		protected function _hasRecompileParameter() {
			return !is_null(Configure::read(self::RECOMPILE_PARAMETER));
		}
		
		/**
		 * Convenience method for checking if we have a configuration setting
		 * for the RECOMPILE_PERCENTAGE value.
		 * @return boolean
		 * @access protected
		 */
		protected function _hasRecompilePercentage() {
			return !is_null(Configure::read(self::RECOMPILE_PERCENTAGE));
		}
		
	}