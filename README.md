
![Logo](https://smtpserver.com/documentation/img/logo.png)


# smtpserver-api

API vendor in PHP for https://smtpserver.com. Mail sending is now super easy!




## License

[PHP License 3.0](https://www.php.net/license/3_0.txt)

![PHP_logo](https://upload.wikimedia.org/wikipedia/commons/thumb/2/27/PHP-logo.svg/1200px-PHP-logo.svg.png)


## Appendix

- [Installation](#installation)
- [Documentation](#documentation)
- [Screenshots](#screenshots)
- [Used By](#used-by)
## Installation

Install `techamica/smtpserver-api` with `composer`. This package is installable only with `composer version 2x` or higher.

```bash
  composer update
  composer require techamica/smtpserver-api
```
    
## Documentation

After installing `techamica/smtpserver-api` with `composer` open your `Controller script` (for `Laravel`) and use `Smtp\SmtpApiMailer` class just under your script `namespace`.

```bash
  use Smtp\SmtpApiMailer;
```
If you're using `Core PHP` then you need to include the `autoload.php` file into your script from the `vendor` directory. Next, use the `Smtp\SmtpApiMailer` class.

```bash
  require_once getcwd()."/vendor/autoload.php";

  use Smtp\SmtpApiMailer;
```
Next, create an object of `SmtpApiMailer` class. This will require you 96-character `API KEY`.

```bash
  $smtp = new SmtpApiMailer('YOUR_API_KEY');
```
Now set `To` mail. You can pass only one `String`(email), or an `Array` of `Strings`(emails) in order to send mail to multiple recipients at once.

```bash
  $smtp->setTo('test1@test.com')
        ->setTo([
          'test2@test.com',
          'test3@test.com'
        ]);
```
But, if you want to add recipients' names, you must pass an `Array` of `email ids` and `names`. Some of the `names` can be empty `String`, if you want.

```bash
  $smtp->setTo([
    'test3@test.com' => 'Good Recipient 1',
    'test4@test.com' => 'Good Recipient 2',
    'test5@test.com'
  ]);
```
`To` mail is mandatory in order to send mail.

Next, set `From` mail. Simply pass `from mail id` and `name` into the method. `name` is `optional`.

```bash
  $smtp->setFrom('info@test.com', 'Good Sender');
```
`From` mail is mandatory in order to send mail.

Next, set `Subject` of the mail. This is an `optional` step.

```bash
  $smtp->setSubject('Test subject for a test mail');
```
Next, set `Custom Headers`. This is also an `optional` step.

```bash
  $smtp->setHeader([
    'Custom-Header-1' => '<https://www.google.com>',
    'Custom-Header-2' => '<https://www.google.com?source=email-client-unsubscribe-button>',
    'Custom-Header-3' => 'ABCD-17G5-098H-F5TS-0865'
  ]);
```
Set `Attachments`, if there's any. You can attach just one file by passing its path as a `String` or you can pass multiple file paths in an `Array`. But remember to put `absolute path` to the files and the total size of attachments should not exceed `25MB`. This is an `optional`step.

```bash
  $smtp->addFile('ABSOLUTE_PATH_TO/web.zip')
        ->addFile([
          "ABSOLUTE_PATH_TO/photo_2019-02-08_00-01-11.jpg",
          "ABSOLUTE_PATH_TO/mongodb_ the definitive guide - kristina chodorow_1401.pdf"
        ]);
```
Set `timeout` for mail sending. Default timeout is `20sec`. Depending on the attachment size & internet connection this can be set to some other value. To set a new timeout, pass the value in `seconds`.

```bash
  $smtp->setTimeout(30);
```

Set `HTML` & `Text`. Either of these two is `mandatory`. It's a good practice to put both.

```bash
  $smtp->->setText('This is a test mail only');
  $smtp->setHtml('<p>This is a test mail only</p>');
```
Finally, send mail. This method either `returns` an `Array` or `throws Error` for any setup-related issue. So make sure to use a `try-catch` block enclosing this method.

```bash
  try {
    $response = $smtp->sendMail();
    print_r($response);
  } catch(\Exception $e) {
    -- YOUR CODE --
  }
```
Output `Array` consists of three keys: `code`, `header` & `body`. `code` is response code, `header` is response header-list & `body` consists of response-body in `JSON` format. `body` looks like:

```bash
  { success: 1, message: 'Mail accepted' }
```
If there's error while sending mail, the `body` looks like:

```bash
  { success: 0, message: 'SOME ERROR MESSAGE' }
```
## Screenshots

![CompleteCode Screenshot](https://smtpserver.com/documentation/img/php-complete-1.png)

## Used By

This project is used by the following company:

- [SMTP SERVER](https://smtpserver.com/)

