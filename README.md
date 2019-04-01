# taitava/silverstripe-sentemails

This is a module that wraps around [taitava/swiftmailer-imapsentfolder](https://github.com/Taitava/swiftmail-imapsentfolder) to provide an ability to store any email messages sent from your SilverStripe website/application to an IMAP email server.

It's quite simple and lightweight.

## Requirements
- SilverStripe framework version 4
- PHP imap extension
- SwiftMailer must be used as the mailer (SilverStripe uses this by default)
- **An IMAP email account.** No other protocols are supported.

## Installation
`composer require taitava/silverstripe-sentemails`

## Usage

Create a new file `app\_config\sentemails.yml`:

```yaml
Taitava\SentEmails\ImapSentFolderFactory:
  mailboxes:
    default:
      host: imap.somedomain.tld
      port: 993
      sent_folder: Sent
      username: 'email.address'
      password: 'verysecretdonotsharepubliclyintheinternet'
    'email.address@somedomain.tld':
      host: imap.somedomain.tld
      port: 993
      sent_folder: Sent
      username: 'email.address'
      password: 'verysecretdonotsharepubliclyintheinternet'
```

Then run `dev/build?flush=all` in your browser. Done! All emails that your application sends will now be saved!

The module will automatically pick the correct mailbox by inspecting the 'From' field from the email message that was sent. If the email address is not found from the `mailboxes` configuration, the module will use the mailbox defined with the key 'default'. You should always define a default mailbox if its possible that mail is sent from unforeseen email addresses!

## Control what gets saved and what not

Sometimes you want to examine the sent email messages and make a decision whether it should be saved or not. There is a flexible way to do this by creating a subclass of the SilverStripe's `Extension` class:

```php

use SilverStripe\Core\Extension;

class MyImapSentFolderFactoryExtension extends Extension
{
        public function onBeforeSaveEmailMessage(Swift_Mime_Message $email_message)
        {
                // ... Inspect the $email_message instance ...

                // ... Decide not to save this message ...
                return false;

                // ... Decide to accept saving the message ...
                return true;

                // ... If you do not write a 'return' statement or if you return null, saving is also accepted ...
                return;
        }
}
```

Now we just need to enable our new extension. Append this to `app\_config\sentemails.yml`:

```yaml
Taitava\SentEmails\ImapSentFolderFactory:
  extensions:
    - MyImapSentFolderFactoryExtension
```

**Pro tip**: As far as I know, there is no extension hook in SilverStripe's `Email` or `SwiftMailer` classes that could be easily used to trigger code *after sending an email*. (Usually you are required to write your own plugin for SwiftMail). If you need this kind of extension hook for whatever reason, you can use an extension like the one described here. Just return `false` whenever you don't need the sent message to be saved.

## Future

Ideas (and pull requests) are welcome :). No big plans at the moment, I'm considering this plugin quite complete. But will try to fix issues if any arise.

## Author

Oh, it's just me. Too lazy to write my name. :)

## License

MIT
