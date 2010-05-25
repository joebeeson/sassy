<?php

	// We'll need the helper for testing...
	App::import('Helper', 'Sassy.Monitor');

	/**
	 * MonitorHelperTest
	 * Tests the MonitorHelper of the Sassy plugin
	 * @author Joe Beeson <jbeeson@gmail.com>
	 */
	class MonitorHelperText extends CakeTestCase {

		/**
		 * Executed at the start of each test
		 * @return null
		 * @access public
		 */
		public function startTest() {
			$this->Helper = new MonitorHelperProxy();
		}

		/**
		 * Executed at the end of each test.
		 * @return null
		 * @access public
		 */
		public function endTest() {
			unset($this->Helper);
			ClassRegistry::flush();
		}

		/**
		 * Tests the _getMonitorFolders method of the helper
		 * @return null
		 * @access public
		 */
		public function testGetMonitorFolders() {
			if (!is_null(Configure::read(MonitorHelper::RECOMPILE_FOLDERS))) {
				Configure::delete(MonitorHelper::RECOMPILE_FOLDERS);
			}

			// We default to the CSS folder if nothing is set...
			$this->assertIdentical(
				$this->Helper->_getMonitorFolders(),
				array(
					CSS => CSS
				)
			);

			// Lets write some new folders to the configuration...
			Configure::write(MonitorHelper::RECOMPILE_FOLDERS, array(
				'/from/folder' => '/to/folder'
			));

			// ... confirm it worked correctly...
			$this->assertIdentical(
				$this->Helper->_getMonitorFolders(),
				array(
					// Notice how we added the DS to it
					'/from/folder' . DS => '/to/folder' . DS
				)
			);

		}

		/**
		 * Tests the _recompilePercentage method of the helper
		 * @return null
		 * @access public
		 */
		public function testRecompilePercentage() {
			if (!is_null(Configure::read('Sassy.Recompile.Percentage'))) {
				// We don't want this set, lets remove it...
				Configure::delete('Sassy.Recompile.Percentage');
			}

			// This should default to the helper constant
			$this->assertIdentical(
				$this->Helper->_recompilePercentage(),
				MonitorHelper::RECOMPILE_PERCENTAGE_DEFAULT
			);

			// Now lets set it and make sure it changes...
			Configure::write('Sassy.Recompile.Percentage', 11);
			$this->assertIdentical(
				$this->Helper->_recompilePercentage(),
				11
			);

		}

		/**
		 * Tests the _recompileParameter method of the helper
		 * @return null
		 * @access public
		 */
		public function testRecompileParameter() {
			if (!is_null(Configure::read('Sassy.Recompile.Parameter'))) {
				// We don't want this set, lets remove it...
				Configure::delete('Sassy.Recompile.Parameter');
			}

			// This should default to the helper constant
			$this->assertIdentical(
				$this->Helper->_recompileParameter(),
				MonitorHelper::RECOMPILE_PARAMETER_DEFAULT
			);

			// Now lets set it and make sure it changes...
			Configure::write('Sassy.Recompile.Parameter', 'testing');
			$this->assertIdentical(
				$this->Helper->_recompileParameter(),
				'testing'
			);

		}

		/**
		 * Tests the _hasRecompilePercentage method of the helper
		 * @return null
		 * @access public
		 */
		public function testHasRecompilePercentage() {
			if (!is_null(Configure::read('Sassy.Recompile.Percentage'))) {
				// We don't want this set, lets remove it...
				Configure::delete('Sassy.Recompile.Percentage');
			}

			// There should be no recompile percentage set
			$this->assertFalse($this->Helper->_hasRecompilePercentage());

			// Now lets set it...
			Configure::write('Sassy.Recompile.Percentage', 10);

			// ... now lets confirm this works correctly...
			$this->assertTrue($this->Helper->_hasRecompilePercentage());
		}

		/**
		 * Tests the _hasRecompileParameter method of the helper
		 * @return null
		 * @access public
		 */
		public function testHasRecompileParameter() {
			if (!is_null(Configure::read('Sassy.Recompile.Parameter'))) {
				// We don't want this set, lets remove it...
				Configure::delete('Sassy.Recompile.Parameter');
			}

			// There should be no recompile parameter set
			$this->assertFalse($this->Helper->_hasRecompileParameter());

			// Now lets set it...
			Configure::write('Sassy.Recompile.Parameter', 'sassy');

			// ... now lets confirm this works correctly...
			$this->assertTrue($this->Helper->_hasRecompileParameter());
		}


	}

	/**
	 * MonitorHelperProxy
	 * Acts as a proxy between our test and the MonitorHelper to provide access
	 * to various protected methods and variables.
	 * @author Joe Beeson <jbeeson@gmail.com>
	 */
	class MonitorHelperProxy extends MonitorHelper {

		/**
		 * Catches failed method calls and "reroutes" them to the class. This
		 * will allow us to get around the protected declarations.
		 * @param string $method
		 * @param array $arguments
		 * @return mixed
		 * @access public
		 */
		public function __call($method, $arguments) {
			if (method_exists($this, $method)) {
				return call_user_func_array(
					array($this, $method),
					$arguments
				);
			}
		}

	}
