![Scan SQL Injection](https://www.tariqhawis.com/img/injectbot/injectbot-flag.png)

# About InjectBot

InjectBot is a web-based SQL injection scanning and exploiting tool. 

# Why InjectBot?

Unlike other SQLi tools out there, Injectbot designed with simplicity in mind, while maintaining the speed and accuracy as possible.


## - A user-friendly SQLi tool

InjectBot is as simple as google in web searching; the options are so simple. Just insert the link you want to scan in the scan box then click scan! The tool will show whether the link is vulnerable to SQLi or not.

![InjectBot scan](https://www.tariqhawis.com/img/injectbot/injectbot-scan.png)

If the target turns out to be vulnerable, a tool will save the target's state and provides further options for you which are as follows:

1. Get target's database and server information:

![InjectBot database info](https://www.tariqhawis.com/img/injectbot/injectbot-dbinfo.png)


2. Get tables information from the current database:

![InjectBot tabel schemas](https://www.tariqhawis.com/img/injectbot/injectbot-tableschema.png)


3. Fetch data rows of selected table:

![InjectBot data rows](https://www.tariqhawis.com/img/injectbot/injectbot-datarows.png)


4. Switch between classic and blind SQLi in options above.


# Who should use InjectBot?

Whether you are a web developer or pen tester, this tools would be useful to test web applications against SQL Injection vulnerabilities. 


# Have an idea for InjectBot?

There are plenty of improvements this script could use, If you want to add something and have any cool idea related to this tool, please contact me using [github issues](https://github.com/tariqhawis/injectbot/issues) and I will update the master version.


# How to Install?

The easiest way is to use our updated docker image which already has injectbot installed and updated with all necessary dependencies, just run this below command:

``docker run --name injectbot -d -p 8080:80 tariqhawis/injectbot``

After execute it in your terminal, then if you get a long hash then your docker is now up and running; you can confirm the status with this command: ``docker ps -a``

Now open Injectbot from your browser using this URL:

http://localhost:8080/

Second option is to clone this repository and point your apache to the tool's path, you should have PHP and curl library installed. to make sure you have apache/php installed, follow below step

For Ubuntu/Debian/Kali...etc:

``apt -y install apache2 php php-curl``

For RHEL (Fedora, CentOS...etc):

``yum -y install httpd php php-curl``

If you have any issue with installation, contact me at [github issues](https://github.com/tariqhawis/injectbot/issues), and I will be glad to help:)


> No specific php version needed, this tools has been tested on PHP 5.x till PHP 7.x


# Looking for a useful SQL Injection Course?

Contact me if you are looking for a course on web penetration testing, web application security, or a course explosively on SQL Injection, I am preparing for attackers and defenders (100% technical).


# Advisory

This tool should be used for authorized penetration testing and/or educational purposes only. 
Any misuse of this software will not be the responsibility of the author or of any other collaborator. 
Use it at your own networks and/or with the network owner's permission.


MIT License 2009-2020 InjectBot :tm: - By TrX
