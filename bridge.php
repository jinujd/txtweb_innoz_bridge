<?php
/*

Story , screenplay and direction : Jinu Joseph Daniel 


@------------------------------------@
__    __       _  __       __      __
|D )   |D )    |  |.  \   |      |
|-+    |-+     |  |.   |   ---   | --
|D )   |   \_  |  | _./   |__|   | __

@------------------------------------@

_____________The app  story__________________
One pleasent morning ,two apps ,
one from txtweb and other
from 55444 met  each other .

They were at opposite banks of a river..

o                                   /o\
{||  '`'`'`'`'`'`'`'`'`'`'`'`'`'`'`' ||}
|\                                  / |
Hello sweet                   Oh boy!,
heart                         I'm from 55444
I'm from 
Txtweb      
_______________________________

After few days they fallen in love...
~     
^                  ~   
^   ^                            ~
 o                                    /o\
{||  '`'`'`'`'`'`'`'`'`'`'`'`'`'`'`'  ||}
|\                                   / |

They decided to cross the river.
But both of them didn't know swimming.
_______________________________
They decided to find a solution.
________________________________
Txtweb app contacts his friend Jinu Joseph Daniel
who is an engineering student

Dude I need a solution to cross the river         
/     		   
 o     [^]
{||\ . /||}
 |\.  ./ \.
            \ 
Dont worry bro..I will find a solution                         
_________________________________

On the next day Jinu made a  very romantic bridge across the river

wow! this's really awesome man  
\
 o      [^]
{|| \ .  ||
 |\.   ./ \.   |.---------------------.|  
/         '`'`'`'`'`'`'`'`'`'`''`'`'`'`'
Thanks dude.Go and find your love
____________________________________	

           o\.
    o  . / ||}
    |~    / |
    ..\
|.---------------------.| 
'`'`'`'`'`'`'`'`'`'`''`'`'`'`'
With the help of bridge,the two apps crossed the river 
and shared their love.

They lived peacefully ,sharing their love for 10000 years.        

___              __
STORY ENDS


Installation:

Make sure that 'allow_url_fopen' is set to 'On' in the php.ini of the server where the app is actually implemented  
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
</html>"
You must specify your txtweb app key as the value of content attribute of meta tag.

For innoz app
-----------------
Put the this file in your server
------------------------------Steps common to both platforms------------------------------------
App URL for innoz : Give the url of bridge.php
App URL for txtweb: Give the url as url to bridge.php?app=<appname> (ex: for app named ire  hosted at
mydomain.com mydomain.com/bridge.php?app=ire) 
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
*/
error_reporting(E_ERROR); //turn off warnings


class txtweb_innoz_bridge
{
    private static $message // the sms sent to the app
        , $url // url of the app
        , $result // data returned by original implementation of the app 
        , $requestUrl //the url with all parameters
        , $requestedPlatform //the platform(Txtweb/Innoz) requested by the app
        , $links = array() //links in data returned by the app as array(linkName => value)
        , $text //text portion of data returned by the app
        , $implementationPlatform //platform in which app is actually implemented(Txtweb/Innoz)
        , $hostUrl //url where bridge is implemented
        , $platformDetails /* details of the platform as array(platformName => array(
    'textMessage'=><get parameter storing sms sent by app> ,
    'mobHash'=><get parameter storing hashcode of mobile number sent by app>,'
    userLocation'=><get parameter storing location of user sent by app>
    ) )*/ , $txtwebAppKey //app key for txtweb implementation
        , $prepareResult //bool indicating whether the result of original implementation needs to be prepared(for doing some modifications in the original result )
        , $prepareUrl /*url which prepares the return data of original implementation , for conversion.
    Before conversion the result of original implementation will be sent to this URL and the result returned by the URL wil be used to convert the app*/ ;
    
    
    function __construct()
    {
        self::initBridge(); //initialise bridge 
        self::fetchResult(); //fetch result  string produced by the actual implementation of the app , using app url
        if (self::$prepareResult) { //if result  of original implementation is to be prepared
            self::prepareResult(); //prepare the result of original implementation 
        }
        self::extractData(); //extract data from the fetched result string
        self::convertLinks(); //convert all links local to the app ,to links to bridge
        self::formatData(); //format data for output
        self::bridgeEcho(); //echo the output produced by bridge
        
    }
    
    
    function initBridge()
    {
        $scheme        = $_SERVER['HTTPS'] ? 'https' : 'http';
        $query         = parse_url($scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        self::$hostUrl = $query['scheme'] . '://' . $query['host'] . $query['path']; //host url
        
        
        $platformDetails       = array(
            'Txtweb' => array(
                'textMessage' => 'txtweb-message',
                'mobHash' => 'txtweb-mobile',
                'userLocation' => 'txtweb-location',
                'appId' => 'app'
            ),
            'Innoz' => array(
                'textMessage' => 'message',
                'mobHash' => 'mobile',
                'userLocation' => 'region',
                'appId' => 'key'
            )
        );
        self::$platformDetails = $platformDetails;
        
        
        $requestToImplemetationMapping = array(
            'Txtweb' => 'Innoz',
            'Innoz' => 'Txtweb'
        ); //platform mapping
        $requestedPlatform             = self::$requestedPlatform = isset($_GET['txtweb-message']) ? 'Txtweb' : 'Innoz';
        self::$implementationPlatform  = $requestToImplemetationMapping[self::$requestedPlatform];
        $requestedMessageParam         = $platformDetails[self::$requestedPlatform]['textMessage'];
        $implementationMessageParam    = $platformDetails[self::$implementationPlatform]['textMessage'];
        
        
        $appId    = $_GET[$platformDetails[$requestedPlatform]['appId']];
        $nodeName = $requestedPlatform . 'App_' . $appId;
        $document = new DOMDocument();
        $document->load('apps.xml');
        $app = $document->getElementsByTagName($nodeName)->item(0);
        if ($app) {
            if ($requestedPlatform == 'Txtweb') {
                self::$txtwebAppKey = $app->getAttribute('key');
            }
            $prepareUrl = $app->getAttribute('prepareURL');
            if ($prepareUrl) {
                $prepareResult    = true;
                self::$prepareUrl = trim($prepareUrl);
            } else {
                $prepareResult = false;
            }
            self::$prepareResult = $prepareResult;
            $url                 = trim($app->nodeValue);
        }
        
        if (!isset($_GET['fetch'])) {
            self::$url = $url;
        } else { //resolving links specified inside request
            $fetchUrl = trim(urldecode($_GET['fetch']));
            unset($_GET['fetch']);
            $query = parse_url(trim($fetchUrl));
            parse_str($query['query'], $params);
            self::$url = $query['scheme'] . '://' . $query['host'] . $query['path'];
            $_GET      = array_merge($_GET, $params);
        }
        
        
        $query            = self::mapParams($_GET); // parameter mapping
        $queryString      = self::prepareQueryString($query);
        self::$requestUrl = self::$url . '?' . $queryString;
        
    }
    
    
    private function mapParams($params)
    {
        /*maps parameters sent to the requested platform to those of the implemented platform*/
        
        $requestedPlatformDetails      = self::$platformDetails[self::$requestedPlatform];
        $requestedPlatformDetails      = array_flip($requestedPlatformDetails);
        $implementationPlatformDetails = self::$platformDetails[self::$implementationPlatform];
        
        foreach ($requestedPlatformDetails as $param => $value) {
            if (isset($params[$param])) {
                $paramVal                = $params[$param];
                $implementationParamName = $implementationPlatformDetails[$value];
                unset($params[$param]);
                $params[$implementationParamName] = $paramVal;
                
            }
            
        }
        
        return $params;
        
    }
    
    private function prepareQueryString($queryParams) //prepares the query string using url encoding
    {
        $encodedParams = array();
        foreach ($queryParams as $param => $value) {
            array_push($encodedParams, $param . '=' . urlencode($value));
        }
        $queryString = implode('&', $encodedParams);
        return $queryString;
    }
    
    private function fetchResult() //fetches result from original implementation
    {
        self::$result = self::send(self::$requestUrl);
        //self::$result = file_get_contents(self::$requestUrl);
    }
    private function prepareResult()
    {
        $queryParams  = array(
            'appStream' => self::$result
        );
        $queryString  = self::prepareQueryString($queryParams);
        $url          = self::$prepareUrl . '?' . $queryString;
        self::$result = self::send($url);
    }
    private function extractData() //extracts data from the fetched result
    {
        $callBack = 'extractDataFrom' . self::$implementationPlatform . 'Format';
        self::$callBack();
    }
    
    private function formatData() //formats data for the requested platform
    {
        $callBack = 'formatDataFor' . self::$requestedPlatform;
        self::$callBack();
    }
    
    private function bridgeEcho() //releases the output from bridge and sents back to requester
    {
        echo self::$result;
    }
    
    private function convertLinks() //convert links in the app to links to bridge
    {
        $platformDetails            = self::$platformDetails;
        $requestedPlatformDetails   = $platformDetails[self::$requestedPlatform];
        $requestedMessageParam      = $requestedPlatformDetails['textMessage'];
        $implementationMessageParam = $platformDetails[self::$implementationPlatform]['textMessage'];
        $appIdParam                 = $requestedPlatformDetails['appId'];
        $appId                      = $_GET[$appIdParam];
        $urlPrefix                  = self::$hostUrl . '?' . $appIdParam . '=' . $appId . '&' . $requestedMessageParam . '=&fetch='; //$requestedMessageParam is added just to identify the requested platform when fetch is set 
        foreach (self::$links as $linkName => $url) {
            $url                    = str_replace($implementationMessageParam, $requestedMessageParam, $url);
            self::$links[$linkName] = $urlPrefix . urlencode($url);
        }
        
    }
    
    private function extractDataFromInnozFormat() //extracts data from result got from an app implemented in innoz platform
    {
        $document = new DOMDocument();
        $document->loadXML(self::$result);
        $html = $document->getElementsByTagName('body')->length;
        if (!$html) { //data is valid XML
            $content = $document->getElementsByTagName('content');
            $links   = $document->getElementsByTagName('option');
            while (($link = $links->item(0))) {
                $linkName               = trim($link->getAttribute('name'));
                $linkUrl                = trim($link->getAttribute('url'));
                self::$links[$linkName] = $linkUrl;
                $link->parentNode->removeChild($link);
            }
            if ($content && $content->length > 0) {
                $content = $content->item(0);
            }
            $text       = $content->nodeValue;
            self::$text = $text;
        } else { //else the app might be implemented as HTML
            self::extractDataFromTxtwebFormat(); //else it will be same as txtweb app,except that,,there will be no <a> tags
        }
        
    }
    
    private function extractDataFromTxtwebFormat() //extracts data from result got from an app implemented in txtweb platform
    {
        
        $result   = str_replace('&nbsp;', ' ', self::$result);
        $result   = preg_replace('#<br[ ]*/?>#', '
		', $result);
        $document = new DOMDocument();
        $document->loadHTML($result);
        $body  = $document->getElementsByTagName('body');
        $links = $document->getElementsByTagName('a');
        while (($link = $links->item(0))) {
            $linkName               = $link->nodeValue;
            $linkUrl                = trim($link->getAttribute('href'));
            self::$links[$linkName] = $linkUrl;
            $link->parentNode->removeChild($link);
        }
        if ($body && $body->length > 0) {
            $body = $body->item(0);
        }
        $text       = $body->nodeValue;
        self::$text = $text;
    }
    
    private function formatDataForTxtweb() //format data if the requested plaform is txtweb
    {
        $header      = '<!doctype html><html><head> <meta name = "txtweb-appkey" content = "' . self::$txtwebAppKey . '" /></head><body>';
        $textContent = self::$text;
        $linksArr    = self::$links;
        $links       = '';
        foreach ($linksArr as $linkName => $url) {
            $links = $links . '<a href = "' . $url . '">' . $linkName . '</a>';
        }
        $footer       = '</body></html>';
        self::$result = $header . $textContent . $links . $footer;
    }
    
    private function formatDataForInnoz() //format data if the requested plaform is innoz
    {
        $dom     = new DOMDocument('1.0', 'iso-8859-1');
        $root    = $dom->appendChild($dom->createElement('response'));
        $content = $dom->createElement('content', self::$text);
        $root->appendChild($content);
        $options = $root->appendChild($dom->createElement('options'));
        foreach (self::$links as $name => $url) {
            $option = $options->appendChild($dom->createElement('option'));
            $option->setAttribute('name', $name);
            $option->setAttribute('url', $url);
        }
        header("Content-Type:text/xml");
        self::$result = $dom->saveXML();
    }
    
    private function send($url, $method = 'GET') //sends data array(param=>val,...) to the page $url in post/get method and returns the reply string
    {
        /*Need an expert help to set the http headers so that allow_url_fopen can be overridden.
        This implementation will not work for servers that set allow_url_fopen to Off*/
        $options = array(
            "http" => array(
                "Accept-language: en\r\n",
                "method" => $method,
                "header" => "\r\nUser-agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 Safari/537.11"
            )
        );
        $context = stream_context_create($options);
        $page    = file_get_contents($url, true, $context);
        
        return $page;
    }
}

new txtweb_innoz_bridge(); //creating bridge
