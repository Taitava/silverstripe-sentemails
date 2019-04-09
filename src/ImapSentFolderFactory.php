<?php


namespace Taitava\SentEmails;


use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Factory;
use Swift_Mime_Message;
use Taitava\ImapSentFolder\ImapSentFolderPlugin;

class ImapSentFolderFactory implements Factory
{
	use Configurable;
	use Extensible;
	
	/**
	 * This should be an array with the following structure:
	 * ```
	 * [
	 *      'email.address@somedomain.tld' => [
	 *              'host' => 'imap.somedomain.tld',
	 *              'port' => 993,
	 *              'username' => 'email.address',
	 *              'password' => 'verysecretdonotsharepubliclyintheinternet',
	 * 		'sent_folder' => 'Sent',
	 *      ],
	 *      'default' => [
	 *              'host' => 'imap.somedomain.tld',
	 *              'port' => 993,
	 *              'username' => 'other.account',
	 *              'password' => 'verysecretdonotsharepubliclyintheinternet',
	 * 		'sent_folder' => 'Sent',
	 *      ],
	 * ]
	 * ```
	 *
	 * The plugin will store the sent email messages to an IMAP account whose email address matches the email message's
	 * 'From' address (the sender). If the array does not have that particular 'From' address defined, the plugin
	 * will use the account defined with the key 'default'.
	 *
	 */
	private static $mailboxes = [];
	
	/**
	 * Creates a new service instance.
	 *
	 * @param string $service IGNORED
	 * @param array $params IGNORED
	 * @return ImapSentFolderPlugin
	 */
	public function create($service, array $params = array())
	{
		$this->include_plugin_classes();
		$mailboxes = (array) static::config()->mailboxes;
		$sent_folder_plugin = new ImapSentFolderPlugin($mailboxes);
		$sent_folder_plugin->setCallBeforeSaving($this->callback_before_saving());
		return $sent_folder_plugin;
	}
	
	/**
	 * SilverStripe doeesn't autoload taitava/swiftmailer-imapsentfolder because it doesn't contain a _config folder nor
	 * a _config.php file. I don't know a better way to include these PHP classes, so do it just with the basic require_once
	 * command.
	 */
	private function include_plugin_classes()
	{
		require_once Director::getAbsFile('vendor/taitava/swiftmailer-imapsentfolder/src/ImapException.php');
		require_once Director::getAbsFile('vendor/taitava/swiftmailer-imapsentfolder/src/ImapSentFolderPlugin.php');
	}
	
	private function callback_before_saving()
	{
		return function (Swift_Mime_Message $email_message)
		{
			$results = static::extend('onBeforeSaveEmailMessage', $email_message);
			foreach ($results as $result)
			{
				if ($result === false) return false; // Cancel saving
			}
			return true; // Allow saving
		};
	}
}
