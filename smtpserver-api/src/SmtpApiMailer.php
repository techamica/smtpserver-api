<?php
	namespace Smtp;

	use \Exception;
	use \CURLFile;

	class SmtpApiMailer {
		private $target_url = "https://api.smtpserver.com/mailer/send";
		private $timeout = 10;
		private $api_key = null;
		private $to = [];
		private $from = null;
		private $from_name = '';
		private $header = [];
		private $subject = null;
		private $body = [ 'text' => null, 'html' => null ];
		private $files = [];
		public $mail_data = [];

		public function __construct($api_key) {
			$this->api_key = $api_key;
		}

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
							if(filter_var($val, FILTER_VALIDATE_EMAIL))
								$this->to[$val] = '';
							else
								throw new Exception($val." is not a proper Email", 1);
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

		public function setHeader($header_list) {
			if(is_array($header_list)) {
				if(sizeof($header_list)===0)
					throw new Exception("Header doesn't contain a single data", 1);
				else {
					foreach($header_list as $key=>$val) {
						if(is_string($key)) {
							if(is_string($val)) {
								if(trim($val)!=='')
									$this->header[$key] = trim($val);
								else
									throw new Exception("Header".$key." hasempty value", 1);
									
							}
							else
								throw new Exception($key." value is not a proper string", 1);
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

		public function addFile($files) {
			if(is_array($files)) {
				foreach($files as $fileName) {
					if(is_string($fileName)) {
						if(file_exists($fileName)) {
							$finfo = finfo_open(FILEINFO_MIME_TYPE);
							$finfo = finfo_file($finfo, $fileName);

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

					$this->files[] = new CURLFile($files, $finfo, basename($files));
				}
				else
					throw new Exception($files." not found", 1);
			}
			else
				throw new Exception("File must be a single file path or array of files with absolute path", 1);

			return $this;
		}

		public function prepare() {
			/* S T A R T: check if API KEY is valid */
			if($this->api_key===null)
				throw new Exception("API KEY must be a proper  string", 1);
			elseif(is_string($this->api_key)) {
				$this->api_key = trim($this->api_key);

				if($this->api_key==='')
					throw new Exception("API KEY cannot be empty or whitespaces", 1);
				elseif(strlen($this->api_key)!==96)
					throw new Exception("API KEY is invalid", 1);
			}
			else
				throw new Exception("API KEY must be a proper  string", 1);
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

			return $this;
		}

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
				$header_size = curl_getinfo($cURL, CURLINFO_HEADER_SIZE);
				$header = substr($response, 0, $header_size);
				$body = substr($response, $header_size);
				curl_close($cURL);

				return json_decode($body, true);
			}
			catch(Exception $e) {
				throw $e;
			}
		}

		public function sendMail() {
			return $this->prepare()->send();
		}
	}
?>