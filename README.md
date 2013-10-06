<html>
<head>
</head>
<body>
Installation:
For txtweb app 
-------------------
step 1)
create a file named,bridge.php in your server and
Put the  code 
"
<!doctype html>
 <html> 
    <head>
        <meta name = "txtweb-appkey" content = "<your txtweb-appkey here>" />
    </head>
    <body></body>
</html>"<br/>
You must specify your txtweb app key as the value of content attribute of meta tag.

For innoz app
-----------------
Put the this file in your server
------------------------------Steps common to both platforms------------------------------------
App URL for innoz : Give the url of bridge.php
App URL for txtweb: Give the url as url to bridge.php?app=<appname> (ex: for app named ire  hosted at
After URL verification by txtweb,replace bridge.php with ,bridge.php given  at https://github.com/jinujd/txtweb_innoz_bridge                                                                                     mydomain.com mydomain.com/bridge.php?app=ire) 
 																					   (You must complete step 1 before this)
----------------------------------------------------------------------
Create an apps.xml file in the  same directory as that of bridge.php(this file)
Under the root tag named apps,
1. For txtweb app corresponding to an innoz app ,the tag must start with TxtwebApp_ and the appname must follow
2. For innoz app corresponding to a txtweb app, the tag must start with InnozApp_ and the  innoz app key must follow
3.Give the link to the original implementation of the app ,as the value of the tag
4.For txtweb apps specify the app key as an attribute named key
5.You can specfiy an additional attribute called prepareURL ,whose value is a url.Before converting the app,
  the result produced by original implementation of the app will be sent to this url as a GET parameter named 'appStream'.
  The output produced by this URL will be the ourtput of new app.
  This is useful when you need to prepare the app output before conversion,like changing txtweb number written in app response to that of innoz platform.
Sample xml file
 -----------------
 <?xml version="1.0" encoding="ISO-8859-1"?>
<apps>
    <TxtwebApp_vote4 prepareURL = 'http://www.myUrl.com.prepare.php' key='3434-34343434-34343434-3434k34'>http://myTxtwebApp1.com/index.php</TxtwebApp_vote4 >
	<TxtwebApp_vote4 key='3434-34343434-34343434-3434k34d'>http://myTxtwebApp2.com/index.php</TxtwebApp_vote4 >
    <InnozApp_dabcdddde6ff0853e8e84ca1845e127a>http://www.myInnozApp1.com/index.php</InnozApp_dabcdddde6ff0853e8e84ca1845e127a>
    <InnozApp_dabcdddde6ff0853e8e84ca1845e127w prepareURL = 'http://www.myUrl.com.prepare.php' >http://www.myInnozApp2.com/index.php</InnozApp_dabcdddde6ff0853e8e84ca1845e127w>
</apps>



NOTE:
Make sure that 'allow_url_fopen' is set to 'On' in the php.ini of the server where the app is actually implemented  
</body>
</html>
