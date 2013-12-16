#Server [![Build Status](https://travis-ci.org/mageekguy/server.png?branch=master)](https://travis-ci.org/mageekguy/server)

Server is a generic (asynchronous) socket server written in PHP.  

##Requierements
To use it, you should use an UNIX operating system and the following PHP extensions should be available:

* [PCNTL](http://www.php.net/manual/en/pcntl.installation.php);
* [POSIX](http://www.php.net/manual/en/posix.installation.php);
* [socket](http://bd1.php.net/manual/en/sockets.installation.php).

Moreover, you should use a PHP version ≥ 5.4.  

##Features
Currently, the server can listen on several IP and handle several clients.  

##How to use it?
A demo is available in the `demo/` directory.  
To run it:  

1. Go to the `demo/scripts` directory;
2. Rename the `.config.sample.php` file to `.config.php`;
3. Edit it to define the IP, the port, the user, the home directory and loggers of the server;
4. `sudo php server.php`;
5. Start a telnet on the IP and the port and type something.

Be aware that the user which run the server should have permissions to create log files.
If the server works fine, each message sent to the server via telnet are logged in the info log file.  
Moreover, the server should reply with a [rot13](http://en.wikipedia.org/wiki/ROT13) version of your message.  
To stop the server, just get the PID of the server with the `ps aux | grep php` command and kill it with `kill <pid>`.
If you can't connect to the server, the first thing you should do is to run it in foreground with the following command:
```
sudo php server.php -f
```
In this mode, the server will not fork, so you can see all output and error directly on your screen.
Note that error log file will be used too.
For more informations to develop your own server, just look `demo/classes/scripts/server.php`.  
