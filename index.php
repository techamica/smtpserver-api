<?php
	require_once "./src/SmtpApiMailer.php";

	$smtp = new Smtp\SmtpApiMailer("YOUR_API_KEY");

	$smtp->setTo('test1@test.com');
	$smtp->setTo('test2@test.com');
	$smtp->setTo([
		'test3@test.com' => 'A Good Subscriber',
		'test4@test.com'
	]);

	$smtp->setFrom('info@test.com', 'A Good Tester');

	$smtp->setSubject('Test subject for a test mail');

	$smtp->setHeader([ 'List-Open'	=> '<https://www.google.com>' ]);
	$smtp->setHeader([
		'List-Unsubscribe'	=> '<https://www.google.com?source=email-client-unsubscribe-button>',
		'List-Job'	=> 'n5643859y3t983y275934yx8732yjf874yj3324d873',
	]);

	$smtp->setText('This is a test mail only');
	$smtp->setHtml('<p>This is a test mail only</p>');

	$smtp->addFile('ABSOLUTE_PATH_TO_\tenor.gif');
	$smtp->addFile([ 'ABSOLUTE_PATH_TO_\salsa.gif', 'ABSOLUTE_PATH_TO_\photo_2019-02-08_00-01-11.jpg' ]);

	$resp = $smtp->sendMail();

	echo "<pre>";
	print_r($resp);
?>