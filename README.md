# Contact Leads
Small script to contact Clients with text spinner
This script is written to use in localhost.

# How to use
First clone the directory

Then run composer `composer install`

<strong>Then visit the index.php page once. You will have 2 new folders.
One called config where you gonna put your .txt files with different smtp configurations. Example of file content:</strong><br /><br />
server:server.server.net<br />
email:test@test.pt<br />
name:Name Name<br />
username:test@test.pt<br />
password:password<br />
port:465<br />
protocol:ssl<br />

<br />
Other folder called mail where you gonna put .txt files with e-mails to spin.<br />
Example of a file:<br /><br />
{Hello|Hi} :name,<br />
{I want to|I Would like to} {talk|have a conversation} with you.<br />
{Thank you|Thank You very much}<br />
<br />
#Extra:
You can use the code :name inside the e-mail or the subject and when you click
spin will replace that :name with the lead name.