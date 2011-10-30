<?php

class mbus_defs {

    //------------------------------------------------------------------------------
    // FRAME data types
    //
    public static $MBUS_DATA_TYPE_FIXED    = 1;
    public static $MBUS_DATA_TYPE_VARIABLE = 2;


    //------------------------------------------------------------------------------
    // FRAME types double quoted are in hex
    //
    public static $MBUS_FRAME_TYPE_ANY = "0";
    public static $MBUS_FRAME_TYPE_ACK = "1";
    public static $MBUS_FRAME_TYPE_SHORT = "2";
    public static $MBUS_FRAME_TYPE_CONTROL = "3";
    public static $MBUS_FRAME_TYPE_LONG = "4";

    public static $MBUS_FRAME_ACK_BASE_SIZE = 1;
    public static $MBUS_FRAME_SHORT_BASE_SIZE = 5;
    public static $MBUS_FRAME_CONTROL_BASE_SIZE = 9;
    public static $MBUS_FRAME_LONG_BASE_SIZE = 9;

    public static $MBUS_FRAME_BASE_SIZE_ACK = 1;
    public static $MBUS_FRAME_BASE_SIZE_SHORT = 5;
    public static $MBUS_FRAME_BASE_SIZE_CONTROL = 9;
    public static $MBUS_FRAME_BASE_SIZE_LONG = 9;

    public static $MBUS_FRAME_FIXED_SIZE_ACK = 1;
    public static $MBUS_FRAME_FIXED_SIZE_SHORT = 5;
    public static $MBUS_FRAME_FIXED_SIZE_CONTROL = 6;
    public static $MBUS_FRAME_FIXED_SIZE_LONG = 6;

    //
    // Frame start/stop bits double quoted are all in hex
    //
    public static $MBUS_FRAME_ACK_START = "E5";
    public static $MBUS_FRAME_SHORT_START = "10";
    public static $MBUS_FRAME_CONTROL_START = "68";
    public static $MBUS_FRAME_LONG_START = "68";
    public static $MBUS_FRAME_STOP = "16";

    //
    //
    //
    public static $MBUS_MAX_PRIMARY_SLAVES = 256;

    //
    // Control field double quoted are all in hex
    //
    public static $MBUS_CONTROL_FIELD_DIRECTION = "7";
    public static $MBUS_CONTROL_FIELD_FCB = "6";
    public static $MBUS_CONTROL_FIELD_ACD = "6";
    public static $MBUS_CONTROL_FIELD_FCV = "5";
    public static $MBUS_CONTROL_FIELD_DFC = "5";
    public static $MBUS_CONTROL_FIELD_F3 = "4";
    public static $MBUS_CONTROL_FIELD_F2 = "3";
    public static $MBUS_CONTROL_FIELD_F1 = "2";
    public static $MBUS_CONTROL_FIELD_F0 = "1";

    public static $MBUS_CONTROL_MASK_SND_NKE = "40";
    public static $MBUS_CONTROL_MASK_SND_UD = "53";
    public static $MBUS_CONTROL_MASK_REQ_UD2 = "5B";
    public static $MBUS_CONTROL_MASK_REQ_UD1 = "5A";
    public static $MBUS_CONTROL_MASK_RSP_UD = "08";

    public static $MBUS_CONTROL_MASK_FCB = "20";
    public static $MBUS_CONTROL_MASK_FCV = "10";

    public static $MBUS_CONTROL_MASK_ACD = "20";
    public static $MBUS_CONTROL_MASK_DFC = "10";

    public static $MBUS_CONTROL_MASK_DIR = "40";
    public static $MBUS_CONTROL_MASK_DIR_M2S = "40";
    public static $MBUS_CONTROL_MASK_DIR_S2M = "00";

    //
    // Address field double quoted are all in hex
    //
    public static $MBUS_ADDRESS_BROADCAST_REPLY = "FE";
    public static $MBUS_ADDRESS_BROADCAST_NOREPLY = "FF";
    public static $MBUS_ADDRESS_NETWORK_LAYER = "FD";

    //
    // Control Information field double quoted are all in hex
    //
    //Mode 1 Mode 2 Application Definition in
    // 51h 55h data send EN1434-3
    // 52h 56h selection of slaves Usergroup July  ́93
    // 50h application reset Usergroup March  ́94
    // 54h synronize action suggestion
    // B8h set baudrate to 300 baud Usergroup July  ́93
    // B9h set baudrate to 600 baud Usergroup July  ́93
    // BAh set baudrate to 1200 baud Usergroup July  ́93
    // BBh set baudrate to 2400 baud Usergroup July  ́93
    // BCh set baudrate to 4800 baud Usergroup July  ́93
    // BDh set baudrate to 9600 baud Usergroup July  ́93
    // BEh set baudrate to 19200 baud suggestion
    // BFh set baudrate to 38400 baud suggestion
    // B1h request readout of complete RAM content Techem suggestion
    // B2h send user data (not standardized RAM write) Techem suggestion
    // B3h initialize test calibration mode Usergroup July  ́93
    // B4h EEPROM read Techem suggestion
    // B6h start software test Techem suggestion
    // 90h to 97h codes used for hashing longer recommended

    public static $MBUS_CONTROL_INFO_DATA_SEND = "51";
    public static $MBUS_CONTROL_INFO_DATA_SEND_MSB = "55";
    public static $MBUS_CONTROL_INFO_SELECT_SLAVE = "52";
    public static $MBUS_CONTROL_INFO_SELECT_SLAVE_MSB = "56";
    public static $MBUS_CONTROL_INFO_APPLICATION_RESET = "50";
    public static $MBUS_CONTROL_INFO_SYNC_ACTION = "54";
    public static $MBUS_CONTROL_INFO_SET_BAUDRATE_300 = "B8";
    public static $MBUS_CONTROL_INFO_SET_BAUDRATE_600 = "B9";
    public static $MBUS_CONTROL_INFO_SET_BAUDRATE_1200 = "BA";
    public static $MBUS_CONTROL_INFO_SET_BAUDRATE_2400 = "BB";
    public static $MBUS_CONTROL_INFO_SET_BAUDRATE_4800 = "BC";
    public static $MBUS_CONTROL_INFO_SET_BAUDRATE_9600 = "BD";
    public static $MBUS_CONTROL_INFO_SET_BAUDRATE_19200 = "BE";
    public static $MBUS_CONTROL_INFO_SET_BAUDRATE_38400 = "BF";
    public static $MBUS_CONTROL_INFO_REQUEST_RAM_READ = "B1";
    public static $MBUS_CONTROL_INFO_SEND_USER_DATA = "B2";
    public static $MBUS_CONTROL_INFO_INIT_TEST_CALIB = "B3";
    public static $MBUS_CONTROL_INFO_EEPROM_READ = "B4";
    public static $MBUS_CONTROL_INFO_SW_TEST_START = "B6";

    public static $MBUS_CONTROL_INFO_RESP_FIXED = "73";
    public static $MBUS_CONTROL_INFO_RESP_FIXED_MSB = "77";

    public static $MBUS_CONTROL_INFO_RESP_VARIABLE = "72";
    public static $MBUS_CONTROL_INFO_RESP_VARIABLE_MSB = "73";

    //
    // DATA BITS double quoted are all in hex
    //
    public static $MBUS_DATA_FIXED_STATUS_FORMAT_MASK = "80";
    public static $MBUS_DATA_FIXED_STATUS_FORMAT_BCD = "0";
    public static $MBUS_DATA_FIXED_STATUS_FORMAT_INT = "80";
    public static $MBUS_DATA_FIXED_STATUS_DATE_MASK = "40";
    public static $MBUS_DATA_FIXED_STATUS_DATE_STORED = "40";
    public static $MBUS_DATA_FIXED_STATUS_DATE_CURRENT = "0";


    //
    // data record fields double quoted are all in hex
    //
    public static $MBUS_DATA_RECORD_DIF_MASK_INST = "0";
    public static $MBUS_DATA_RECORD_DIF_MASK_MIN = "10";

    public static $MBUS_DATA_RECORD_DIF_MASK_TYPE_INT32 = "4";
    public static $MBUS_DATA_RECORD_DIF_MASK_STORAGE_NO = "40";
    public static $MBUS_DATA_RECORD_DIF_MASK_EXTENTION = "80";


    //
    // FIXED DATA FLAGS
    //

    //
    // VARIABLE DATA FLAGS double quoted are all in hex
    //
    public static $MBUS_VARIABLE_DATA_MEDIUM_OTHER = "0";
    public static $MBUS_VARIABLE_DATA_MEDIUM_OIL = "1";
    public static $MBUS_VARIABLE_DATA_MEDIUM_ELECTRICITY = "2";
    public static $MBUS_VARIABLE_DATA_MEDIUM_GAS = "3";
    public static $MBUS_VARIABLE_DATA_MEDIUM_HEAT = "4";
    public static $MBUS_VARIABLE_DATA_MEDIUM_STEAM = "5";
    public static $MBUS_VARIABLE_DATA_MEDIUM_HOT_WATER = "6";
    public static $MBUS_VARIABLE_DATA_MEDIUM_WATER = "7";
    public static $MBUS_VARIABLE_DATA_MEDIUM_HEAT_COST = "8";
    public static $MBUS_VARIABLE_DATA_MEDIUM_COMPR_AIR = "9";
    public static $MBUS_VARIABLE_DATA_MEDIUM_COOL_OUT = "A";
    public static $MBUS_VARIABLE_DATA_MEDIUM_COOL_IN = "B";
    public static $MBUS_VARIABLE_DATA_MEDIUM_BUS = "E";
    public static $MBUS_VARIABLE_DATA_MEDIUM_COLD_WATER = "16";
    public static $MBUS_VARIABLE_DATA_MEDIUM_DUAL_WATER = "17";
    public static $MBUS_VARIABLE_DATA_MEDIUM_PRESSURE = "18";
    public static $MBUS_VARIABLE_DATA_MEDIUM_ADC = "19";

    // DATA RECORDS double quoted are all in hex
    public static $MBUS_DIB_DIF_EXTENSION_BIT = "80";
    public static $MBUS_DIB_VIF_EXTENSION_BIT = "80";

}
