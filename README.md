#Server

Server is a generic (asynchronous) socket server written in PHP.  

##Requierements
To use it, you should use an UNIX operating system and the following PHP extensions should be available:
* [PCNTL](http://www.php.net/manual/en/pcntl.installation.php);
* [POSIX](http://www.php.net/manual/en/posix.installation.php).
Moreover, you should use a PHP version ≥ 5.3.  

##Features
Currently, the server can listen on several IP and handle several clients.  

##How to use it?
A demo is available in the `demo/` directory.  
To run it:  
1. Go in the `demo/scripts` directory;
2. Rename the `.config.sample.php` file to `.config.php`;
3. Edit it to define the IP, the port, the user, the home directory and loggers of the server;
4. `sudo php server.php`;
5. Do a telnet on the IP and the port and tape something.
If the server works fine, each message sent to the server via telnet are logged in the info log file.  
If it's not the case, errors are logged in the error log file.  
To stop the server, just get the PID of the server with the `ps aux | grep php` command and kill it with `kill <pid>`.  
For more informations to develop your own server, just look `demo/classes/scripts/server.php`.  
