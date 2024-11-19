<?php
/**
 *  PayPal IPN Listener
 *  @note - before testing if the script works, first check if the PayPal sandbox is not down at:
 *  https://www.sandbox.paypal.com/cgi-bin/webscr
 *
 *  A class to listen for and handle Instant Payment Notifications (IPN) from 
 *  the PayPal server.
 *
 *  https://github.com/Quixotix/PHP-PayPal-IPN
 *
 *  @package    PHP-PayPal-IPN
 *  @author     Micah Carrick
 *  @modified   Modified by Kestutis Matuliauskas
 *  @version    2.1.0
 */
class PayPalIPN
{
    /**
     *  If true, the recommended cURL PHP library is used to send the post back 
     *  to PayPal. If false then fsockopen() is used. Default true.
     *
     *  @var boolean
     */
    protected $use_curl = true;
    
    /**
     *  NOTE - Your best bet is to leave this as false and let server to use the default.
     *  If true, explicitly sets cURL to use SSL version 4. Use this if cURL
     *  is compiled with GnuTLS SSL.
     *  http://php.net/manual/en/function.curl-setopt.php
     *
     *  @var boolean
     */
    protected $force_ssl_v4 = false;
   
    /**
     *  If true, cURL will use the CURLOPT_FOLLOWLOCATION to follow any 
     *  "Location: ..." headers in the response.
     *
     *  @var boolean
     */
    protected $follow_location = false;
    
    /**
     *  If true, an SSL secure connection (port 443) is used for the post back 
     *  as recommended by PayPal. If false, a standard HTTP (port 80) connection
     *  is used. Default true.
     *
     *  @var boolean
     */
    protected $use_ssl = true;
    
    /**
     *  The amount of time, in seconds, to wait for the PayPal server to respond
     *  before timing out. Default 30 seconds.
     *
     *  @var int
     */
    protected $timeout = 30;
    
    protected $webHost = "";
    
    private $post_data = array();
    private $post_uri = '';     
    private $response_status = '';
    private $response = '';


    public function __construct($paramWebHost)
    {
        $this->webHost = sanitize_text_field($paramWebHost);
    }


    /**
     *  Require Post Method
     *
     *  Throws an exception and sets a HTTP 405 response header if the request
     *  method was not POST.
     */
    public function requirePostMethod()
    {
        // require POST requests
        if ($_SERVER['REQUEST_METHOD'] && $_SERVER['REQUEST_METHOD'] != 'POST')
        {
            header('Allow: POST', true, 405);
            throw new \Exception("Invalid HTTP request method.");
        }
    }
    
    /**
     *  Process IPN
     *
     *  Handles the IPN post back to PayPal and parsing the response. Call this
     *  method from your IPN listener script. Returns true if the response came
     *  back as "VERIFIED", false if the response came back "INVALID", and
     *  throws an exception if there is an error.
     *
     *  @return bool
     *  @throws \Exception
     */
    public function processIpn()
    {
        // read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
        $requestData = 'cmd=_notify-validate';

        // STEP 1: read POST data - use raw POST data
        if (!empty($_POST))
        {
            $this->post_data = $_POST;

            // Reading POSTed data directly from $_POST causes serialization issues with array data in the POST.
            // Instead, read raw POST data from the input stream.
            $rawPOSTData = file_get_contents('php://input');
            $rawPOSTArray = explode('&', $rawPOSTData);
            $myPost = array();
            foreach ($rawPOSTArray AS $keyValue)
            {
                $keyValue = explode ('=', $keyValue);
                if (count($keyValue) == 2)
                {
                    $myPost[$keyValue[0]] = urldecode($keyValue[1]);
                }
            }

            foreach ($myPost AS $key => $value)
            {
                $value = urlencode($value);
                $requestData .= "&$key=$value";
            }
        } else
        {
            throw new \Exception("No POST data found.");
        }

        if ($this->use_curl)
        {
            $this->curlPost($requestData);
        } else
        {
            $this->fsockPost($requestData);
        }

        if (strpos($this->response_status, '200') === false)
        {
            throw new \Exception("Invalid response status: ".sanitize_text_field($this->response_status).".");
        }

        // Use strpos here instead of strcmp, because words VERIFIED / INVALID stays somewhere at the end of the string
        if (strpos($this->response, "VERIFIED") !== false)
        {
            return true;
        } elseif (strpos($this->response, "INVALID") !== false)
        {
            return false;
        } else
        {
            throw new \Exception("Unexpected response from PayPal.");
        }
    }

    public function getIPN_Data()
    {
        return $this->post_data;
    }

    /**
     *  Post Back Using fsockopen()
     *
     *  Sends the post back to PayPal using the fsockopen() function. Called by
     *  the processIpn() method if the use_curl property is false. Throws an
     *  exception if the post fails. Populates the response, response_status,
     *  and post_uri properties on success.
     *
     *  @param  string  $requestData The post data as a URL encoded string
     *  @throws \Exception
     */
    protected function fsockPost($requestData)
    {

        if ($this->use_ssl)
        {
            $uri = 'tls://'.$this->webHost;
            $port = '443';
            $this->post_uri = $uri.'/cgi-bin/webscr';
        } else
        {
            $uri = $this->webHost; // no "http://" in call to fsockopen()
            $port = '80';
            $this->post_uri = 'http://'.$uri.'/cgi-bin/webscr';
        }

        // Set up the acknowledgement request headers
        $header = "POST /cgi-bin/webscr HTTP/1.1\r\n";              // HTTP POST request
        //$header .= "Host: ".$this->getWebHost()."\r\n"; // THIS HAS TO BE REMOVED (PP RECOMEDANTION)
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: ".strlen($requestData)."\r\n";
        $header .= "Connection: Close\r\n\r\n";

        // Open a socket for the acknowledgement request
        $fp = fsockopen($uri, $port, $errno, $errstr, $this->timeout);

        if (!$fp)
        {
            // fsockopen error
            throw new \Exception("fsockopen error: [$errno] $errstr");
        }

        // Send the HTTP POST request back to PayPal for validation
        fputs($fp, $header.$requestData."\r\n\r\n");

        // While not EOF
        while(!feof($fp))
        {
            // Get the acknowledgement response
            if (empty($this->response))
            {
                // extract HTTP status from first line
                $this->response .= $status = fgets($fp, 1024);
                $this->response_status = trim(substr($status, 9, 4));
            } else
            {
                $this->response .= fgets($fp, 1024);
            }
        }

        // Close the file
        fclose($fp);
    }

    /**
     *  Post Back Using cURL
     *
     *  Sends the post back to PayPal using the cURL library. Called by
     *  the processIpn() method if the use_curl property is true. Throws an
     *  exception if the post fails. Populates the response, response_status,
     *  and post_uri properties on success.
     *
     *  @param string $requestData  The post data as a URL encoded string
     *  @throws \Exception
     */
    protected function curlPost($requestData)
    {
        if ($this->use_ssl)
        {
            $this->post_uri = 'https://'.$this->webHost.'/cgi-bin/webscr';
        } else
        {
            $this->post_uri = 'http://'.$this->webHost.'/cgi-bin/webscr';
        }

        $ch = curl_init($this->post_uri);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->follow_location); // Not used in PayPal manual demo, added additionally
        curl_setopt($ch, CURLOPT_HEADER, true); // Not used in PayPal manual demo, added additionally
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout); // Not used in PayPal manual demo, added additionally
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

        // Taken from: https://developer.paypal.com/docs/classic/ipn/ht_ipn/
        // In wamp-like environments that do not come bundled with root authority certificates,
        // please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set
        // the directory path of the certificate as shown below:
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cert/cacert.pem");

        // Your best bet is to not set this and let it use the default.
        // Setting it to 2 or 3 is very dangerous given the known vulnerabilities in SSLv2 and SSLv3.
        if ($this->force_ssl_v4)
        {
            // v3 is outdated for security risks, we must use v4 here
            // Read more: https://stories.paypal-corp.com/archive/paypal-response-to-ssl-30-vulnerability-aka-poodle
            curl_setopt($ch, CURLOPT_SSLVERSION, 4);
        }

        $this->response = curl_exec($ch);
        $this->response_status = strval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        
        if(!($res = $this->response))
        {
            $errno = curl_errno($ch);
            $errstr = curl_error($ch);
            throw new \Exception("cURL error: [$errno] $errstr");
        }
    }

    /**
     *  Get POST URI
     *
     *  Returns the URI that was used to send the post back to PayPal. This can
     *  be useful for troubleshooting connection problems. The default URI
     *  would be "ssl://www.sandbox.paypal.com:443/cgi-bin/webscr"
     *
     *  @return string
     */
    public function getPostUri()
    {
        return $this->post_uri;
    }

    /**
     *  Get Response
     *
     *  Returns the entire response from PayPal as a string including all the
     *  HTTP headers.
     *
     *  @return string
     */
    public function getResponse() {
        return $this->response;
    }
    
    /**
     *  Get Response Status
     *
     *  Returns the HTTP response status code from PayPal. This should be "200"
     *  if the post back was successful. 
     *
     *  @return string
     */
    public function getResponseStatus() {
        return $this->response_status;
    }
    
    /**
     *  Get Text Report
     *
     *  Returns a report of the IPN transaction in plain text format. This is
     *  useful in emails to order processors and system administrators. Override
     *  this method in your own class to customize the report.
     *
     *  @return string
     */
    public function getTextReport() {
        
        $r = '';
        
        // date and POST url
        for ($i=0; $i<80; $i++) { $r .= '-'; }
        $r .= "\n[".date('m/d/Y g:i A').'] - '.$this->getPostUri();
        if ($this->use_curl) $r .= " (curl)\n";
        else $r .= " (fsockopen)\n";
        
        // HTTP Response
        for ($i=0; $i<80; $i++) { $r .= '-'; }
        $r .= "\n{$this->getResponse()}\n";
        
        // POST vars
        for ($i=0; $i<80; $i++) { $r .= '-'; }
        $r .= "\n";
        
        foreach ($this->post_data as $key => $value) {
            $r .= str_pad($key, 25)."$value\n";
        }
        $r .= "\n\n";
        
        return $r;
    }
}

