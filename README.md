
Installation:

Make sure that 'allow_url_fopen' is set to 'On' in the php.ini of the server where the app is actually implemented  
For txtweb app 
-------------------
create a file named,bridge.php in your server and
Put the  code 
"
<!doctype html>
 <html> 
    <head>
        <meta name = "txtweb-appkey" content = "<your txtweb-appkey here>" />
    </head>
    <body></body>
</html>"
You must specify your txtweb app key as the value of content attribute of meta tag.

For innoz app
-----------------
Put the this file in your server
------------------------------Steps common to both platforms------------------------------------
App URL for innoz : Give the url of bridge.php
App URL for txtweb: Give the url as url to bridge.php?app=<appname> (ex: for app named ire  hosted at
                                                                                           mydomain.com mydomain.com/bridge.php?app=ire) 
----------------------------------------------------------------------
Create an apps.xml file in the  same directory as that of bridge.php
Under the root tag named apps,
1. For txtweb app corresponding to an innoz app ,the tag must start with TxtwebApp_ and the appname must follow
2. For innoz app corresponding to a txtweb app, the tag must start with InnozApp_ and the  innoz app key must follow
3.Give the link to the original implementation of the app ,as the value of the tag
4.For txtweb apps specify the app key as an attribute named key

Sample xml file
 -----------------
<?xml version="1.0" encoding="ISO-8859-1"?>
<apps>
    <TxtwebApp_surveys key='23232-3ewe-23er2-23dw'>http://jinujd.0fees.net/poll/index.php</TxtwebApp_surveys>
    <InnozApp_23ert-er3w-23ee-3err>http://www.mydomain.com/ire.php</InnozApp_23ert-er3w-23ee-3err>
</apps>
