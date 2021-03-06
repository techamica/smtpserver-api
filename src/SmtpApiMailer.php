<?php
	namespace Smtp;

	use \Exception;
	use \CURLFile;

	/**
	* SmtpApiMailer.php
	*
	* Official API library for smtpserver.com
	*
	* @author     Techamica <is@woano.com>
	* @copyright  2021 Techamica
	* @license    http://www.php.net/license/3_0.txt  PHP License 3.0
	* @version    1.0.2
	* @link       https://github.com/techamica/smtpserver-api
	*/
	class SmtpApiMailer {
		const max_upload_size = 26214400;
		const api_key_length = 96;
		/**
		* Declare all private data-members
		*
		* @access private
		*/
		private $target_url = "https://api.smtpserver.com/mailer/send";
		private $timeout = 20;
		private $api_key = null;
		private $to = [];
		private $from = null;
		private $from_name = '';
		private $header = [];
		private $subject = null;
		private $body = [ 'text' => null, 'html' => null ];
		private $files = [];
		private $mail_data = [];
		private $total_size = 0;

		/**
		* Initiate object
		*
		* @method 	constructor
		* @access public
		* @param 	$api_key <client's 96 character API KEY>
		*/
		public function __construct($api_key) {
			$this->api_key = $api_key;
		}

		/**
		* Set timeout
		*
		* @method 	setTimeout
		* @access public
		* @param 	$timeout <timeout in seconds>
		* @return 	$this
		*/
		public function setTimeout($timeout) {
			$this->timeout = $timeout>120 ? 120 : $timeout;

			return $this;
		}

		/**
		* Set To emails
		*
		* @method 	setTo
		* @access public
		* @param 	$to_list <an Email or array of Emails or array of Emails & Names>
		* @return 	$this
		*/
		public function setTo($to_list) {
			if(is_array($to_list)) {
				if(sizeof($to_list)===0)
					throw new Exception("To doesn't contain a single Email", 1);
				else {
					foreach($to_list as $key=>$val) {
						if(is_string($key)) {
							if(is_string($val)) {
								if(filter_var($key, FILTER_VALIDATE_EMAIL))
									$this->to[$key] = trim($val);
								else
									throw new Exception($key." is not a proper Email", 1);
							}
							else
								throw new Exception($key." name is not a proper string", 1);
						}
						else {
							if(is_string($val)) {
								if(filter_var($val, FILTER_VALIDATE_EMAIL))
									$this->to[$val] = '';
								else
									throw new Exception($val." is not a proper Email", 1);
							}
							else
								throw new Exception($key." name is not a proper string", 1);
						}
					}
				}
			}
			elseif(is_string($to_list)) {
				if(filter_var($to_list, FILTER_VALIDATE_EMAIL))
					$this->to[$to_list] = '';
				else
					throw new Exception($to_list." is not a proper Email", 1);
			}
			else
				throw new Exception("To must be an Email or array of Emails or array of Emails & Names", 1);

			return $this;
		}

		/**
		* Set From email
		*
		* @method 	setFrom
		* @access public
		* @param 	$from_mail <from email>
		* @param 	$from_name <from name> #OPTIONAL
		* @return 	$this
		*/
		public function setFrom($from_mail, $from_name='') {
			if(is_string($from_mail)) {
				if(filter_var($from_mail, FILTER_VALIDATE_EMAIL))
					$this->from = $from_mail;

				if(is_string($from_name))
					$this->from_name = trim($from_name);
				else
					throw new Exception("From name must be a string", 1);
			}
			else
				throw new Exception("From mail must be a string containing a proper Email", 1);

			return $this;
		}

		/**
		* Set Headers
		*
		* @method 	setHeader
		* @access public
		* @param 	$header_list <an array of Header names & data>
		* @return 	$this
		*/
		public function setHeader($header_list) {
			if(is_array($header_list)) {
				if(sizeof($header_list)===0)
					throw new Exception("Header doesn't contain a single data", 1);
				else {
					foreach($header_list as $key=>$val) {
						if(is_string($key)) {
							if(trim($key)!=='') {
								if(is_string($val)) {
									if(trim($val)!=='')
										$this->header[$key] = trim($val);
									else
										throw new Exception("Header".$key." has empty value", 1);
								}
								else
									throw new Exception($key." value is not a proper string", 1);
							}
							else
								throw new Exception("One or more header has empty key", 1);
						}
						else
							throw new Exception("One or more fields in Header is not a proper string", 1);
					}
				}
			}
			else
				throw new Exception("Header must be an array of Header names & data", 1);

			return $this;
		}

		/**
		* Set Subject
		*
		* @method 	setSubject
		* @access public
		* @param 	$subject <subject>
		* @return 	$this
		*/
		public function setSubject($subject) {
			if(is_string($subject)) {
				if(trim($subject)!=='')
					$this->subject = trim($subject);
				else
					throw new Exception("Subject cannot be empty string or whitespaces", 1);
			}
			else
				throw new Exception("Subject must be a proper string", 1);

			return $this;
		}

		/**
		* Set Text version of email content
		*
		* @method 	setText
		* @access public
		* @param 	$text <text version of HTML content>
		* @return 	$this
		*/
		public function setText($text) {
			if(is_string($text)) {
				if(trim($text)!=='')
					$this->body['text'] = trim($text);
				else
					throw new Exception("Text cannot be empty string or whitespaces", 1);
			}
			else
				throw new Exception("Text must be a proper string", 1);

			return $this;
		}

		/**
		* Set HTML email content
		*
		* @method 	setHtml
		* @access public
		* @param 	$html <HTML email content>
		* @return 	$this
		*/
		public function setHtml($html) {
			if(is_string($html)) {
				if(trim($html)!=='')
					$this->body['html'] = trim($html);
				else
					throw new Exception("Html cannot be empty string or whitespaces", 1);
			}
			else
				throw new Exception("Html must be a proper string", 1);

			return $this;
		}

		/**
		* Add file attachments to mail
		*
		* @method 	addFile
		* @access public
		* @param 	$files <a single file path or array of files with absolute path>
		* @return 	$this
		*/
		public function addFile($files) {
			if(is_array($files)) {
				foreach($files as $fileName) {
					if(is_string($fileName)) {
						if(file_exists($fileName)) {
							$finfo = finfo_open(FILEINFO_MIME_TYPE);
							$finfo = finfo_file($finfo, $fileName);

							$this->total_size += filesize($fileName);

							$this->files[] = new CURLFile($fileName, $finfo, basename($fileName));
						}
						else
							throw new Exception($fileName." not found", 1);
					}
					else
						throw new Exception("One or more file paths is not proper string", 1);
				}
			}
			elseif(is_string($files)) {
				if(file_exists($files)) {
					$finfo = finfo_open(FILEINFO_MIME_TYPE);
					$finfo = finfo_file($finfo, $files);

					$this->total_size += filesize($files);

					$this->files[] = new CURLFile($files, $finfo, basename($files));
				}
				else
					throw new Exception($files." not found", 1);
			}
			else
				throw new Exception("File must be a single file path or array of files with absolute path", 1);

			return $this;
		}

		/**
		* Prepare mail sending for API
		*
		* @method 	prepare
		* @access private
		* @return 	$this
		*/
		private function prepare() {
			/* S T A R T: check if API KEY is valid */
			if($this->api_key===null)
				throw new Exception("API KEY must be a proper string", 1);
			elseif(is_string($this->api_key)) {
				$this->api_key = trim($this->api_key);

				if($this->api_key==='')
					throw new Exception("API KEY cannot be empty or whitespaces", 1);
				elseif(strlen($this->api_key)!==self::api_key_length)
					throw new Exception("API KEY is invalid", 1);
			}
			else
				throw new Exception("API KEY must be a proper string", 1);
			/* E N D: check if API KEY is valid */

			// validate if to mails exist
			if(sizeof($this->to)===0)
				throw new Exception("A To email must be set in order to send mail", 1);
			else
				$this->mail_data['to'] = json_encode($this->to);

			// validate from mail
			if($this->from===null)
				throw new Exception("A From email must be set in order to send mail", 1);
			else
				$this->mail_data['from'] = $this->from;

			// set from name
			if($this->from_name!=='')
				$this->mail_data['from_name'] = $this->from_name;

			// set headers
			if(sizeof($this->header)!==0)
				$this->mail_data['headers'] = json_encode($this->header);

			// set subject
			if($this->subject!==null)
				$this->mail_data['subject'] = $this->subject;

			/* S T A R T: check if a valid Text/HTML was provided */
			if($this->body['text']===null && $this->body['html']===null)
				throw new Exception("Either of Text & HTML is mandatory", 1);
			else {
				if($this->body['text']!==null)
					$this->mail_data['text'] = $this->body['text'];
				if($this->body['html']!==null)
					$this->mail_data['html'] = $this->body['html'];
			}
			/* E N D: check if a valid Text/HTML was provided */

			if($this->total_size>self::max_upload_size)
				throw new Exception("Maximum upload size of ".self::max_upload_size." Bytes exceeded", 1);

			return $this;
		}

		/**
		* Push mail via API
		*
		* @method 	send
		* @access private
		* @return 	JSON
		*/
		private function send() {
			try {
				$cURL = curl_init($this->target_url);
				curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($cURL, CURLOPT_TIMEOUT, $this->timeout);
				curl_setopt($cURL, CURLOPT_HEADER, true);

				curl_setopt($cURL, CURLOPT_HTTPHEADER,
				    array(
				        'Content-Type: multipart/form-data',
				        'App-Key: '.$this->api_key
				    )
				);
				curl_setopt($cURL, CURLOPT_POST, true);
				curl_setopt($cURL, CURLOPT_POSTFIELDS, array_merge($this->mail_data, $this->files));

				$response = curl_exec($cURL);
				$httpcode = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
				$header_size = curl_getinfo($cURL, CURLINFO_HEADER_SIZE);
				$header = substr($response, 0, $header_size);
				$body = substr($response, $header_size);
				curl_close($cURL);

				return [ 'code' => $httpcode, 'header' => $header, 'body' => $body ];
			}
			catch(Exception $e) {
				throw $e;
			}
		}

		/**
		* Trigger mail sending
		*
		* @method 	sendMail
		* @access public
		* @return 	JSON
		*/
		public function sendMail() {
			return $this->prepare()->send();
		}
	}
?>