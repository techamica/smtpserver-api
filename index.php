<?php
	require_once "./src/SmtpApiMailer.php";

	$smtp = new Smtp\SmtpApiMailer("h42789345jn3462h523b7xgrdnrb34812rsb23h8744jt5mxgtn3478thn2187g2m7r9x1grb681dfg681237gnz2387gn8b");

	// $smtp->setTo('is@woano.com');
	// $smtp->setTo('ab@woano.com');
	$smtp->setTo([
		// 'niklavs.b@gmail.com' => 'Niklavs Birins',
		'is@woano.com' => 'Indrajit Sengupta',
		// 'ms@woano.com'
	]);

	$smtp->setFrom('info@sendingmail.xyz', 'Alex Cooper');

	$smtp->setSubject('Test subject for a test mail');

	$smtp->setHeader([ 'List-Open'	=> '<https://www.google.com>' ]);
	$smtp->setHeader([
		'List-Unsubscribe'	=> '<https://www.google.com?source=email-client-unsubscribe-button>',
		'List-Job'	=> 'n5643859y3t983y275934yx8732yjf874yj3324d873',
	]);

	$smtp->setText('This is a test mail only');
	$smtp->setHtml('<p>This is a test mail only</p>');

	// $smtp->addFile('C:\Users\isg\Pictures\tenor.gif');
	// $smtp->addFile([ 'C:\Users\isg\Pictures\tenor.gif', 'C:\Users\isg\Pictures\photo_2019-02-08_00-01-11.jpg' ]);

	$resp = $smtp->sendMail();

	echo "<pre>";
	print_r($resp);
?>