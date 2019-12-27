<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require __DIR__ . '/vendor/autoload.php';
$config_folder = __DIR__ . '/config';
$mail_folder = __DIR__ . '/mail';

function alert($message, $type = 'error')
{
    echo '<div class="alert alert-' . $type . '" role="alert">' . $message . '</div>';
}

if( !is_dir( $config_folder ) ){
    mkdir( $config_folder , 0755);
}

if( !is_dir( $mail_folder ) ){
    mkdir( $mail_folder , 0755);
}

$configs = glob( $config_folder . "/*.txt");
$mails = glob( $mail_folder . "/*.txt");

if( isset($_POST['spinner'])){
if(file_exists( $mail_folder . '/' . basename($_POST['spinner']))){
echo file_get_contents( $mail_folder . '/' . basename($_POST['spinner']) );
}
exit;
}
?>
  <!DOCTYPE html>
  <html>

  <head>
    <title>Contact Leads</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>

    <script type="text/javascript">
      function preg_quote(str, delimiter) {
        return (str + '').replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + (delimiter || '') + '-]', 'g'), '\\$&');
      }

      function spin(text) {
        var matches = text.match(/{[^<]+/gi);
        if (matches === null) {
          return text;
        }
        if (matches[0].indexOf('{') != -1) {
          matches[0] = matches[0].substr(matches[0].indexOf('{') + 1);
        }
        if (matches[0].indexOf('}') != -1) {
          matches[0] = matches[0].substr(0, matches[0].indexOf('}'));
        }
        var parts = matches[0].split('|');
        var t = preg_quote(matches[0]);
        e_v = new RegExp('{' + t + '}', 'g');
        text = text.replace(e_v, parts[Math.floor(Math.random() * parts.length)]);
        return spin(text);
      }

      function run() {
        let file = document.getElementById("email").value;
        let lead_name = document.getElementById("lead_name").value;
        let subject = document.getElementById("subject").value;
        subject = subject.replace(':name', lead_name);
        document.getElementById("subject").value = subject;

        $.ajax({
          method: "POST",
          url: "index.php",
          data: {
            spinner: file
          }
        }).done(function(msg) {
          let text = msg;
          text = spin(text);
          text = text.replace(':name', lead_name);
          document.getElementById("message").value = text;
        });
      }
    </script>
  </head>

  <body>
    <div class="container">
      <?php
if( isset($_POST['email']) && isset($_POST['smtp']) && !empty($_POST['smtp'])){
if(file_exists( $config_folder . '/' . basename($_POST['smtp']))){
$lines = file($config_folder . '/' . basename($_POST['smtp']), FILE_IGNORE_NEW_LINES);

foreach($lines as $line){
$line = explode(':', $line);
$smtp_config[$line[0]] = $line[1];
}

echo alert('SMTP: ' . $smtp_config['server'] . ' Username: '  . $smtp_config['username'], 'success' );

// Create the Transport
if( !isset($smtp_config['protocol'])){
$transport = (new Swift_SmtpTransport($smtp_config['server'], $smtp_config['port']))
->setUsername($smtp_config['username'])
->setPassword($smtp_config['password']);
} else {
$transport = (new Swift_SmtpTransport($smtp_config['server'], $smtp_config['port'], $smtp_config['protocol']))
->setUsername($smtp_config['username'])
->setPassword($smtp_config['password']);
}

if(!isset($smtp_config['email'])){
$smtp_config['email'] = $smtp_config['username'];
}

// Create the Mailer using your created Transport
$mailer = new Swift_Mailer($transport);
// Create a message
$message = (new Swift_Message('Wonderful Subject'))
->setFrom([$smtp_config['email'] => $smtp_config['name']])
->setTo([$_POST['lead_email'] => $_POST['lead_name']])
->setBody($_POST['message'])
;

// Send the message
$result = $mailer->send($message);
if( $result ){
echo alert("E-mail sent ", 'success');
} else {
echo alert("Error: $result ", 'danger');
}

} else {
echo alert("Can't find the file " . basename($_POST['smtp']));
}
}
?>
        <div class="card">
          <div class="card-header">
            Contact Leads
          </div>
          <div class="card-body">
            <form method="POST" action="index.php">
              <div class="row">
                <div class="col">
                  <div class="form-group">
                    <label for="lead_email">Email (Lead)</label>
                    <input type="email" name="lead_email" class="form-control" id="lead_email" aria-describedby="emailHelp" placeholder="Enter email" required="required">
                  </div>
                </div>
                <div class="col">
                  <div class="form-group">
                    <label for="lead_name">Name (Lead)</label>
                    <input type="text" name="lead_name" class="form-control" id="lead_name" placeholder="Name (lead)" required="required">
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label for="smtp">SMTP Config</label>
                <select class="form-control" name="smtp" id="smtp" required="required">
                  <option></option>
                  <?php
					foreach ($configs as $key => $file) {
					echo '<option value="'  . basename($file)  . '">'  . basename($file)  . '</option>';
					}
					?>
                </select>
              </div>
              <div class="row">
                <div class="col">
                  <div class="form-group">
                    <label for="email">Mail</label>
                    <select class="form-control" name="email" id="email" required="required">
                      <option></option>
                      <?php
						foreach ($mails as $key => $file) {
						echo '<option value="'  . basename($file)  . '">'  . basename($file)  . '</option>';
						}
						?>
                    </select>
                  </div>
                </div>
                <div class="col">
                  <div class="form-group">
                    <label for="spin">Spin the Text</label>
                    <input name="spin" type="button" onclick="run()" class="btn btn-info form-control" value="Spin">
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label for="subject">Subject</label>
                <input type="email" name="subject" class="form-control" id="subject" placeholder="Enter a subject" required="required">
              </div>
              <div class="form-group">
                <label for="email">Message</label>
                <textarea id="message" name="message" class="form-control" rows="15"></textarea>
              </div>
              <button type="submit" class="btn btn-primary">Submit E-mail</button>
            </form>
          </div>
        </div>
    </div>
  </body>
  </html>