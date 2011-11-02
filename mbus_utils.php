<?php

class mbus_utils {

    private static $log_to_terminal = true;
    private static $log_turned_on = true;

    // View Browser in Source mode to get nice formatting.
    public static function logToTerminal($toTerminal = true) {
        mbus_utils::$log_to_terminal = $toTerminal;
    }
    public static function logToBrowser($toTerminal = false) {
        mbus_utils::$log_to_terminal = $toTerminal;
    }
    public static function turnLogOff() {
        mbus_utils::$log_turned_on = false;
    }

    public static function mylog($message) {
        if ( mbus_utils::$log_turned_on ) {
            if ( mbus_utils::$log_to_terminal ) {
                // Use \n as line delimiter for a terminal
                echo "\n" . date("c") . " - " . $message;
            } else {
                // Use <br> as a line delimieter for web browser.
                echo "<br>" . date("c") . " - " . $message;
            }
        }
    }

    /**
     * IMPORTANT: Values are stored in the array as decimal.
     *            E.g. Hex 0x68 is stored and returned as 104.
     *            Use isDecEqualToHex to compare values or see an example.
     */
    public static function byteStr2byteArray($s) {
            return array_slice(unpack("C*", "\0".$s), 1);
    }

    /**
     * Can be used to compare values returned from the array in byteStr2byteArray to a hex value.
     * Usage:
     *   mbus_utils::isDecEqualToHex("104", "68"); // Returns true.
     */
    public static function isDecEqualToHex($decbyte, $test) {
        mbus_utils::mylog("Comparing [" . dechex($decbyte) . "] with [" . $test . "]");
        return (dechex($decbyte) == $test);
    }

    public static function byteArray2byteStr(array $t) {
            return call_user_func_array(pack, array_merge(array("C*"), $t));
    }
    public static function lsbStr2ushortArray($s) {
            return array_slice(unpack("v*", "\0\0".$s), 1);
    }
    public static function ushortArray2lsbStr(array $t) {
            return call_user_func_array(pack, array_merge(array("v*"), $t));
    }
    public static function lsbStr2ulongArray($s) {
            return array_slice(unpack("V*", "\0\0\0\0".$s), 1);
    }
    public static function ulongArray2lsbStr(array $t) {
            return call_user_func_array(pack, array_merge(array("V*"), $t));
    }
    public static function outputByteString($byteString) {
        //echo "\n\n[" . mbus_utils::returnByteString($byteString) . "]\n";
        mbus_utils::mylog(mbus_utils::returnByteString2($byteString));
    }

    public static function returnByteString($byteString) {
        $out = "";
        $byteArr = mbus_utils::byteStr2byteArray($byteString);
        foreach( $byteArr as $byte ) {
            //$out .= "\\x";

            if ( intval($byte) < 16 ) {
                $out .= "0";
            }
            $out .= dechex($byte) . " ";
        }
        return strtoupper($out);
    }

    public static function returnByteString2($byteString) {
        $out = "\n" . str_pad("", 30) . "       0  1  2  3  4  5  6  7  8  9";
        $out .= "\n" . str_pad("", 30) . "       -----------------------------";
        $out .= "\n" . str_pad("", 30) . " 0  |  ";
        $byteArr = mbus_utils::byteStr2byteArray($byteString);
        $i = 1;
        foreach( $byteArr as $key => $byte ) {
            //$out .= "\\x";

            //$out .= "k" . $key . " ";
            if ( intval($byte) < 16 ) {
                $out .= "0";
            }
            $out .= dechex($byte) . " ";
            if ( $i % 10 == 0 ) {

                $out .= "\n" . str_pad("", 30) . ($i) . "  |  ";
            }
            $i++;
        }

        return strtoupper($out);
    }

    public static function getMedium($medium)
    {
        switch ($medium)
        {
            case mbus_defs::$MBUS_VARIABLE_DATA_MEDIUM_OTHER:
                return "Other";
                break;

            case mbus_defs::$MBUS_VARIABLE_DATA_MEDIUM_OIL:
                return "Oil";
                break;

            case mbus_defs::$MBUS_VARIABLE_DATA_MEDIUM_ELECTRICITY:
                return "Electricity";
                break;

            case mbus_defs::$MBUS_VARIABLE_DATA_MEDIUM_GAS:
                return "Gas";
                break;

            case mbus_defs::$MBUS_VARIABLE_DATA_MEDIUM_HEAT:
                return "Heat";
                break;

            case mbus_defs::$MBUS_VARIABLE_DATA_MEDIUM_STEAM:
                return "Steam";
                break;

            case mbus_defs::$MBUS_VARIABLE_DATA_MEDIUM_HOT_WATER:
                return "Hot water";
                break;

            case mbus_defs::$MBUS_VARIABLE_DATA_MEDIUM_WATER:
                return "Water";
                break;

            case mbus_defs::$MBUS_VARIABLE_DATA_MEDIUM_HEAT_COST:
                return "Heat Cost Allocator";
                break;

            case mbus_defs::$MBUS_VARIABLE_DATA_MEDIUM_COMPR_AIR:
                return "Compressed Air";
                break;

            case mbus_defs::$MBUS_VARIABLE_DATA_MEDIUM_COOL_OUT:
                return "Cooling load meter: Outlet";
                break;

            case mbus_defs::$MBUS_VARIABLE_DATA_MEDIUM_COOL_IN:
                return "Cooling load meter: Inlet";
                break;

            case mbus_defs::$MBUS_VARIABLE_DATA_MEDIUM_BUS:
                return "Bus/System";
                break;

            case mbus_defs::$MBUS_VARIABLE_DATA_MEDIUM_COLD_WATER:
                return "Cold water";
                break;

            case mbus_defs::$MBUS_VARIABLE_DATA_MEDIUM_DUAL_WATER:
                return "Dual water";
                break;

            case mbus_defs::$MBUS_VARIABLE_DATA_MEDIUM_PRESSURE:
                return "Pressure";
                break;

            case mbus_defs::$MBUS_VARIABLE_DATA_MEDIUM_ADC:
                return "A/D Converter";
                break;

            case 0x0C:
                return "Heat (Volume measured at flow temperature: inlet)";
                break;

            case 0x20: // - 0xFF
                return "Reserved";
                break;


            // add more ...
            default:
                return "Unknown medium " . $medium;
                break;
        }

        return buff;
    }

    /**
     * Pass in the decimal value and returns a string containing the function description.
     *
     * 6.3 Variable Data Structure
     *
     * |=======================================|
     * | Dec | Bin  | Description              |
     * | 0   | 00b  | Instantaneous value      |
     * | 2   | 10b  | Minimum value            |
     * | 1   | 01b  | Maximum value            |
     * | 3   | 11b  | Value during error state |
     * |=======================================|
     */
    public static function getFunctionField($decimal)
    {
        switch ($decimal)
        {
            case 0:
                return "Instantaneous value";
                break;

            case 1:
                return "Maximum value";
                break;

            case 2:
                return "Minimum value";
                break;

            case 3:
                return "Value during error state";
                break;

            default:
                return "unknown";
        }
    }

    /**
     * Lookup the unit from the VIB (VIF or VIFE)
     */
    public static function vib_unit_lookup($vib, $vib_nvife, $vif) {

        if ($vif == 0xFD) // first type of VIF extention: see table 8.4.4
        {
            if ($vib_nvife == 0)
            {
                return "Missing VIF extension";
            }
            else if ($vib_nvife == 0x10)
            {
                // VIFE = E001 0000 Customer location
                return "Customer location";
            }
            else if ($vib_nvife == 0x0C)
            {
                // E000 1100 Model / Version
                return "Model / Version";
            }
            else if ($vib_nvife == 0x11)
            {
                // VIFE = E001 0001 Customer
                return "Customer";
            }
            else if ($vib_nvife == 0x9)
            {
                // VIFE = E001 0110 Password
                return "Password";
            }
            else if ($vib_nvife == 0x0b)
            {
                // VIFE = E000 1011 Parameter set identification
                return "Parameter set identification";
            }
            else if (($vib_nvife & 0x70) == 0x40)
            {
                // VIFE = E100 nnnn 10^(nnnn-9) V
                $n = ($vib_nvife & 0x0F);
                return "10^(". mbus_utils::unit_prefix($n-9) . ") V";
            }
            else if (($vib_nvife & 0x70) == 0x50)
            {
                // VIFE = E101 nnnn 10nnnn-12 A
                $n = ($vib_nvife & 0x0F);
                return "10^(". mbus_utils::unit_prefix($n-12) . ") A";
            }
            else if (($vib_nvife & 0xF0) == 0x70)
            {
                // VIFE = E111 nnn Reserved
                return "Reserved VIF extension";
            }
            else
            {
                return "Unrecongized VIF extension: ". $vib_nvife;
            }
            return "";
        }

        return mbus_utils::vif_unit_lookup($vif); // no extention, use VIF
    }


    /**
     * Look up the unit from a VIF field in the data record.
     *
     * See section 8.4.3  Codes for Value Information Field (VIF) in the M-BUS spec
     */
    public static function vif_unit_lookup($vif)
    {
        //mbus_utils::mylog("********** [" . (0x00+2) . "]");


        $vifNoExt = $vif & 0x7F;
        switch ($vifNoExt) // ignore the extension bit in this selection
        {
            // E000 0nnn Energy 10(nnn-3) W
            case ($vifNoExt >= 0x00 && $vifNoExt <= 0x07):
                $n = ($vif & 0x07) - 3;
                return "Energy (" . mbus_utils::unit_prefix($n) ."Wh)";
                break;

            // 0000 1nnn          Energy       10(nnn)J     (0.001kJ to 10000kJ)
            case ($vifNoExt >= 0x08 && $vifNoExt <= 0x0F):
                $n = ($vif & 0x07);
                return "Energy (" . mbus_utils::unit_prefix($n) . "J)";
                break;

            // E001 1nnn Mass 10(nnn-3) kg 0.001kg to 10000kg
            case ($vifNoExt >= 0x18 && $vifNoExt <= 0x1F):
                $n = ($vif & 0x07);
                return "Mass (" . mbus_utils::unit_prefix($n-3) . "kg)";
                break;

            // E010 1nnn Power 10(nnn-3) W 0.001W to 10000W
            case ($vifNoExt >= 0x28 && $vifNoExt <= 0x2F):
                $n = ($vif & 0x07);
                return "Power (" . mbus_utils::unit_prefix($n-3) . "W)";
                break;

            // E011 0nnn Power 10(nnn) J/h 0.001kJ/h to 10000kJ/h
            case ($vifNoExt >= 0x30 && $vifNoExt <= 0x37):
                $n = ($vif & 0x07);
                return "Power (" . mbus_utils::unit_prefix($n) . "J/h)";
                break;

            // E001 0nnn Volume 10(nnn-6) m3 0.001l to 10000l
            case ($vifNoExt >= 0x10 && $vifNoExt <= 0x17):
                $n = ($vif & 0x07);
                return "Volume (" . mbus_utils::unit_prefix($n-6) . " m^3)";
                break;

            // E011 1nnn Volume Flow 10(nnn-6) m3/h 0.001l/h to 10000l/
            case ($vifNoExt >= 0x38 && $vifNoExt <= 0x3F):
                $n = ($vif & 0x07);
                return "Volume flow (" . mbus_utils::unit_prefix($n-6) . " m^3/h)";
                break;

            // E100 0nnn Volume Flow ext. 10(nnn-7) m3/min 0.0001l/min to 1000l/min
            case ($vifNoExt >= 0x40 && $vifNoExt <= 0x47):
                $n = ($vif & 0x07);
                return "Volume flow (" . mbus_utils::unit_prefix($n-7) . " m^3/min)";
                break;

            // E100 1nnn Volume Flow ext. 10(nnn-9) m3/s 0.001ml/s to 10000ml/
            case ($vifNoExt >= 0x48 && $vifNoExt <= 0x4F):
                $n = ($vif & 0x07);
                return "Volume flow (". mbus_utils::unit_prefix($n-9) . "m^3/s)";
                break;

            // E101 0nnn Mass flow 10(nnn-3) kg/h 0.001kg/h to 10000kg/h
            case ($vifNoExt >= 0x50 && $vifNoExt <= 0x57):
                $n = ($vif & 0x07);
                return "Mass flow (" . mbus_utils::unit_prefix($n-3) . ") kg/h 0.001kg/h to 10000kg/h";
                break;

            // E101 10nn Flow Temperature 10(nn-3) °C 0.001°C to 1°C
            case ($vifNoExt >= 0x58 && $vifNoExt <= 0x5B):
                $n = ($vif & 0x03);
                return "Flow temperature (" . mbus_utils::unit_prefix($n-3) . ") °C 0.001°C to 1°C";
                break;

            // E101 11nn Return Temperature 10(nn-3) °C 0.001°C to 1°C
            case ($vifNoExt >= 0x5C && $vifNoExt <= 0x5F):
                $n = ($vif & 0x03);
                return "Return temperature (" . mbus_utils::unit_prefix($n-3) . ") °C 0.001°C to 1°C";
                break;

            // E110 10nn Pressure 10(nn-3) bar 1mbar to 1000mbar
            case ($vifNoExt >= 0x68 && $vifNoExt <= 0x6B):
                $n = ($vif & 0x03);
                return "Pressure 10(" . mbus_utils::unit_prefix($n-3) . ") bar 1mbar to 1000mbar";
                break;

            // E010 00nn On Time
            // nn = 00 seconds
            // nn = 01 minutes
            // nn = 10   hours
            // nn = 11    days
            // E010 01nn Operating Time coded like OnTime
            case ($vifNoExt >= 0x20 && $vifNoExt <= 0x23):
            case ($vifNoExt >= 0x24 && $vifNoExt <= 0x27):
                {
                    $s = "";

                    if ($vif & 0x4)
                        $s = "Operating time ";
                    else
                        $s = "On time ";

                    switch ($vif & 0x03)
                    {
                        case 0x00:
                            $s .= "(seconds)";
                            break;
                        case 0x01:
                            $s .= "(minutes)";
                            break;
                        case 0x02:
                            $s .= "(hours)";
                            break;
                        case 0x03:
                            $s .= "(days)";
                            break;
                    }
                }
                return $s;
                break;

            // E110 110n Time Point
            // n = 0        date
            // n = 1 time & date
            // data type G
            // data type F
            case ($vifNoExt >= 0x6C && $vifNoExt <= 0x6D):
                if ($vif & 0x1)
                    return "Time Point (time & date)";
                else
                    return "Time Point (date)";

                break;

            // E110 00nn    Temperature Difference   10(nn-3)K   (mK to  K)
            case ($vifNoExt >= 0x60 && $vifNoExt <= 0x63):
                $n = ($vif & 0x03);
                return "Temperature Difference (" . mbus_utils::unit_prefix($n-3) . " deg C)";
                break;

            // E110 01nn External Temperature 10(nn-3) °C 0.001°C to 1°C
            case ($vifNoExt >= 0x64 && $vifNoExt <= 0x67):
                $n = ($vif & 0x03);
                return "External temperature (" . mbus_utils::unit_prefix($n-3) . " deg C)";
                break;

            // E110 1110 Units for H.C.A. dimensionless
            case 0x6E:
                return "Units for H.C.A.";
                break;

            // E110 1111 Reserved
            case 0x6F:
                return "Reserved";
                break;

            // Fabrication No
            case 0x78:
                return "Fabrication number";
                break;

            case 0x7C:
                return "Plain Text";
                break;

            // Manufacturer specific: 7Fh / FF
            case 0x7F:
            case 0xFF:
                return "Manufacturer specific";
                break;

            default:

                return "Unknown (VIF=0x". dechex($vif & 0x7F) . ")";
                break;
        }


        return "";
    }


    /**
     *
     * Lookup the unit description from a VIF field in a data record
     *
     */
    public static function unit_prefix($exp)
    {
        switch ($exp)
        {
            case 0:
                return "";
                break;

            case -3:
                return "m";
                break;

            case -6:
                return "my";
                break;

            case 1:
                return "10 ";
                break;

            case 2:
                return "100 ";
                break;

            case 3:
                return "k";
                break;

            case 4:
                return "10 k";
                break;

            case 5:
                return "100 k";
                break;

            case 6:
                return "M";
                break;

            case 9:
                return "T";
                break;

            default:
                return "10^". $exp;
        }

        return "";
    }

    /**
     *
     * Lookup the unit description from a VIF field in a data record
     *
     */
    public static function getDataFieldType($dif)
    {
        //mbus_utils::mylog("Data field 6.3 - table 5 - binary [" . decbin($record_data_len) . "]");

        switch ($dif & 0x0F) {
            case 0:
                return 'No Data';
            break;
            case 1:
                return '8 Bit Integer';
            break;
            case 2:
                return '16 Bit Integer';
            break;
            case 3:
                return '24 Bit Integer';
            break;
            case 4:
                return '32 Bit Integer';
            break;
            case 5:
                return '32 Bit Real';
            break;
            case 6:
                return '48 Bit Integer';
            break;
            case 7:
                return '64 Bit Integer';
            break;
            case 8:
                return 'Selection for Readout';
            break;
            case 9:
                return '2 Digit BCD';
            break;
            case 10:
                return '4 Digit BCD';
            break;
            case 11:
                return '6 Digit BCD';
            break;
            case 12:
                return '8 Digit BCD';
            break;
            case 13:
                return 'Variable Length';
            break;
            case 14:
                return '12 Digit BCD';
            break;
            case 15:
                return 'Special Functions';
            break;
            default:
                return 'Data Type Not in Spec!!';
        }
    }


    //------------------------------------------------------------------------------
    // Decode data and write to string
    //
    // Data format (for record->data data array)
    //
    // Length in Bit   Code    Meaning           Code      Meaning
    //      0          0000    No data           1000      Selection for Readout
    //      8          0001     8 Bit Integer    1001      2 digit BCD
    //     16          0010    16 Bit Integer    1010      4 digit BCD
    //     24          0011    24 Bit Integer    1011      6 digit BCD
    //     32          0100    32 Bit Integer    1100      8 digit BCD
    //   32 / N        0101    32 Bit Real       1101      variable length
    //     48          0110    48 Bit Integer    1110      12 digit BCD
    //     64          0111    64 Bit Integer    1111      Special Functions
    //
    // The Code is stored in record->drh.dib.dif
    //
    ///
    /// Return a string containing the data
    ///
    // Source: MBDOC48.PDF
    //
    //------------------------------------------------------------------------------
    public static function getValue($dif, $vif, $data) {

        if ( $vif != 0x00 ) {
            $vifNoExt = $vif & 0x7F;
            switch ($vifNoExt) // ignore the extension bit in this selection
            {
                // E110 110n Time Point
                // n = 0        date
                // n = 1 time & date
                // data type G
                // data type F
                case ($vifNoExt >= 0x6C && $vifNoExt <= 0x6D):
                    if ($vif & 0x1) {
                        // Data length should be 4 bytes.
                        if ( count($data) < 4 ) {
                            return "Invalid number of bytes to determine date and time!";
                        }
                        return mbus_utils::data_date_time_decode($data);
                    } else {
                        return "NYI - Time Point (date)";
                    }
                    break;
                // This is the units of the value. Not the actual value! Still need to get value in this record.
                // Tricky eh, see 6.3 plain text VIF
                case ($vifNoExt == 0x7C):
                    if($data_length <= 0xBF) {
                        return mbus_utils::data_str_decode($data);
                    }
                    break;
            }
        }

        switch ($dif & 0x0F)
        {
            case 0x00: // no data
                return "No Data";
                break;

            case 0x01: // 1 byte integer (8 bit)
            case 0x02: // 2 byte integer (16 bit)
            case 0x03: // 3 byte integer (24 bit)
            case 0x04: // 4 byte integer (32 bit)
            case 0x06: // 6 byte integer (48 bit)
            case 0x07: // 8 byte integer (64 bit)
                return mbus_utils::data_int_decode($data);
                break;

            case 0x09: // 2 digit BCD (8 bit)
            case 0x0A: // 4 digit BCD (16 bit)
            case 0x0B: // 6 digit BCD (24 bit)
            case 0x0C: // 8 digit BCD (32 bit)
            case 0x0E: // 12 digit BCD
                return mbus_utils::data_bcd_decode($data);
                break;

            case 0x0E: // Special Functions
                return "Special Functions";
                break;

            case 0x0D: // variable length ASCII
                if($data_length <= 0xBF) {
                    return mbus_utils::data_str_decode($data);
                    break;
                } // FALLTHROUGH
            default:

                return "Unknown DIF (0x" . dechex($dif) . ")";
                break;
        }

        return "";
    }

    /**
     * Decode Date Time data
     */
    public static function data_date_time_decode($data) {
        $minute = $data[0] & 0x3F;
        $hour = $data[1] & 0x1F;
        $day = $data[2] & 0x1F;
        $month = $data[3] & 0x0F;
        $year = bindec(decbin(($data[3] & 0xF0) >> 3) . " " . decbin(($data[2] & 0xE0) >> 5));
        return date("r", mktime($hour, $minute, 0, $month, $day, $year));
    }
    /**
     * Decode Integer data
     */
    public static function data_int_decode($data) {
        $val = 0;
        //mbus_utils::mylog("count(data)" . count($data));
        for ($i = count($data); $i > 0; $i--) {
            //mbus_utils::mylog("data[i-1]: " . dechex($data[$i-1]));
            $val = ($val << 8) + $data[$i-1];
            //mbus_utils::mylog("$val: " . $val);
        }

        return $val;

    }
    /**
     * Decode BCD data
     */
    public static function data_bcd_decode($data) {
        //mbus_utils::mylog("Data field 6.3 - table 5 - binary");
        $val = 0;
        for ($i = count($data); $i > 0; $i--) {
            $val = ($val * 10) + (($data[$i-1]>>4) & 0xF);
            $val = ($val * 10) + ( $data[$i-1]     & 0xF);

        }
        return $val;
    }

    /**
     * Decode ascii data (skip first byte)
     */
    public static function data_str_decode($data) {

        $s = "";
        for ($i = count($data); $i >= 0; $i--) {
            $s .= chr($data[$i]);
        }
        //mbus_utils::mylog("Decode string: " . $s);
        return trim($s);
    }

    /**
     * Returns a hex converted byte in the format 0xNN.
     * Remember that PHP always stores values as decimal.
     */
    public static function ByteToHex($byte) {
        $retval = "0x";
        if ( $byte < 0x0F ) {
            $retval .= "0" . strtoupper(dechex($byte));
        } else {
            $retval .= strtoupper(dechex($byte));
        }
        return $retval . " ";
    }


}
