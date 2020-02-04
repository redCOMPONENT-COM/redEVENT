<?php
	/**
	 * This is project's console commands configuration for Robo task runner.
	 *
	 * Download robo.phar from http://robo.li/robo.phar and type in the root of the repo: $ php robo.phar
	 * Or do: $ composer update, and afterwards you will be able to execute robo like $ php vendor/bin/robo
	 *
	 * @see  http://robo.li/
	 */
	require_once 'vendor/autoload.php';

	/**
	 * Class RoboFile
	 *
	 * @since  1.6.14
	 */
	class RoboFile extends \Robo\Tasks
	{
		// Load tasks from composer, see composer.json
		use Joomla\Testing\Robo\Tasks\LoadTasks;
		// Load tasks from composer, see composer.json
		use Joomla\Testing\Robo\Tasks\LoadTasks;

		/**
		 * Current root folder
		 */
		private $testsFolder = './';

		/**
		 * @var   array
		 * @var   array
		 * @since 5.6.0
		 */
		private $defaultArgs = [
			'--tap',
			'--fail-fast'
		];

		/**
		 * Hello World example task.
		 *
		 * @see  https://github.com/redCOMPONENT-COM/robo/blob/master/src/HelloWorld.php
		 * @link https://packagist.org/packages/redcomponent/robo
		 *
		 * @return object Result
		 */
		public function sayHelloWorld()
		{
			$result = $this->taskHelloWorld()->run();

			return $result;
		}

		/**
		 * Sends Codeception errors to Slack
		 *
		 * @param   string  $slackChannel             The Slack Channel ID
		 * @param   string  $slackToken               Your Slack authentication token.
		 * @param   string  $codeceptionOutputFolder  Optional. By default tests/_output
		 *
		 * @return mixed
		 */
		public function sendCodeceptionOutputToSlack($slackChannel, $slackToken = null, $codeceptionOutputFolder = null)
		{
			if (is_null($slackToken))
			{
				$this->say('we are in Travis environment, getting token from ENV');

				// Remind to set the token in repo Travis settings,
				// see: http://docs.travis-ci.com/user/environment-variables/#Using-Settings
				$slackToken = getenv('SLACK_ENCRYPTED_TOKEN');
			}

			if (is_null($codeceptionOutputFolder))
			{
				$this->codeceptionOutputFolder = '_output';
			}

			$this->say($codeceptionOutputFolder);

			$result = $this
				->taskSendCodeceptionOutputToSlack(
					$slackChannel,
					$slackToken,
					$codeceptionOutputFolder
				)
				->run();

			return $result;
		}

		/**
		 * Sends the build report error back to Slack
		 *
		 * @param   string  $cloudinaryName       Cloudinary cloud name
		 * @param   string  $cloudinaryApiKey     Cloudinary API key
		 * @param   string  $cloudinaryApiSecret  Cloudinary API secret
		 * @param   string  $githubRepository     GitHub repository (owner/repo)
		 * @param   string  $githubPRNo           GitHub PR #
		 * @param   string  $slackWebhook         Slack Webhook URL
		 * @param   string  $slackChannel         Slack channel
		 * @param   string  $buildURL             Build URL
		 *
		 * @return  void
		 *
		 * @since   5.1
		 */
		public function sendBuildReportErrorSlack($cloudinaryName, $cloudinaryApiKey, $cloudinaryApiSecret, $githubRepository, $githubPRNo, $slackWebhook, $slackChannel, $buildURL = '')
		{
			$directories = glob('_output/*' , GLOB_ONLYDIR);

			foreach ($directories as $directory)
			{
				$this->sendBuildReportErrorSlackDirectory($directory, $cloudinaryName, $cloudinaryApiKey, $cloudinaryApiSecret, $githubRepository, $githubPRNo, $slackWebhook, $slackChannel, $buildURL);
			}
		}

		/**
		 * Sends the build report error back to Slack
		 *
		 * @param   string  $directory            Directory to explore
		 * @param   string  $cloudinaryName       Cloudinary cloud name
		 * @param   string  $cloudinaryApiKey     Cloudinary API key
		 * @param   string  $cloudinaryApiSecret  Cloudinary API secret
		 * @param   string  $githubRepository     GitHub repository (owner/repo)
		 * @param   string  $githubPRNo           GitHub PR #
		 * @param   string  $slackWebhook         Slack Webhook URL
		 * @param   string  $slackChannel         Slack channel
		 * @param   string  $buildURL             Build URL
		 *
		 * @return  void
		 *
		 * @since   5.1
		 */
		public function sendBuildReportErrorSlackDirectory($directory, $cloudinaryName, $cloudinaryApiKey, $cloudinaryApiSecret, $githubRepository, $githubPRNo, $slackWebhook, $slackChannel, $buildURL = '')
		{
			$errorSelenium = true;
			$reportError = false;
			$reportFile = $directory . '/selenium.log';
			$errorLog = 'Selenium log in ' . $directory . ':' . chr(10). chr(10);
			$this->say('Starting to Prepare Build Report');
			$this->say('Exploring folder ' . $directory . ' for error reports');

			// Loop through Codeception snapshots
			if (file_exists($directory) && $handler = opendir($directory))
			{
				$reportFile = $directory . '/report.tap.log';
				$errorLog = 'Codeception tap log in ' . $directory . ':' . chr(10). chr(10);
				$errorSelenium = false;
			}

			if (file_exists($reportFile))
			{
				$this->say('Report File Prepared');
				if ($reportFile)
				{
					$errorLog .= file_get_contents($reportFile, null, null, 15);
				}

				if (!$errorSelenium)
				{
					$handler = opendir($directory);
					$errorImage = '';

					while (!$reportError && false !== ($errorSnapshot = readdir($handler)))
					{
						// Avoid sending system files or html files
						if (!('png' === pathinfo($errorSnapshot, PATHINFO_EXTENSION)))
						{
							continue;
						}

						$reportError = true;
						$errorImage = $directory . '/' . $errorSnapshot;
					}
				}

				if ($reportError || $errorSelenium)
				{
					// Sends the error report to Slack
					$this->say('Sending Error Report');
					$reportingTask = $this->taskReporting()
						->setCloudinaryCloudName($cloudinaryName)
						->setCloudinaryApiKey($cloudinaryApiKey)
						->setCloudinaryApiSecret($cloudinaryApiSecret)
						->setGithubRepo($githubRepository)
						->setGithubPR($githubPRNo)
						->setBuildURL($buildURL . 'display/redirect')
						->setSlackWebhook($slackWebhook)
						->setSlackChannel($slackChannel)
						->setTapLog($errorLog);

					if (!empty($errorImage))
					{
						$reportingTask->setImagesToUpload($errorImage)
							->publishCloudinaryImages();
					}

					$reportingTask->publishBuildReportToSlack()
						->run()
						->stopOnFail();
				}
			}
		}

		/**
		 * Downloads and prepares a Joomla CMS site for testing
		 *
		 * @return mixed
		 */
		public function prepareSiteForSystemTests()
		{
			// Get Joomla Clean Testing sites
			if (is_dir('joomla-cms'))
			{
				$this->taskDeleteDir('joomla-cms')->run();
			}

			$this->cloneJoomla();
		}

		/**
		 * Downloads and prepares a Joomla CMS site for testing
		 *
		 * @return mixed
		 */
		public function prepareSiteForUnitTests()
		{
			// Make sure we have joomla
			if (!is_dir('joomla-cms'))
			{
				$this->cloneJoomla();
			}

			if (!is_dir('joomla-cms/libraries/vendor/phpunit'))
			{
				$this->getComposer();
				$this->taskComposerInstall('../composer.phar')->dir('joomla-cms')->run();
			}

			// Copy extension. No need to install, as we don't use mysql db for unit tests
			$joomlaPath = __DIR__ . '/joomla-cms';
			$this->_exec("gulp copy --wwwDir=$joomlaPath --gulpfile ../build/gulpfile.js");
		}

        /**
         * Downloads and Install redCORE for Integration Testing testing
         *
         * @param   integer  $cleanUp  Clean up the directory when present (or skip the cloning process)
         *
         * @return  void
         * @since   1.0.0
         */
        protected function getredCOREExtensionForIntegrationTests($cleanUp = 1)
        {
            // Get redCORE Clean Testing sites
            if (is_dir('build/redFORM/build/redCORE'))
            {
                if (!$cleanUp)
                {
                    $this->say('Using cached version of redCORE and skipping clone process');

                    return;
                }

                $this->taskDeleteDir('build/redFORM/build/redCORE')->run();
            }

            $version = '1.10.6';
            $this->_exec("git clone -b $version --single-branch --depth 1 https://travisredweb:travisredweb2013github@github.com/redCOMPONENT-COM/redCORE.git build/redFORM/build/redCORE");

            $this->say("redCORE ($version) cloned at build/redFORM/build");
        }

		/**
		 * Downloads and Install redFORM for Integration Testing testing
		 *
		 * @param   integer  $cleanUp  Clean up the directory when present (or skip the cloning process)
		 *
		 * @return  void
		 * @since   1.0.0
		 */
		protected function getredFORMExtensionForIntegrationTests($cleanUp = 1)
		{
			// Get redFORM Clean Testing sites
			if (is_dir('build/redFORM'))
			{
				if (!$cleanUp)
				{
					$this->say('Using cached version of redFORM and skipping clone process');

					return;
				}

				$this->taskDeleteDir('build/redFORM')->run();
			}

			$version = '3.3.15';
			$this->_exec("git clone -b $version --single-branch --depth 1 https://travisredweb:travisredweb2013github@github.com/redCOMPONENT-COM/redFORM.git build/redFORM");

			$this->say("redFORM ($version) cloned at build/");
		}

		/**
		 * Executes Selenium System Tests in your machine
		 *
		 * @param   array  $options  Use -h to see available options
		 *
		 * @return mixed
		 */
		public function runTest($opts = [
			'test|t'	    => null,
			'suite|s'	    => 'acceptance'
		])
		{
			$this->getComposer();

			$this->taskComposerInstall()->run();

			$this->taskSeleniumStandaloneServer()
				->setURL("http://localhost:4444")
				->runSelenium()
				->waitForSelenium()
				->run()
				->stopOnFail();

			// Make sure to Run the Build Command to Generate AcceptanceTester
			$this->_exec("vendor/bin/codecept build");

			if (!$opts['test'])
			{
				$this->say('Available tests in the system:');

				$iterator = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator(
						$this->testsFolder . $opts['suite'],
						RecursiveDirectoryIterator::SKIP_DOTS),
					RecursiveIteratorIterator::SELF_FIRST);

				$tests = array();

				$iterator->rewind();
				$i = 1;

				while ($iterator->valid())
				{
					if (strripos($iterator->getSubPathName(), 'cept.php')
						|| strripos($iterator->getSubPathName(), 'cest.php'))
					{
						$this->say('[' . $i . '] ' . $iterator->getSubPathName());
						$tests[$i] = $iterator->getSubPathName();
						$i++;
					}

					$iterator->next();
				}

				$this->say('');
				$testNumber	= $this->ask('Type the number of the test  in the list that you want to run...');
				$opts['test'] = $tests[$testNumber];
			}

			$pathToTestFile = './' . $opts['suite'] . '/' . $opts['test'];

			// loading the class to display the methods in the class
			require './' . $opts['suite'] . '/' . $opts['test'];

			$classes = Nette\Reflection\AnnotationsParser::parsePhp(file_get_contents($pathToTestFile));
			$className = array_keys($classes)[0];

			// If test is Cest, give the option to execute individual methods
			if (strripos($className, 'cest'))
			{
				$testFile = new Nette\Reflection\ClassType($className);
				$testMethods = $testFile->getMethods(ReflectionMethod::IS_PUBLIC);

				foreach ($testMethods as $key => $method)
				{
					$this->say('[' . $key . '] ' . $method->name);
				}

				$this->say('');
				$methodNumber = $this->askDefault('Choose the method in the test to run (hit ENTER for All)', 'All');

				if($methodNumber != 'All')
				{
					$method = $testMethods[$methodNumber]->name;
					$pathToTestFile = $pathToTestFile . ':' . $method;
				}
			}

			$this->taskCodecept()
				->test($pathToTestFile)
				->arg('--steps')
				->arg('--debug')
				->arg('--fail-fast')
				->run()
				->stopOnFail();
		}

		/**
		 * Preparation for running manual tests after installing Joomla/Extension and some basic configuration
		 *
		 * @return void
		 */
		public function runTestPreparation()
		{
			$this->prepareSiteForSystemTests();

			$this->getComposer();

			$this->taskComposerInstall()->run();

			$this->taskSeleniumStandaloneServer()
				->setURL("http://localhost:4444")
				->runSelenium()
				->waitForSelenium()
				->run()
				->stopOnFail();

			// Make sure to Run the Build Command to Generate AcceptanceTester
			$this->_exec("vendor/bin/codecept build");

			$this->taskCodecept()
				->arg('--tap')
				->arg('--fail-fast')
				->arg($this->testsFolder . 'acceptance/install/')
				->run()
				->stopOnFail();
		}

		/**
		 * Function to Run tests in a Group
		 *
		 * @return void
		 */
		public function runTests()
		{
			$this->prepareSiteForSystemTests();

			$this->prepareReleasePackages();

			$this->getComposer();

			$this->taskComposerInstall()->run();

			$this->taskSeleniumStandaloneServer()
				->setURL("http://localhost:4444")
				->runSelenium()
				->waitForSelenium()
				->run()
				->stopOnFail();

			// Make sure to Run the Build Command to Generate AcceptanceTester
			$this->_exec("vendor/bin/codecept build");

			$this->taskCodecept()
				->arg('--tap')
				->arg('--fail-fast')
				->arg($this->testsFolder . 'acceptance/install/')
				->run()
				->stopOnFail();

			$this->taskCodecept()
				->arg('--tap')
				->arg('--fail-fast')
				->arg($this->testsFolder . 'acceptance/administrator/')
				->run()
				->stopOnFail();

//		$this->taskCodecept()
//			->arg('--steps')
//			->arg('--debug')
//			->arg('--tap')
//			->arg('--fail-fast')
//			->arg($this->testsFolder . 'acceptance/frontend/')
//			->run()
//			->stopOnFail();

			$this->taskCodecept()
				->arg('--tap')
				->arg('--fail-fast')
				->arg($this->testsFolder . 'acceptance/uninstall/')
				->run()
				->stopOnFail();

			$this->killSelenium();
		}

		/**
		 * Function to run unit tests
		 *
		 * @return void
		 */
		public function runUnitTests()
		{
			$this->prepareSiteForUnitTests();
			$this->_exec("joomla-cms/libraries/vendor/phpunit/phpunit/phpunit")
				->stopOnFail();
		}

		public function testsSitePreparation($use_htaccess = 1, $cleanUp = 1)
		{
			$skipCleanup = false;
			// Get Joomla Clean Testing sites
			if (is_dir('/joomla-cms'))
			{
				if (!$cleanUp)
				{
					$skipCleanup = true;
					$this->say('Using cached version of Joomla CMS and skipping clone process');
				}
				else
				{
					$this->taskDeleteDir('/joomla-cms')->run();
				}
			}
			if (!$skipCleanup)
			{
				$version = 'staging';
				/*
				* When joomla Staging branch has a bug you can uncomment the following line as a tmp fix for the tests layer.
				* Use as $version value the latest tagged stable version at: https://github.com/joomla/joomla-cms/releases
				*/
				$version = '3.9.15';
				$this->_exec("git clone -b $version --single-branch --depth 1 https://github.com/joomla/joomla-cms.git joomla-cms");
				$this->say("Joomla CMS ($version) site created at joomla-cms");
			}
			// Optionally uses Joomla default htaccess file
			if ($use_htaccess == 1)
			{
				$this->_copy('joomla-cms/htaccess.txt', 'joomla-cms/.htaccess');
				$this->_exec('sed -e "s,# RewriteBase /,RewriteBase /joomla-cms/,g" --in-place joomla-cms/.htaccess');
			}
		}

		/**
		 * Stops Selenium Standalone Server
		 *
		 * @return void
		 */
		public function killSelenium()
		{
			$this->_exec('curl http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer');
		}

		/**
		 * Downloads Composer
		 *
		 * @return void
		 */
		private function getComposer()
		{
			// Make sure we have Composer
			if (!file_exists('./composer.phar'))
			{
				$this->_exec('curl --retry 3 --retry-delay 5 -sS https://getcomposer.org/installer | php');
			}
		}

		/**
		 * Runs Selenium Standalone Server.
		 *
		 * @return void
		 */
		public function runSelenium()
		{
			$this->_exec("vendor/bin/selenium-server-standalone >> selenium.log 2>&1 &");
		}

		/**
		 * Prepares the .zip packages of the extension to be installed in Joomla
		 */
		public function prepareReleasePackages()
		{
			$this->_exec("gulp release --skip-version --testRelease --gulpfile ../build/gulpfile.js");
		}

		/**
		 * Looks for PHP Parse errors in core
		 */
		public function checkForParseErrors()
		{
			$this->_exec('php checkers/phppec.php ../component/ ../modules/ ../plugins/ ../redeventb2b/ ../redeventsync/');
		}

		/**
		 * Looks for missed debug code like var_dump or console.log
		 */
		public function checkForMissedDebugCode()
		{
			$this->_exec('php checkers/misseddebugcodechecker.php');
		}

		/**
		 * Check the code style of the project against a passed sniffers
		 */
		public function checkCodestyle()
		{
			if (!is_dir('checkers/phpcs/Joomla'))
			{
				$this->say('Downloading Joomla Coding Standards Sniffers');
				$this->_exec("git clone -b master --single-branch --depth 1 https://github.com/joomla/coding-standards.git checkers/phpcs/Joomla");
			}

			$this->taskExec('php checkers/phpcs.php')
				->printed(true)
				->run()
				->stopOnFail();
		}

		/**
		 * Sends the build report error back to Slack
		 *
		 * @param   string $cloudinaryName      Cloudinary cloud name
		 * @param   string $cloudinaryApiKey    Cloudinary API key
		 * @param   string $cloudinaryApiSecret Cloudinary API secret
		 * @param   string $githubRepository    GitHub repository (owner/repo)
		 * @param   string $githubPRNo          GitHub PR #
		 * @param   string $slackWebhook        Slack Webhook URL
		 * @param   string $slackChannel        Slack channel
		 * @param   string $buildURL            Build URL
		 *
		 * @return  void
		 *
		 * @since   5.1
		 */
		public function sendBuildReportErrorTravisToSlack($cloudinaryName, $cloudinaryApiKey, $cloudinaryApiSecret, $githubRepository, $githubPRNo, $slackWebhook, $slackChannel, $buildURL)
		{
			$errorSelenium = true;
			$reportError   = false;
			$reportFile    = 'tests/selenium.log';
			$errorLog      = 'Selenium log:' . chr(10) . chr(10);

			// Loop through Codeception snapshots
			if (file_exists('tests/_output') && $handler = opendir('tests/_output'))
			{
				$reportFile    = 'tests/_output/report.tap.log';
				$errorLog      = 'Codeception tap log:' . chr(10) . chr(10);
				$errorSelenium = false;
			}

			if (file_exists($reportFile))
			{
				if ($reportFile)
				{
					$errorLog .= file_get_contents($reportFile, null, null, 15);
				}

				if (!$errorSelenium)
				{
					$handler    = opendir('tests/_output');
					$errorImage = '';

					while (!$reportError && false !== ($errorSnapshot = readdir($handler)))
					{
						// Avoid sending system files or html files
						if (!('png' === pathinfo($errorSnapshot, PATHINFO_EXTENSION)))
						{
							continue;
						}

						$reportError = true;
						$errorImage  = __DIR__ . '/tests/_output/' . $errorSnapshot;
					}
				}

				echo $errorImage;

				if (!$reportError || $errorSelenium)
				{
					// Sends the error report to Slack
					$reportingTask = $this->taskReporting()
						->setCloudinaryCloudName($cloudinaryName)
						->setCloudinaryApiKey($cloudinaryApiKey)
						->setCloudinaryApiSecret($cloudinaryApiSecret)
						->setGithubRepo($githubRepository)
						->setGithubPR($githubPRNo)
						->setBuildURL($buildURL)
						->setSlackWebhook($slackWebhook)
						->setSlackChannel($slackChannel)
						->setTapLog($errorLog);

					if (!empty($errorImage))
					{
						$reportingTask->setImagesToUpload($errorImage)
							->publishCloudinaryImages();
					}

					$reportingTask->publishBuildReportToSlack()
						->run()
						->stopOnFail();
				}
			}
		}
		/**
		 * Clone joomla from official repo
		 *
		 * @return void
		 */
		private function cloneJoomla()
		{
			$version = 'staging';

			/*
			 * When joomla Staging branch has a bug you can uncomment the following line as a tmp fix for the tests layer.
			 * Use as $version value the latest tagged stable version at: https://github.com/joomla/joomla-cms/releases
			 */
			$version = '3.9.0';

			$this->_exec("git clone -b $version --single-branch --depth 1 https://github.com/joomla/joomla-cms.git joomla-cms");

			$this->say("Joomla CMS ($version) site created at joomla-cms");
		}

		/**
		 * Tests setup
		 *
		 * @param   boolean  $debug   Add debug to the parameters
		 * @param   boolean  $steps   Add steps to the parameters
		 *
		 * @return  void
		 * @since   5.6.0
		 */
		public function testsSetup($debug = true, $steps = true)
		{
			$args = [];

			if ($debug)
			{
				$args[] = '--debug';
			}

			if ($steps)
			{
				$args[] = '--steps';
			}

			$args = array_merge(
				$args,
				$this->defaultArgs
			);

            // Gets redFORM
            $this->getredCOREExtensionForIntegrationTests(0);

			// Gets redFORM
			$this->getredFORMExtensionForIntegrationTests(0);

			// Sets the output_append variable in case it's not yet
			if (getenv('output_append') === false)
			{
				$this->say('Setting output_append');
				putenv('output_append=');
			}

			// Builds codeception
			$this->_exec("vendor/bin/codecept build");

			// Executes the initial set up
			$this->taskCodecept()
				->args($args)
				->arg('acceptance/install/')
				->run()
				->stopOnFail();
		}

		/**
		 * Individual test folder execution
		 *
		 * @param   string   $folder  Folder to execute codecept run to
		 * @param   boolean  $debug   Add debug to the parameters
		 * @param   boolean  $steps   Add steps to the parameters
		 *
		 * @return  void
		 * @since   5.6.0
		 */
		public function testsRun($folder, $debug = true, $steps = true)
		{
			$args = [];

			if ($debug)
			{
				$args[] = '--debug';
			}

			if ($steps)
			{
				$args[] = '--steps';
			}

			$args = array_merge(
				$args,
				$this->defaultArgs
			);
			// Sets the output_append variable in case it's not yet
			if (getenv('output_append') === false)
			{
				putenv('output_append=');
			}

			// Codeception build
			$this->_exec("vendor/bin/codecept build");

			// Actual execution of Codeception test
			$this->taskCodecept()
				->args($args)
				->arg( $folder . '/')
				->run()
				->stopOnFail();
		}
	}
