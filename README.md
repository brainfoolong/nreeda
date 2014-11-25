nReeda - Web-based Open Source RSS/XML/Atom Feed Reader
======

#### Breaking change on update
When you have already installed nReeda and when you update to 1.1.0 you need to update one file manually.
Upload/Override all files manually as always, than open
`modules/RDR/_RDR.local.php` and remove the first parameter of CHOQ\_DB::add 
```
CHOQ_DB::add('default', 'mysql://...');
# must be changed to
CHOQ_DB::add('mysql://...');
```

### What is nReeda?
Simply: A self hosted RSS Reader. Just collect thousends of news from around the globe. nReeda will bring it all together in a fluid design. nReeda, was mainly designed for easy access to all your important news, on the phone, on the desktop, anywhere. There is no need of an extra app for this, it all works with just the website itself.

#### Demo
The best way to see how this application works is with our demo login.
Head to http://nreeda.bfldev.com/
User: demo
PW: demo 

#### More Information
Go to the Wiki at https://github.com/brainfoolong/nreeda/wiki
