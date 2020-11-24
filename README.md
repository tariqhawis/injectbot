# =|)=-injrobot-=|>

## About injrobot

### Powerful web tool for scans and exploits SQL Injection web vulnerability.

A tool I made almost  12 years ago for my own use. I couldn't be more regretful to keep it from public all this long. Now It is time to let other SQLi enthusiasts to use and contribute to this wonderful tool


### Why Injrobot?

You may wonder why you would use this tool while there are many sql tools out there that are powerful enough.
Injrobot designed for two propose in mind; 


#### 1st. A user-friendly SQLi tool

Injrobot is as simple as google in web searching. With it, you don't need to setup proxy settings in you browser. And it's a GUI tool! So if you had enough from black screens, then injrobot should be your next tool

#### 2nd. A powerful tool!

With injrobot you can find mostly all kind of exploit types of SQL Injections, including MySQL (4, 5 and above), SQL Server (2005, 2008 and above), and MS Access DB; It also covers various kinds of techniques; From fetching tables and data records, to read file from and upload web shell into the target system; And all that with all possible evasion techniques
.

### Have an idea for InjRobot?

There are plenty of improvements this script could use, I have listed some of them in TODO.txt file, but I am certain there is much more can be added whether to enhance the performance, or to the frontend to make it prettier and more user friendly!

If you want to add something and have any cool idea related to this tool, please contact me using [https://github.com/tariqhawis/injrobot/issues](github issues) and I will update the master version.


### How to use Injrobot?

The easiest way is to use our docker images which already have our script installed and updated, just run this below command:

``docker run --name injrobot -d -p 8080:80 tariqhawis/injrobot``

Wait for some time until you get a long hash which means it now up and running, you can confirm the status with : ``docker ps -a``
now open your browser and type the url:

http://localhost:8080/

You should see the script's black and green page!

Of course, as and alternative way you can always have the liberty to clone and install the script on your own apache server if it's already there and it should work as normal.


### Looking for a useful SQL Injection Course?

Contact me and ask about the SQL Injection Course, I am preparing for attackers and defenders (100% technical).


### Advisory

All the uses of this tool should be used for authorized penetration testing and/or educational purposes only. 
Any misuse of this software will not be the responsibility of the author or of any other collaborator. 
Use it at your own networks and/or with the network owner's permission.


MIT License 2008-2020 injrobot - By TrX(TM)
