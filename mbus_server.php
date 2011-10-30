<?php

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL ^ E_NOTICE); 
ini_set('display_errors', 1);

class mbus_server {

    /**
     * Socket for server
     */
    private $server_socket;
    
    /**
     * Port for the service. 
     */
    private $port = 560;

    /**
     * IP address for the target host. 
     */
    private $address = '127.0.0.1'; // Local testing.

    public function run() {
        $this->mylog( "Server for testing Cyble mbus communications over TCP/IP Connection");
        if ( !$this->createSocket() ) {
            return false;
        }


        while (true) {
            $this->mylog( "Waiting for a connection ...");
            $conn = false;
            switch(@socket_select($r = array($this->server_socket), $w = array($this->server_socket), $e = array($this->server_socket), 60)) {
                case 0: 
                    $this->mylog("Connection timed out");
                    break;
                case 1:
                    $this->mylog("Connection accepted");
                    $connection = @socket_accept($this->server_socket);
                    if ($connection === false) { 
                        $this->mylog("Connection invalid!");
                        usleep(1000); 
                    } elseif ($connection > 0) { 
                        
                        $this->mylog("Receiving data request ...");
                        $buf = socket_read($connection, 2048);
                        $this->outputByteString($buf);
                        $this->mylog( "Receive data done.");
                       
                        $this->mylog("Sending sample data...");
                        $in = "\x68\x56\x56\x68\x08\x06\x72\x86\x78\x01\x10\x77\x04\x14\x07\xad\x00\x00\x00\x0c\x78\x86\x78\x01\x10\x0d\x7c\x08\x44\x49\x20\x2e\x74\x73\x75\x63\x0a\x30\x30\x30\x30\x30\x30\x30\x30\x30\x30\x04\x6d\x08\x09\x76\x1a\x02\x7c\x09\x65\x6d\x69\x74\x20\x2e\x74\x61\x62\x8b\x0f\x04\x13\x01\x00\x00\x00\x04\x93\x7f\x00\x00\x00\x00\x44\x13\x01\x00\x00\x00\x0f\x00\x02\x1f\x97\x16";
                        $this->outputByteString($in);
                        
                        $sent = socket_write($connection, $in, strlen($in));
                        if($sent === false) { 
                            $this->mylog( "socket_write() failed. Reason: ($result) " . socket_strerror(socket_last_error($this->server_socket)) . "");
                            $this->myCloseSocket($connection);
                            myexit(0);
                        }       
                        $this->mylog("sample data sent.");
                        
                    } else { 
                        $this->mylog("Error: ".socket_strerror($connection)); 
                        $this->mylog("Communication is over for this connection.");
                        myexit();
                    }             
                    break;
                case 2:
                    $this->mylog( "Connection refused");
                    break;
                default:
                    $this->mylog( "Unknown socket select return code.");
                    sleep(5);
                    break;
            }
        }


        $this->my_close_socket($this->server_socket);

        $this->mylog( "Done.\n\n");
    }

    /**
     * Create socket.
     */    
    private function createSocket() {
        /** 
         * Create a TCP/IP socket. 
         */
        $this->server_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($this->server_socket === false) {
            $this->mylog( "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "");
            return false;
        } else {
            $this->mylog( "Socket Created.");
        }

        $this->mylog( "Attempting to bind to '$this->address' on port '$this->port'...");
        @socket_bind($this->server_socket, $this->address, $this->port);

        if ( socket_last_error() == 98 ) {
            $this->mylog ("Address in use attempt to close socket first.");
            $this->myCloseSocket($this->server_socket);

            // Retry.    
            $this->server_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

            if ($this->server_socket === false) {
                $this->mylog( "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "");
                return false;
            } else {
                $this->mylog( "Socket Created.");
            }
            
            $this->mylog( "Retry, attempting to bind to '$this->address' on port '$this->port'...");
            socket_bind($this->server_socket, $this->address, $this->port);
            
            if ( socket_last_error() == 98 ) {
                $this->mylog( "Bind failed... aborting.");
                return false;
            }    
        }

        // Start listening for connections 
        socket_listen($this->server_socket); 

        // Non block socket type 
        //socket_set_nonblock($this->server_socket); 
        
        return true;
    
    }

    /**
     * Close a socket.
     */
    private function myCloseSocket($socket) {
        /**
         * Close the socket
         */
        $this->mylog( "Closing socket...");
             
        $arrOpt = array('l_onoff' => 1, 'l_linger' => 1);
        socket_set_block($socket);
        socket_set_option($socket, SOL_SOCKET, SO_LINGER, $arrOpt);
        sleep(1);//wait remote host
        socket_shutdown($socket, 1);//remote host yet can read
        sleep(1);//wait remote host
        socket_shutdown($socket, 0);//close reading
        sleep(1);//wait remote host    
        socket_close($socket);//finaly we can free resource    

        $this->mylog( "OK Closed.");
    }

    private function mylog($message) {
        echo "\n" . date("c") . " - " . $message;
    }

    private function myexit($exitCode = 0, $message = "") {
        $this->mylog("Exiting. " . $message);
        echo "\n\n";
        exit($exitCode);

    }

    function byteStr2byteArray($s) {
            return array_slice(unpack("C*", "\0".$s), 1);
    }
    function byteArray2byteStr(array $t) {
            return call_user_func_array(pack, array_merge(array("C*"), $t));
    }
    function lsbStr2ushortArray($s) {
            return array_slice(unpack("v*", "\0\0".$s), 1);
    }
    function ushortArray2lsbStr(array $t) {
            return call_user_func_array(pack, array_merge(array("v*"), $t));
    }
    function lsbStr2ulongArray($s) {
            return array_slice(unpack("V*", "\0\0\0\0".$s), 1);
    }
    function ulongArray2lsbStr(array $t) {
            return call_user_func_array(pack, array_merge(array("V*"), $t));
    }
    private function outputByteString($s) {
        $out = "";
        $bufArr = $this->byteStr2byteArray($s);
        foreach( $bufArr as $char ) {
            $out .= "\\";
            $out .= "x";
            if ( intval($char) < 16 ) {
                $out .= "0";
            }
            $out .= dechex($char) . "";
        }
        echo "\n\n" . $out . "\n";    
    }
}

echo "\n\nStart\n\n";

$mbusServer = new mbus_server();
$mbusServer->run();

echo "\n\nFinshed.\n\n";
