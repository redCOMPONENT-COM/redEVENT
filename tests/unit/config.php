<?php
class JTestConfig
{
	public $dbtype		= 'mysqli';
	public $host		= 'localhost';
	public $user		= 'root';
	public $password	= 'ju';
	public $db			= 'jl25_test_unit';
	public $dbprefix	= 'jos_';
	public $ftp_host	= '127.0.0.1';
	public $ftp_port	= '21';
	public $ftp_user	= '';
	public $ftp_pass	= '';
	public $ftp_root	= '';
	public $ftp_enable	= 0;
	public $log_path = '/opt/lampp/htdocs/jl25_test/logs';
	public $tmp_path = '/opt/lampp/htdocs/jl25_test/tmp';
	public $mailer		= 'mail';
	public $mailfrom = 'julv@free.fr';
	public $fromname = 'jl25 test';
	public $sendmail	= '/usr/sbin/sendmail';
	public $smtpauth	= '0';
	public $smtpsecure = 'none';
	public $smtpport	= '25';
	public $smtpuser	= '';
	public $smtppass	= '';
	public $smtphost	= 'localhost';
	public $debug		= 0;
	public $caching		= '0';
	public $cachetime	= '900';
	public $language	= 'en-GB';
	public $secret		= null;
	public $editor		= 'none';
	public $offset		= 0;
	public $lifetime	= 15;
	public $joomla_basepath	= '/opt/lampp/htdocs/jl25_test';
}