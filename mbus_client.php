<?php

include_once ("mbus_defs.php");
include_once ("mbus_frame.php");
include_once ("mbus_utils.php");

class mbus_client {

    /**
     * Port for the service.
     */
    private $port = 559;

    /**
     * IP address for the target host.
     */
    //private $address = '127.0.0.1'; // Localhost for testing
    private $address = '180.216.43.111'; // Vivid Wireless IP Address

    /**
     * Device ID
     */
    private $device_id = 4; // This is the short ID of the cyble.


    /**
     * Socket onnection to the service.
     * Allows the client to send and receive byte data.
     */
    private $socket;

    /**
     * Run the mbus client.
     */
    public function run($testmode = false) {

        $data = "";

        if ( $testmode ) {
            mbus_utils::mylog( "!!! TEST MODE - SENDING HARDCODED BYTE STRING - NOT ACTUALLY CONNECTING !!!");

            /**
             * test building the request byte string.
            $data = "";
            if ( $this->getData($data, $this->device_id) === false ) {
                mbus_utils::mylog( "Get data failed.");
                return false;
            }
             */


            // $data = "\x68\x56\x56\x68\x08\x06\x72\x86\x78\x01\x10\x77\x04\x14\x07\xad\x00\x00\x00\x0c\x78\x86\x78\x01\x10\x0d\x7c\x08\x44\x49\x20\x2e\x74\x73\x75\x63\x0a\x30\x30\x30\x30\x30\x30\x30\x30\x30\x30\x04\x6d\x08\x09\x76\x1a\x02\x7c\x09\x65\x6d\x69\x74\x20\x2e\x74\x61\x62\x8b\x0f\x04\x13\x01\x00\x00\x00\x04\x93\x7f\x00\x00\x00\x00\x44\x13\x01\x00\x00\x00\x0f\x00\x02\x1f\x97\x16";


            //$data="\x68\x56\x56\x68\x08\x04\x72\x87\x03\x02\x10\x77\x04\x14\x03\x9A\x00\x00\x00\x0C\x78\x87\x03\x02\x10\x0D\x7C\x08\x44\x49\x20\x2E\x74\x73\x75\x63\x0A\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x04\x6D\x2B\x0F\x79\x1A\x02\x7C\x09\x65\x6D\x69\x74\x20\x2E\x74\x61\x62\xD2\x0F\x04\x14\x1A\x00\x00\x00\x04\x94\x7F\x00\x00\x00\x00\x44\x14\x19\x00\x00\x00\x0F\x00\x02\x1F\x9F\x16";

            //$data="\x68\x56\x56\x68\x08\x04\x72\x87\x03\x02\x10\x77\x04\x14\x03\x9B\x00\x00\x00\x0C\x78\x87\x03\x02\x10\x0D\x7C\x08\x44\x49\x20\x2E\x74\x73\x75\x63\x0A\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x04\x6D\x33\x0F\x79\x1A\x02\x7C\x09\x65\x6D\x69\x74\x20\x2E\x74\x61\x62\xD2\x0F\x04\x14\x1A\x00\x00\x00\x04\x94\x7F\x00\x00\x00\x00\x44\x14\x19\x00\x00\x00\x0F\x00\x02\x1F\xA8\x16";

            //$data="\x68\x56\x56\x68\x08\x04\x72\x87\x03\x02\x10\x77\x04\x14\x03\x9C\x00\x00\x00\x0C\x78\x87\x03\x02\x10\x0D\x7C\x08\x44\x49\x20\x2E\x74\x73\x75\x63\x0A\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x04\x6D\x09\x13\x79\x1A\x02\x7C\x09\x65\x6D\x69\x74\x20\x2E\x74\x61\x62\xD2\x0F\x04\x14\x1A\x00\x00\x00\x04\x94\x7F\x00\x00\x00\x00\x44\x14\x19\x00\x00\x00\x0F\x00\x02\x1F\x83\x16";

            //$data = "\x68\x56\x56\x68\x08\x08\x72\x80\x03\x02\x10\x77\x04\x14\x16\xA1\x00\x00\x00\x0C\x78\x80\x03\x02\x10\x0D\x7C\x08\x44\x49\x20\x2E\x74\x73\x75\x63\x0A\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x04\x6D\x27\x0F\x79\x1A\x02\x7C\x09\x65\x6D\x69\x74\x20\x2E\x74\x61\x62\xD2\x0F\x04\x15\xB7\x11\x00\x00\x04\x95\x7F\x00\x00\x00\x00\x44\x15\xB7\x11\x00\x00\x0F\x00\x04\x1F\x0D\x16";

            //$data="\x68\x56\x56\x68\x08\x04\x72\x87\x03\x02\x10\x77\x04\x14\x03\xA3\x00\x00\x00\x0C\x78\x87\x03\x02\x10\x0D\x7C\x08\x44\x49\x20\x2E\x74\x73\x75\x63\x0A\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x04\x6D\x0B\x0B\x7F\x1A\x02\x7C\x09\x65\x6D\x69\x74\x20\x2E\x74\x61\x62\xCC\x0F\x04\x14\x1A\x00\x00\x00\x04\x94\x7F\x00\x00\x00\x00\x44\x14\x19\x00\x00\x00\x0F\x00\x02\x1F\x84\x16";

            //RX-buffer:31.10.11 11:53 Win V
            $data="\x68\x56\x56\x68\x08\x04\x72\x87\x03\x02\x10\x77\x04\x14\x03\xA7\x00\x00\x00\x0C\x78\x87\x03\x02\x10\x0D\x7C\x08\x44\x49\x20\x2E\x74\x73\x75\x63\x0A\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x04\x6D\x35\x0B\x7F\x1A\x02\x7C\x09\x65\x6D\x69\x74\x20\x2E\x74\x61\x62\xCC\x0F\x04\x14\x1A\x00\x00\x00\x04\x94\x7F\x00\x00\x00\x00\x44\x14\x19\x00\x00\x00\x0F\x00\x02\x1F\xB2\x16";

        } else {
            /**
             * Establish a connection.
             */
            if ( ! $this->connect() ) {
                return false;
            }

            /**
             * Get data from socket.
             */
            $data = "";
            if ( $this->getData($data, $this->device_id) === false ) {
                if ( ! $this->disconnect($this->socket) ) {
                    mbus_utils::mylog( "Disconnect failed.");
                    return false;
                }
                mbus_utils::mylog( "Get data failed.");
                return false;
            }

            /**
             * Disconnect from socket.
             */
            if ( ! $this->disconnect($this->socket) ) {
                mbus_utils::mylog( "Disconnect failed.");
                return false;
            }
        } // if testmode

        /**
         * Parse data retrieved.
         */
        //mbus_utils::mylog( "Parse data ...");

        $mbusFrame = new mbus_frame();
        if ( ! $mbusFrame->parse($data) ) {
            mbus_utils::mylog( "Parse data failed!");
            return false;
        }
        $mbusFrame->PrettyPrint();

        return true;
    }

    /**
     * Interact with the socket by sending and receiving bytes.
     */
    private function getData(&$data, $device_id) {
        /**
         * Send the data request byte string.
         */
        $mbusFrame = new mbus_frame();
        $request = $mbusFrame->get_data_request_frame($device_id);

        //$request = "\x10\x5B\x04\x5F\x16"; // ID = 4
        //$request = "\x10\x5B\x06\x61\x16"; // ID = 6

        //mbus_utils::outputByteString($request);
        //return true;

        if ( ! $this->sendData($request) ) {
            mbus_utils::mylog( "Failed to send data request.");
            return false;
        }

        if ( $this->receiveData($data) === false ) {
            mbus_utils::mylog( "Failed to receive data.");
            return false;
        }

        mbus_utils::mylog("Data received.");
        //mbus_utils::outputByteString($data);
        return true;
    }

    /**
     * Send data
     */
    private function sendData($data) {
        mbus_utils::mylog( "Sending data request via packed hex string...");
        mbus_utils::outputByteString($data);
        $sent = socket_write($this->socket, $data, strlen($data));
        if($sent === false) {
            mbus_utils::mylog( "socket_write() failed. Reason: ($result) " . socket_strerror(socket_last_error($this->socket)) . "");
            return false;
        }
        return true;
    }

    /**
     * Receive data
     */
    private function receiveData(&$data) {
        mbus_utils::mylog( "Receive response...");
        if (false === ($bytes = socket_recv($this->socket, $data, 2048, MSG_WAITALL))) {
            mbus_utils::mylog( "socket_recv() failed; reason: " . socket_strerror(socket_last_error($this->socket)) . "");
            return false;
        }
        //mbus_utils::mylog( "Read $bytes bytes from socket_recv().");
        //mbus_utils::outputByteString($data);
        return true;
    }

    /**
     * Create socket.
     */
    private function connect() {
        mbus_utils::mylog( "PHP client that connects to Cyble meter head via mbus over TCP/IP.");

        /**
         * Create a TCP/IP socket.
         */
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->socket === false) {
            mbus_utils::mylog( "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "");
            return false;
        } else {
            mbus_utils::mylog( "Socket Created.");
        }

        /**
         * Set Timeout
         */
        $timeout = array('sec'=>2,'usec'=>100000);
        if (!socket_set_option($this->socket,SOL_SOCKET,SO_RCVTIMEO,$timeout)) {
            mbus_utils::mylog( 'Unable to set option on socket: '. socket_strerror(socket_last_error()) . PHP_EOL);
            return false;
        }
        //var_dump(socket_get_option($this->socket,SOL_SOCKET,SO_RCVTIMEO));

        /**
         * Connect to the socket
         */
        mbus_utils::mylog( "Attempting to connect to '$this->address' on port '$this->port'...");
        $result = socket_connect($this->socket, $this->address, $this->port);
        if ($result === false) {
            mbus_utils::mylog( "socket_connect() failed. Reason: ($result) " . socket_strerror(socket_last_error($this->socket)) . "");
            return false;
        }
        mbus_utils::mylog( "Socket connected.");
        return true;
    }

    /**
     * Close the socket
     */
    private function disconnect($socket) {
        mbus_utils::mylog( "Closing socket...");
        //sleep(1);//wait remote host
        socket_shutdown($socket, 1);//remote host yet can read
        usleep(500);//wait remote host
        socket_shutdown($socket, 0);//close reading
        //usleep(500);//wait remote host
        socket_close($socket);//finaly we can free resource
        mbus_utils::mylog( "Socket Closed.");

        return true;
    }

}

//echo "\n\nStart\n\n";
//$mbusClient = new mbus_client();
//$mbusClient->run();
//echo "\n\nFinshed.\n\n";
