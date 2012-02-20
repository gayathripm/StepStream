<?php
    /* Send an SMS using Twilio. You can run this file 3 different ways:
     *
     * - Save it as sendnotifications.php and at the command line, run 
     *        php sendnotifications.php
     *
     * - Upload it to a web host and load mywebhost.com/sendnotifications.php 
     *   in a web browser.
     * - Download a local server like WAMP, MAMP or XAMPP. Point the web root 
     *   directory to the folder containing this file, and load 
     *   localhost:8888/sendnotifications.php in a web browser.
     */
    // Include the PHP Twilio library. You need to download the library from 
    // twilio.com/docs/libraries, and move it into the folder containing this 
    // file.
    require "Services/Twilio.php";
 
    // set our AccountSid and AuthToken - from www.twilio.com/user/account
    $AccountSid = "ACd3f0e3c3de00445fb42f40142fc7d53f";
    $AuthToken = "7816f7ae16568c11a0e408998dda0243";
 
    // instantiate a new Twilio Rest Client
    $client = new Services_Twilio($AccountSid, $AuthToken);
 
    // make an associative array of people we know, indexed by phone number. Feel
    // free to change/add your own phone number and name here.
    $people = array(
        "+16788605254" => "Gayathri",
        
    );
 
    // iterate over all our friends. $number is a phone number above, and $name 
    // is the name next to it
    foreach ($people as $number => $name) {
 
        // Send a new outgoing SMS */
        $sms = $client->account->sms_messages->create(
            // the number we are sending from, must be a valid Twilio number
            "415-599-2671", 
 
            // the number we are sending to - Any phone number
            $number,
 
            // the sms body
            "Hey $name"
        );
 
        // Display a confirmation message on the screen
        echo "Sent message to $name";
    }
?>
