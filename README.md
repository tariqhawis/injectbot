# =|)=-InjectBot-=|>

## About InjectBot

InjectBot is a web-based SQL injection scanning and exploiting tool. It can scan any kind of web application whether written in PHP, Java, or ASP.NET, that based on MySQL, MSSQL, MS Access.


## Why InjectBot?

Unlike other SQLi tools out there, injectbot designed for simplicity in mind, while maintaining the speed and accuracy.


#### 1st. A user-friendly SQLi tool

InjectBot is as simple as google in web searching. With it, you don't need to change your proxy settings in the browser. And it's a GUI tool! So if you've had enough from the complexity of the parameters and usage syntax in the black-screen tools, then you should give InjectBot a try.

#### 2nd. A powerful tool!

With InjectBot you can find mostly all kind of exploit types of SQL Injections, including MySQL (4, 5 and above), MSSQL/SQL Server (2005, 2008 and above), and MS Access DB; It also covers various kinds of techniques; such as fetching users tables and data records, read file from the server, and even let you have a foothold on the server by the ability to upload web shell into the target system; And all that being integrated with evasion techniques that works to 80% of input validation filter.


## Who should use InjectBot?

This tools is very useful for whoever wants to test a web applications against SQL Injection vulnerability; whether a developer or a pen tester who need to challenge an application's ability to validate user's inputs effectively. 


## Have an idea for InjectBot?

There are plenty of improvements this script could use, I have listed some of them in TODO.txt file, but I am certain there is much more can be added whether to enhance the performance, or to the frontend to make it prettier and more user friendly!

If you want to add something and have any cool idea related to this tool, please contact me using [github issues](https://github.com/tariqhawis/injectbot/issues) and I will update the master version.


## How to Install?

The easiest way is to use our updated docker image which already have our script installed and updated with all necessary dependencies, just run this below command:

``docker run --name injectbot -d -p 8080:80 tariqhawis/injectbot``

After execute it in your terminal, wait for some time until you get a long hash which means it now up and running, you can confirm the status with this command: ``docker ps -a``
now open your browser and type the url:

http://localhost:8080/

A web interface should loaded with InjectRobot at the header!

Of course, as and alternative way you can always clone and install the tool on your own apache server with PHP and curl library enabled.
If you do not have any or none of those things, and still don't want to use docker. Run the following command:

Ubuntu:

``apt -y install apache2 php php-curl``

RHEL (Fedora, CentOS...etc):

``yum -y install httpd php php-curl``

If you have any issue with installation, contact me at [github issues](https://github.com/tariqhawis/injectbot/issues), and I will be glad to help:)


> No specific php version needed, this tools has been tested on PHP 5.x as well as PHP 7.x


## Looking for a useful SQL Injection Course?

Contact me if you are looking for a course on web penetration testing, web application security, or a course explosively on SQL Injection, I am preparing for attackers and defenders (100% technical).


## Advisory

This tool should be used for authorized penetration testing and/or educational purposes only. 
Any misuse of this software will not be the responsibility of the author or of any other collaborator. 
Use it at your own networks and/or with the network owner's permission.


MIT License 2008-2020 InjectBot :tm: - By TrX
