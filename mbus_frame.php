<?php

/**
 * MBus is based on IEC standards.
 *  http://en.wikipedia.org/wiki/International_Electrotechnical_Commission
 *
 * Of which there are a quite a few.
 *  http://en.wikipedia.org/wiki/List_of_IEC_standards
 *
 * A sample mbus frame.
 *
 *   LONG: size = N >= 9 byte
 *
 *       byte1: start1  = 0x68
 *       byte2: length1 = ...
 *       byte3: length2 = ...
 *       byte4: start2  = 0x68
 *       byte5: control = ...
 *       byte6: address = ...
 *       byte7: ctl.info= ...
 *       byte8: data    = ...
 *             ...     = ...
 *       byteN-1: chksum  = ...
 *       byteN: stop    = 0x16
 *
 */
class mbus_frame {

    private $start1;
    private $length1 = 0;
    private $length2 = 0;
    private $start2;
    private $control;
    private $address;
    private $control_information;

    private $checksum;
    private $stop;

    private $data;  // 252 max

    private $type; // int

    //private $mbus_frame_data;
    private $mbus_frame_configured = false;

    private $dataarray;
    private $dataarray_len;

    /**
     * Frame types
     */
    public static $MBUS_FRAME_TYPE_ANY = 0;
    public static $MBUS_FRAME_TYPE_ACK = 1;
    public static $MBUS_FRAME_TYPE_SHORT = 2;
    public static $MBUS_FRAME_TYPE_CONTROL = 3;
    public static $MBUS_FRAME_TYPE_LONG = 4;

    public static $MBUS_FRAME_ACK_BASE_SIZE       = 1;
    public static $MBUS_FRAME_SHORT_BASE_SIZE     = 5;
    public static $MBUS_FRAME_CONTROL_BASE_SIZE   = 9;
    public static $MBUS_FRAME_LONG_BASE_SIZE      = 9;

    public static $MBUS_FRAME_BASE_SIZE_ACK       = 1;
    public static $MBUS_FRAME_BASE_SIZE_SHORT     = 5;
    public static $MBUS_FRAME_BASE_SIZE_CONTROL   = 9;
    public static $MBUS_FRAME_BASE_SIZE_LONG      = 9;

    public static $MBUS_FRAME_FIXED_SIZE_ACK      = 1;
    public static $MBUS_FRAME_FIXED_SIZE_SHORT    = 5;
    public static $MBUS_FRAME_FIXED_SIZE_CONTROL  = 6;
    public static $MBUS_FRAME_FIXED_SIZE_LONG     = 6;

    /**
     * Frame start/stop bits
     */
    public static $MBUS_FRAME_ACK_START = 0xE5;
    public static $MBUS_FRAME_SHORT_START = 0x10;
    public static $MBUS_FRAME_CONTROL_START = 0x68;
    public static $MBUS_FRAME_LONG_START = 0x68;
    public static $MBUS_FRAME_STOP = 0x16;

    /**
     * Control field
     */
    public static $MBUS_CONTROL_FIELD_DIRECTION    = 0x07;
    public static $MBUS_CONTROL_FIELD_FCB          = 0x06;
    public static $MBUS_CONTROL_FIELD_ACD          = 0x06;
    public static $MBUS_CONTROL_FIELD_FCV          = 0x05;
    public static $MBUS_CONTROL_FIELD_DFC          = 0x05;
    public static $MBUS_CONTROL_FIELD_F3           = 0x04;
    public static $MBUS_CONTROL_FIELD_F2           = 0x03;
    public static $MBUS_CONTROL_FIELD_F1           = 0x02;
    public static $MBUS_CONTROL_FIELD_F0           = 0x01;

    public static $MBUS_CONTROL_MASK_SND_NKE       = 0x40;
    public static $MBUS_CONTROL_MASK_SND_UD        = 0x53;
    public static $MBUS_CONTROL_MASK_REQ_UD2       = 0x5B;
    public static $MBUS_CONTROL_MASK_REQ_UD1       = 0x5A;
    public static $MBUS_CONTROL_MASK_RSP_UD        = 0x08;

    public static $MBUS_CONTROL_MASK_FCB           = 0x20;
    public static $MBUS_CONTROL_MASK_FCV           = 0x10;

    public static $MBUS_CONTROL_MASK_ACD           = 0x20;
    public static $MBUS_CONTROL_MASK_DFC           = 0x10;

    public static $MBUS_CONTROL_MASK_DIR           = 0x40;
    public static $MBUS_CONTROL_MASK_DIR_M2S       = 0x40;
    public static $MBUS_CONTROL_MASK_DIR_S2M       = 0x00;


    /**
     * Stores frame type.
     */
    private $frame_type = "";

    /**
     * Stores general frame data
     */
    private $varframe = array();

    /**
     * Stores Variable Length Data Header.
     */
    private $varheader = array();

    /**
     * Stores Variable Length Data.
     */
    private $varrecords = array();


    /**
     * send a data request packet to from master to slave
     */
    public function get_data_request_frame($address) {

        if ( ! $this->mbus_frame_setup(mbus_frame::$MBUS_FRAME_TYPE_SHORT) ) {
            mbus_utils::mylog("Frame setup failed!");
            return false;
        }

        $this->control  = mbus_frame::$MBUS_CONTROL_MASK_REQ_UD2 | mbus_frame::$MBUS_CONTROL_MASK_DIR_M2S;
        $this->address  = $address;

        if ( ! $this->mbus_frame_pack() ) {
            mbus_utils::mylog("Frame pack failed!");
            return false;
        }


        return mbus_utils::byteArray2byteStr($this->dataarray);
    }

    /**
     * Allocate an M-bus frame data structure and initialize it according to which
     * frame type is requested.
     */
    private function mbus_frame_setup($frame_type) {

        //mbus_utils::mylog("mbus_frame_setup for type: " . $frame_type);
        $this->frame_type = $frame_type;
        switch ($this->frame_type)
        {
            case mbus_frame::$MBUS_FRAME_TYPE_ACK:
                $this->start1 = mbus_frame::$MBUS_FRAME_ACK_START;
                break;

            case mbus_frame::$MBUS_FRAME_TYPE_SHORT:
                $this->start1 = mbus_frame::$MBUS_FRAME_SHORT_START;
                $this->stop   = mbus_frame::$MBUS_FRAME_STOP;
                break;

            case mbus_frame::$MBUS_FRAME_TYPE_CONTROL:
                $this->start1 = mbus_frame::$MBUS_FRAME_CONTROL_START;
                $this->start2 = mbus_frame::$MBUS_FRAME_CONTROL_START;
                $this->length1 = 3;
                $this->length2 = 3;
                $this->stop   = mbus_frame::$MBUS_FRAME_STOP;
                break;

            case mbus_frame::$MBUS_FRAME_TYPE_LONG:
                $this->start1 = mbus_frame::$MBUS_FRAME_LONG_START;
                $this->start2 = mbus_frame::$MBUS_FRAME_LONG_START;
                $this->stop   = mbus_frame::$MBUS_FRAME_STOP;
                break;

            default:
                mbus_utils::mylog("Unhandled frame type!");
                return false;
        }


        return true;
    }

    /**
     * Pack the M-bus frame into a binary string representation that can be sent
     * on the bus. The binary packet format is different for the different types
     * of M-bus frames.
     */
    private function mbus_frame_pack() {

        if ( ! $this->calc_checksum() ) {
            mbus_utils::mylog("Calculate checksum failed.");
            return false;
        }

        switch ($this->frame_type)
        {
            case mbus_frame::$MBUS_FRAME_TYPE_ACK:
                $this->dataarray[0] = $this->start1;
                break;

            case mbus_frame::$MBUS_FRAME_TYPE_SHORT:
                //printf("$this->start1: %d\n", $this->start1);
                //printf("$this->control: %d\n", $this->control);
                //printf("$this->address: %d\n", $this->address);
                //printf("$this->checksum: %d\n", $this->checksum);
                //printf("$this->stop: %d\n", $this->stop);

                $this->dataarray[] = $this->start1;
                $this->dataarray[] = $this->control;
                $this->dataarray[] = $this->address;
                $this->dataarray[] = $this->checksum;
                $this->dataarray[] = $this->stop;
                break;

            case mbus_frame::$MBUS_FRAME_TYPE_CONTROL:

                $this->dataarray[] = $this->start1;
                $this->dataarray[] = $this->length1;
                $this->dataarray[] = $this->length2;
                $this->dataarray[] = $this->start2;

                $this->dataarray[] = $this->control;
                $this->dataarray[] = $this->address;
                $this->dataarray[] = $this->control_information;

                $this->dataarray[] = $this->checksum;
                $this->dataarray[] = $this->stop;
                break;

            case mbus_frame::$MBUS_FRAME_TYPE_LONG:

                mbus_utils::mylog("Packing long frame types is NYI.");
                return false;
                $this->dataarray[] = $this->start1;
                $this->dataarray[] = $this->length1;
                $this->dataarray[] = $this->length2;
                $this->dataarray[] = $this->start2;

                $this->dataarray[] = $this->control;
                $this->dataarray[] = $this->address;
                $this->dataarray[] = $this->control_information;

                // Need to generate data.
                for ($i = 0; $i < $this->data_size; $i++) {
                    $this->dataarray[] = $this->data[i];
                }

                $this->dataarray[] = $this->checksum;
                $this->dataarray[] = $this->stop;
                break;

            default:
                mbus_utils::mylog("Invalid frame type!");
                return false;
        }

        return true;
    }

    /**
     * Caclulate the checksum of the M-Bus frame.
     */
    private function calc_checksum() {

        switch($this->frame_type)
        {
            case mbus_frame::$MBUS_FRAME_TYPE_SHORT:
                $this->checksum = $this->control;
                $this->checksum += $this->address;
                break;

            case mbus_frame::$MBUS_FRAME_TYPE_CONTROL:
                $this->checksum = $this->control;
                $this->checksum += $this->address;
                $this->checksum += $this->control_information;

                //printf("\nMBUS_FRAME_TYPE_CONTROL DataSize [%d]", $this->data_size);
                //printf("\nMBUS_FRAME_TYPE_CONTROL CheckSum [%d]", $this->checksum);

                break;

            case mbus_frame::$MBUS_FRAME_TYPE_LONG:

                $this->checksum = $this->control;
                //printf("%d ", $this->checksum);
                $this->checksum += $this->address;
                //printf("%d ", $this->checksum);
                $this->checksum += $this->control_information;
                //printf("%d ", $this->checksum);

                for ($i = 0; $i < count($this->dataarray); $i++) {
                    $this->checksum += $this->dataarray[$i];
                    //printf("%d ", $this->checksum);
                }

                //printf("\nMBUS_FRAME_TYPE_LONG DataSize [%d]", $this->data_size);
                //printf("\nMBUS_FRAME_TYPE_LONG CheckSum [%d]", $this->checksum);
                break;

            case mbus_frame::$MBUS_FRAME_TYPE_ACK:
            default:
                $this->checksum = 0;
        }

        return true;
    }

    /**
     * Entry point, detect message type and direct to appropriate function.
     */
    public function parse(&$data) {

        $this->data = $data;

        $this->dataarray = mbus_utils::byteStr2byteArray($this->data);
        $this->dataarray_len = count($this->dataarray);

        $this->varframe['DataLength'] = $this->dataarray_len;
        if ( $this->dataarray_len == 0 ) {
            mbus_utils::mylog("No data to parse!");
            return false;
        }

        if ( dechex($this->dataarray[0]) == mbus_defs::$MBUS_FRAME_LONG_START ) {
            // $this->varframe['FrameType'] = "Long or Control Frame Detected.";
            if ( !$this->parseLongFrameTypeHeader() ) {
                mbus_utils::mylog("Parse long frame type failed.");
                return false;
            }

            if ( !$this->parseFrameData() ) {
                mbus_utils::mylog("Parse parse internal data for long frame type failed.");
                return false;
            }

        } else {
            mbus_utils::mylog("Unhandled Frame Detected.");
            return false;
        }

        $this->mbus_frame_configured = true;
        return true;
    }

    /**
     * Returns the frame or false (use === to check return value)
     */
    public function getFrame() {
        if ( $this->mbus_frame_configured ) {
            return $this->mbus_frame;
        }
        return false;
    }
    /**
     * Parse an mbus "long" frame type.
     * Start1               [0]     \x68
     * Length1              [1]     \x56
     * Length2              [2]     \x56
     * Start2               [3]     \x68
     * Control              [4]     \x08
     * Address              [5]     \x06
     * Control_Information  [6]     \x72
     * ...
     * Checksum             [N-1]   \x97
     * StopByte             [N]     \x16
     */
    private function parseLongFrameTypeHeader() {

        if ($this->dataarray_len < 3) {
            mbus_utils::mylog("Got a valid long/control packet start, but we need data to determine the length!");
            return false;
        }

        $this->start1 = $this->dataarray[0];
        $this->length1 = $this->dataarray[1];
        $this->length2 = $this->dataarray[2];

        if ( $this->length1 != $this->length2) {
            mbus_utils::mylog("Not a valid M-bus frame.");
            return false;
        }

        if ($this->dataarray_len < (mbus_defs::$MBUS_FRAME_FIXED_SIZE_LONG + $this->length1)) {
            mbus_utils::mylog("Length of packet incorrect, we need more data!");
            return false;
        }

        $this->start2   = $this->dataarray[3];
        $this->control  = $this->dataarray[4];
        $this->address  = $this->dataarray[5];
        $this->control_information = $this->dataarray[6];
        $this->checksum = $this->dataarray[$this->dataarray_len-2];
        $this->stop     = $this->dataarray[$this->dataarray_len-1];

        if ($this->dataarray_len == 7) {
            $frame_type = mbus_defs::$MBUS_FRAME_TYPE_CONTROL;
            mbus_utils::mylog("NYI - Frame type is control - Very similar to long.");
            $this->varframe['FrameType'] = "NYI - Frame type is control - Very similar to long.";
            return false;
        } else {
            $frame_type = mbus_defs::$MBUS_FRAME_TYPE_LONG;
            //mbus_utils::mylog("Frame type is long.");
            $this->varframe['FrameType'] = "Frame type is long.";
        }

        /**
         * Calcuate checksum
         */
        $calculated_checksum = $this->control;
        $calculated_checksum += $this->address;
        $calculated_checksum += $this->control_information;

        for ($i = 7; $i < $this->dataarray_len - 2; $i++) {
            $calculated_checksum += $this->dataarray[$i];
            $calculated_checksum = $calculated_checksum % 256;
        }

        if ( $this->checksum != $calculated_checksum ) {
            mbus_utils::mylog("Checksums do not match!!");
            return false;
        }

        /**
         * Do some more checks.
         */
        if(dechex($this->start1) != mbus_defs::$MBUS_FRAME_CONTROL_START) {
            mbus_utils::mylog("Invalid start1 frame code.");
            mbus_utils::mylog("frame_start1 [" . $this->start1 . "] != " . mbus_defs::$MBUS_FRAME_CONTROL_START);
            return false;
        }

        if(dechex($this->start2) != mbus_defs::$MBUS_FRAME_CONTROL_START) {
            mbus_utils::mylog("Invalid start2 frame code.");
            return false;
        }
        if($this->length1 != $this->length2) {
            mbus_utils::mylog("Frame 1 and 2 must be equal.");
            return false;
        }

        /**
         * Data length is the array length minus 6.
         * It is 6 because of the first four bytes (start1, length1, start2 and length2) and
         * the last two bytes (checksum and stop byte) are not counted.
         */
        if($this->length1 != ($this->dataarray_len - 6)) {
            mbus_utils::mylog("Frame 1 length [" . dechex($this->length1) . "] must be data size [" . $this->dataarray_len . "] - 6.");
            return false;
        }

        //mbus_utils::mylog("Successfully parsed header data");
        return true;
    }

    /**
     * Frame data can be either fixed or variable.
     *
     * $data[6] = 0x72|0x76 = Variable Length Data Structure
     * $data[6] = 0x73|0x77 = Fixed Length Data Structure
     */
    private function parseFrameData() {

        switch (dechex($this->control_information)) {
            case mbus_defs::$MBUS_CONTROL_INFO_RESP_VARIABLE: // 0x72
                $this->type = mbus_defs::$MBUS_DATA_TYPE_VARIABLE;
                //mbus_utils::mylog("Data type is variable. See: 6.3 Variable Data Structure");
                if ( ! $this->getSlaveInformation() ) {
                    mbus_utils::mylog("Failed to get slave information!");
                    return false;
                }

                return $this->parseVariableData();

            break;

            case mbus_defs::$MBUS_CONTROL_INFO_RESP_FIXED: // 0x73
                $this->type = mbus_defs::$MBUS_DATA_TYPE_FIXED;
                mbus_utils::mylog("NYI - Data type is fixed. See: 6.2 Fixed Data Structure");
                return false;
                // return mbus_data_fixed_parse(frame, &(data->data_fix));

            break;

            default:
                mbus_utils::mylog("Could not determine frame data type!");
                return false;
        }

        return true;
    }


    /**
     *  6.3.1 Variable 'Fixed' Data Header
     *
     *  $data[7 - 18]
     *  |=============================================================================|
     *  | Ident. Nr.   | Manufr. | Version | Medium | Access No. | Status | Signature |
     *  | 4 Byte (BCD) | 2 Byte  | 1 Byte  | 1 Byte | 1 Byte     | 1 Byte | 2 Byte    |
     *  |=============================================================================|
     *
     *  Ident.Nr    [7-10]  \x86\x78\x01\x10
     *  Manufr.     [11-12] \x77\x04
     *  Version     [13]    \x14
     *  Medium      [14]    \x07
     *  Access No.  [15]    \xad
     *  Status      [16]    \x00
     *  Signature   [17-18] \x00\x00
     */
    private function getSlaveInformation() {
        /**
         * Identity Number
         * Byte [7-10]
         * Translate 4 Byte BCD.
         * http://en.wikipedia.org/wiki/Binary-coded_decimal
         */
        $this->varheader['Id'] .= (($this->dataarray[10] & 0xF0) >> 4) . ($this->dataarray[10] & 0x0F);
        $this->varheader['Id'] .= (($this->dataarray[9] & 0xF0) >> 4) . ($this->dataarray[9] & 0x0F);
        $this->varheader['Id'] .= (($this->dataarray[8] & 0xF0) >> 4) . ($this->dataarray[8] & 0x0F);
        $this->varheader['Id'] .= (($this->dataarray[7] & 0xF0) >> 4) . ($this->dataarray[7] & 0x0F);

        /**
         * Manufacturer
         * Byte [11-12]
         * Ascii encoding using IEC 61107.
         * http://en.wikipedia.org/wiki/Smart_meter
         */
        $manByte1PlusByte2 = $this->dataarray[11] + ($this->dataarray[12] << 8);
        $this->varheader['Manufacturer'] = chr(($manByte1PlusByte2 >> 10 & 0x001F) + 64) . chr(($manByte1PlusByte2 >> 5 & 0x001F) + 64) . chr(($manByte1PlusByte2 & 0x001F) + 64);

        /**
         * Version
         * Byte [13]
         */
        $this->varheader['Version'] = $this->dataarray[13];

        /**
         * Medium
         * Byte [14]
         */
        $this->varheader['Medium'] = mbus_utils::getMedium(dechex($this->dataarray[14]));

        /**
         * Access No.
         * Byte [15]
         */
        $this->varheader['AccessNumber'] = $this->dataarray[15];

        /**
         * Status
         * Byte [16]
         */
        $this->varheader['Status'] = $this->dataarray[16];

        /**
         * Signature - Reserved For Future
         * Byte [17]
         */
        $this->varheader['Signature'] = $this->dataarray[17];


        return true;
    }

    /*
        6.3.2 Variable Data Blocks

        First DIF   [19]    \x0c
        \x78\x86\x78\x01\x10\x0d\x7c\x08\x44
        \x49\x20\x2e\x74\x73\x75\x63\x0a\x30\x30\x30\x30\x30\x30\x30\x30\x30\x30\x04\x6d\x08\x09\x76\x1a\x02\x7c\x09\x65\x6d
        \x69\x74\x20\x2e\x74\x61\x62\x8b\x0f\x04\x13\x01\x00\x00\x00\x04\x93\x7f\x00\x00\x00\x00\x44\x13\x01\x00\x00\x00\x0f
        \x00\x02\x1f


    Documentation is under heading Variable Data Structure in section 6.3 of specification.


    |========|====================|========|========================|===========|
    | DIF    | DIFE               | VIF    | VIFE                   | Data      |
    | 1 Byte | 0-10 (1 Byte each) | 1 Byte | 0-10 (1 Byte each)     | 0-N Byte  |
    |========|====================|========|========================|===========|
    | Data Information Block (DIB)| Value Information Block (VIB)   |
    |=============================|=================================|
    |                 Data Record Header   DRH                      |
    |===============================================================|

    DIF - Data Information Format
    |========================================================|
    | Bit 7      |   6     |  5   4    |  3    2    1    0   |
    | Extension  | LSB of  |  Function |  Data Field:        |
    | Bit        | storage |  Field    |  Length and coding  |
    |            | number  |           |  of data            |
    |========================================================|

    DIFE - Data Information Format Extension
    |========================================================|
    | Bit 7      |   6     |  5   4    |  3    2    1    0   |
    | Extension  | (Device)|  Tariff   |  Storage Number     |
    | Bit        | Unit    |           |                     |
    |========================================================|

    VIF - Value Information Field
    |========================================================|
    | Bit 7      |   6        5   4       3    2    1    0   |
    | Extension  | Unit and Multiplier (value)               |
    | Bit        |                                           |
    |========================================================|

    */
    private function parseVariableData() {

        $i = 19;
        $record_num = 0;
        //mbus_utils::mylog("Read and parse variable data blocks from dataarray[$i-" . ($this->dataarray_len - 2) . "]");

        while ($i < $this->dataarray_len - 2) {

            $this->varrecords[$record_num]["general"]["StartBytePosition"] = $i;

            $dif = $this->dataarray[$i];
            $record_data_len = $dif & 0x07;

            $this->varrecords[$record_num]["dif"]["Data"] = dechex($dif);
            $this->varrecords[$record_num]["dif"]["DataLength"] = $record_data_len;
            $this->varrecords[$record_num]["dif"]["DataType"] = mbus_utils::getDataFieldType($dif);
            $this->varrecords[$record_num]["dif"]["ExtensionBitSet"] = ($dife & 0x80) ? "Yes" : "No";
            $this->varrecords[$record_num]["dif"]["StorageNumLSB"] = ($dife & 0x40);


            /**
             * The manufacturer data header (MDH) is made up by the character 0Fh or 1Fh and indicates the beginning of the manufacturer
             * specific part of the user data and should be omitted, if there is no manufacturer specific data.
             */
            if (($dif & 0xFF) == 0x0F || ($dif & 0xFF) == 0x1F) {
                if (($dif & 0xFF) == 0x1F) {
                    mbus_utils::mylog("0x1F indicates manufacturer specific part of the user data and more data to follow...");
                } else {
                    // mbus_utils::mylog("0x0F indicates  manufacturer specific part of the user data and no more data.");
                }

                $this->varrecords[$record_num]["dif"]["data_type"] = "Manufacturer Data";

                $i++;
                //mbus_utils::mylog("Just copy the remaining data as it is vendor specific");
                while ($i < $this->dataarray_len - 2) {
                    $this->varrecords[$record_num]["data"][] = $this->dataarray[$i];
                    $i++; // Increment byte pointer.
                }

                $this->varrecords[$record_num]["general"]["EndBytePosition"] = $i-1;
                continue;
            }


            // DIFE
            // If bit 8 is set then this means there is another DIFE to come.
            $dife_count = 1;
            while (($this->dataarray[$i] & 0x80) == 0x80) {
                //mbus_utils::mylog("Found DIF extension " . $i);
                $i++; // Increment byte pointer.
                $dife = $this->dataarray[$i];

                $this->varrecords[$record_num]["dife"][$dife_count]["Data"] = dechex($dife);
                $this->varrecords[$record_num]["dife"][$dife_count]["ExtensionBitSet"] = ($dife & 0x80) ? "Yes" : "No";
                $this->varrecords[$record_num]["dife"][$dife_count]["Unit"] = ($dife & 0x40);
                $this->varrecords[$record_num]["dife"][$dife_count]["Tariff"] = ($dife & 0x30);
                $this->varrecords[$record_num]["dife"][$dife_count]["storage_num_msb"] = dechex(($dife & 0x0F));
                $dife_count++;
            }

            $i++; // Increment byte pointer.

            // VIF (Value Information Block)
            $vif = $this->dataarray[$i];
            $this->varrecords[$record_num]['vif']['Data'] = dechex($vif);
            $this->varrecords[$record_num]["vif"]["ExtensionBitSet"] = ($vif & 0x80) ? "Yes" : "No";
            $this->varrecords[$record_num]["vif"]["UnitAndMultiplier"] = dechex(($vif & 0x7F));

            // VIFE
            //mbus_utils::mylog("VIF extension value [" . dechex(($this->dataarray[$i]) & 0x80) . "]");
            $vife_count = 1;
            while (($this->dataarray[$i] & 0x80) == 0x80) {
                mbus_utils::mylog("Found VIF extension " . $i);
                $this->varrecords[$record_num]['vife'][$vife_count]['data'] = dechex($this->dataarray[$i+1]);
                $vife_count++;
                $i++; // Increment byte pointer.
            }


            $i++; // Increment byte pointer to first byte of real data.

            // If Variable Length data field, then need to determine the length.
            if(($dif & 0x0D) == 0x0D) {
                // mbus_utils::mylog("Calculate data length from the first byte of actual data.");
                if($this->dataarray[$i] <= 0xBF) {
                    $record_data_len = $this->dataarray[$i++];
                    $this->varrecords[$record_num]["dif"]["data_type"] = 'Variable ASCII String';
                } else if($this->dataarray[$i] >= 0xC0 && $this->dataarray[$i] <= 0xCF) {
                    $record_data_len = ($this->dataarray[$i++] - 0xC0) * 2;
                    $this->varrecords[$record_num]["dif"]["data_type"] = 'Variable Postive BCD';
                } else if($this->dataarray[$i] >= 0xD0 && $this->dataarray[$i] <= 0xDF) {
                    $record_data_len = ($this->dataarray[$i++] - 0xD0) * 2;
                    $this->varrecords[$record_num]["dif"]["data_type"] = 'Variable Negative BCD';
                } else if($this->dataarray[$i] >= 0xE0 && $this->dataarray[$i] <= 0xEF) {
                    $record_data_len = $this->dataarray[$i++] - 0xE0;
                    $this->varrecords[$record_num]["dif"]["data_type"] = 'Variable Binary';
                } else if($this->dataarray[$i] >= 0xF0 && $this->dataarray[$i] <= 0xFA) {
                    $record_data_len = $this->dataarray[$i++] - 0xF0;
                    $this->varrecords[$record_num]["dif"]["data_type"] = 'Variable Floating Point';
                }
            }

            $this->varrecords[$record_num]["dif"]["DataLength"] = $record_data_len;

            //mbus_utils::mylog("Copy [" . $record_data_len . "] bytes of data.");

            for ($j = 0; $j < $record_data_len; $j++) {
                $this->varrecords[$record_num]["data"][] = $this->dataarray[$i];
                $i++;
            }

            $this->varrecords[$record_num]["dif_data_rec_len"] = $record_data_len;

            $this->varrecords[$record_num]['dif']['Function'] = mbus_utils::getFunctionField(($dif & 0x30) >> 4);
            $this->varrecords[$record_num]['vif']['Unit'] = mbus_utils::vif_unit_lookup($vif);
            $this->varrecords[$record_num]['Value'] = mbus_utils::getValue($dif, $this->varrecords[$record_num]["data"], $record_data_len);

            $this->varrecords[$record_num]["general"]["EndBytePosition"] = $i-1;
            $record_num++;

        }

        return true;
    }


    public function PrettyPrint() {
         /**
         * Output the variable header key value pairs.
         */
        mbus_utils::mylog("========================================================");
        mbus_utils::mylog("----               Frame Information                ----");
        mbus_utils::mylog("--------------------------------------------------------");
        foreach ($this->varframe as $key => $value) {
            mbus_utils::mylog(str_pad(" $key ", 30) . $value);
        }
        mbus_utils::mylog(str_pad(" Data ", 30) . "");
        mbus_utils::outputByteString($this->data);

        mbus_utils::mylog("--------------------------------------------------------");

        /**
         * Output the variable header key value pairs.
         */
        mbus_utils::mylog("========================================================");
        mbus_utils::mylog("----               Slave Information                ----");
        mbus_utils::mylog("--------------------------------------------------------");
        foreach ($this->varheader as $key => $value) {
            mbus_utils::mylog(str_pad(" $key ", 30) . $value);
        }
        mbus_utils::mylog("--------------------------------------------------------");


        /**
         * Output the variable records key value pairs.
         */
        mbus_utils::mylog("========================================================");
        mbus_utils::mylog("----               Record Information               ----");
        foreach ($this->varrecords as $key1 => $record) {
            mbus_utils::mylog("--------------------------------------------------------");
            mbus_utils::mylog(str_pad("Record Number ", 30) . $key1);

            $difgeneralarray = $record['general'];
            foreach ($difgeneralarray as $dif_general_key => $dif_general_value) {
                mbus_utils::mylog(str_pad(" $dif_general_key ", 30) . $dif_general_value);
            }

            $sbp = $record['general']['StartBytePosition'];
            mbus_utils::mylog(str_pad(" StartByte ", 30) . dechex($this->dataarray[$sbp]));
            $ebp = $record['general']['EndBytePosition'];
            mbus_utils::mylog(str_pad(" EndByte ", 30) . dechex($this->dataarray[$ebp]));


            mbus_utils::mylog("DIF found");
            $difarray = $record['dif'];
            foreach ($difarray as $dif_key => $dif_value) {
                mbus_utils::mylog(str_pad(" $dif_key ", 30) . $dif_value);
            }

            $difearray = $record["dife"];
            if ( isset($difearray) ) {
                mbus_utils::mylog("DIFe found");
                foreach ($difearray as $dife_key => $dife_value) {
                    foreach ($dife_value as $dife_key2 => $dife_value2) {
                        mbus_utils::mylog(str_pad(" $dife_key2 ", 30) . $dife_value2);
                    }
                }
            } else {
                mbus_utils::mylog("No DIFe");
            }

            $vifarray = $record["vif"];
            if ( isset($vifarray) ) {
                mbus_utils::mylog("VIF found");
                foreach ($vifarray as $vif_key => $vif_value) {
                    mbus_utils::mylog(str_pad(" $vif_key ", 30) . $vif_value);
                }
            } else {
                mbus_utils::mylog("No VIF");
            }

            $vifearray = $record["vife"];
            if ( isset($vifearray) ) {
                mbus_utils::mylog("VIFe found");
                foreach ($vifearray as $vife_key => $vife_value) {
                    foreach ($vife_value as $vife_key2 => $vife_value2) {
                        mbus_utils::mylog(str_pad(" $vife_key2 ", 30) . $vife_value2);
                    }
                }
            } else {
                mbus_utils::mylog("No VIFe");
            }

            mbus_utils::mylog("Data");
            $value = $record['Value'];
            if ( isset($value) ) {
                mbus_utils::mylog(str_pad(" Value ", 30) . $value);
            } else {
                mbus_utils::mylog(" No Value");
            }

            $dataarray = $record['data'];
            if ( isset($dataarray) ) {
                $data = "";
                foreach ($dataarray as $data_key => $data_value) {
                    $data .= "[" . dechex($data_value) . "]";
                }
                mbus_utils::mylog(str_pad(" Raw Bytes ", 30) . strtoupper($data));
            } else {
                mbus_utils::mylog(str_pad(" Raw Bytes ", 30) . "No data");
            }
        }

        mbus_utils::mylog("--------------------------------------------------------");


    }

}
