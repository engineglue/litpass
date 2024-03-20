This project is a working example of how to incorporate the LitPass API. An authentication script is included in this example so you can easily drop it in and see how it works.

[When using the LitPass API, you are asked to include a link to the LitPass help page. A sample of this is included in the index.htm.]

Installation:
1. Place the project files within an Apache web server with PHP and sqlite.

```
sudo apt update
sudo apt install apache2 php sqlite3 php-sqlite3
sudo service apache2 start

cp * /var/www/litpass/
```

[Additional procedures are required for a production environment and they're not included here.]

2. Apache will need permission to write to the SQLite database:

```
chown -R www-data:www-data /var/www/litpass/databases/
```

3. Access index.htm in a web browser and sign up to create a user account in the database. (Under normal conditions, a verification link would be emailed to the user but in this case, it will be printed to the screen.)

4. Utilize the verification link to create your account, then logout and log back in to test the login script.

You can browse the data in the database using a SQLite database browser to see how the records are stored but I suppose you'll mainly just be interested in reading the code to see how this works. Let me know if you have questions.

Note that there are functions included with these scripts (such as a user verification process) but they're beyond the scope of this project and will need additional setup. Also note that within a non-ssl/tls dev environment, this example won't be able to hash anything but it will still demonstrate the process. You may also want to add a captcha to key forms. Again, this project is only an example of how to incorporate the LitPass API. ;)