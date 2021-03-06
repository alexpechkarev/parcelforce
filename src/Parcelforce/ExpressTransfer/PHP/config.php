<?php

/* 
 * The MIT License
 *
 * Copyright 2014 Alexander Pechkarev <alexpechkarev@gmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */



    
    return array(
        
        /*
        |-----------------------------------------------------------------------
        | Databse credentials
        |-----------------------------------------------------------------------
        */        
        
        'DB_HOST'       =>  'localhost',
        
        'DB_NAME'       =>  'devdb', 
        
        'DB_USER'       =>  'root', 
        
        'DB_PASS'       =>  '',
        
        
        /*
        |-----------------------------------------------------------------------
        | Databse tables
        |-----------------------------------------------------------------------
        */         
        
        'filenum_table' => array(
            'tableName' => 'tbl_parcelforce_filenum',
            'fieldName' => 'filenum'
            ),
        
        'consnum_table' => array(
            'tableName' => 'tbl_parcelforce_consnum',
            'fieldName' => 'consnum'
            ),
        
        /*
        |-----------------------------------------------------------------------
        | Header Record – Type 0
        |-----------------------------------------------------------------------
        */
        
        'header_record' => array(
            
            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 1
             * Mandatory/Optional   - M
             * Comment              - Set to 0
             */
            'header_record_type_indicator'     => 0,

            /**
             * Fomat                - numeric 
             * Min/Max length       - 2
             * Mandatory/Optional   - M
             * Comment              - Set to '02'
             */        
            'header_file_version_number'       => '02',

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 4
             * Mandatory/Optional   - M
             * Comment              - One of domestic file types: SKEL, DSCA or DSCC
             * for more detail of Domestic file types 
             * refere to File Specification for Data Exchange using Parcelforce Table 2.1

             */        
            'header_file_type'                 => 'DSCC',

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 7
             * Mandatory/Optional   - M
             * Comment              - Customer account number as provided by Parcelforce (Format: 3 alpha, 4 numeric)

             */        
            'header_customer_account'          => 'ABC1234',

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 7
             * Mandatory/Optional   - M
             * Comment              - Generic contract number as provided by Parcelforce. 
             *                        If the contract number is to be supplied in Detail record (field 10) 
             *                        then the string MULTIPL should be present in this field
             *
             *
             */        
            'header_generic_contract'          => 'C000001',

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 1-4
             * Mandatory/Optional   - M
             * Comment              - Unique number per batch, to be created bythe 
             *                        source systemStart at 1 and increment by 1 per batch
             *                        After 9999 is reached, restart at 1
             *
             *
             */         
            'header_bath_number'               => 1,

            /**
             * Fomat                - numeric (CCYYMMDD)
             * Min/Max length       - 8
             * Mandatory/Optional   - M
             * Comment              - Date consignment is to be collected by Parcelforce.
             *
             *
             */        
            'header_dispatch_date'             => 'CCYYMMDD',

            /**
             * Fomat                - alphanumeric (CCYYMMDD)
             * Min/Max length       - 6
             * Mandatory/Optional   - M
             * Comment              - For File Type SKEL – either the Despatch Time 
             *                        or 6 zeros if time is not available. 
             *                        For file types DSCC or DSCA – The earliest collection time 
             *                        for an ad-hoc collections or 6 zeros if a time is not available
             *
             */         
            'header_dispatch_time'             => '000000',

            /**
             * Fomat                - numeric (HHMMSS)
             * Min/Max length       - 6
             * Mandatory/Optional   - M
             * Comment              - ONLY used in DSCC or DSCA files. 
             *                          The latest time parcel can be collected, 
             *                          or 6 zeros if a time is not available.
             */         
            'header_last_collection'           => '000000',
            
            'filler_field'                     => '',
            
        ),
        
        
        
        
        /*
        |-----------------------------------------------------------------------
        | Sender Record – Type 1 
        |-----------------------------------------------------------------------
        |  
        | Fill you busines name and address details below 
        */
        
        "senderDetails" => array(
            /**
             * Fomat                - Alphanumeric 
             * Min/Max length       - 1
             * Mandatory/Optional   - M
             * Comment              - Set to 1
             */          
            'sender_record_type_indicator'     => 1,

            /**
             * Fomat                - numeric 
             * Min/Max length       - 2
             * Mandatory/Optional   - M
             * Comment              - Set to '02'
             */        
            'sender_file_version_number'       => '02',
            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 1 - 18
             * Mandatory/Optional   - M
             * Comment              - Initialize with sender name
             */         
            "senderName"                    => 'PARCELFORCE WORLDWIDE', 

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 1 - 24
             * Mandatory/Optional   - M
             * Comment              - Initialize with sender address line 1
             */         
            "senderAddress1"                => 'LYTHAM HOUSE', 

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 0 - 24
             * Mandatory/Optional   - 0
             */         
            "senderAddress2"                => '28 CALDECOTTE LAKE DRIVE',

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 0 - 24
             * Mandatory/Optional   - 0
             */        
            "senderAddress3"                => 'CALDECOTTE',

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 0 - 24
             * Mandatory/Optional   - 0
             */        
            "senderAddress4"                => '',

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 0 - 24
             * Mandatory/Optional   - 0
             */        
            "senderAddress5"                => '',

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 1 - 18
             * Mandatory/Optional   - M
             * Comment              - Initialize with sender post town
             */        
            "senderPostTown"                => 'MILTON KEYNES',

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 6 - 8
             * Mandatory/Optional   - M
             * Comment              - Initialize with sender postcode
             */          
            "senderPostcode"                => 'MK7 8LE',

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 0 - 40
             * Mandatory/Optional   - O
             * Comment              - Field and separator not used in SKEL files. Only relevant for DSCC and DSCA
             */         
            "senderContactName"             => '', 

            /**
             * Fomat                - numeric 
             * Min/Max length       - 0 - 20
             * Mandatory/Optional   - O
             * Comment              - Field and separator not used in SKEL files. Only relevant for DSCC and DSCA
             */        
            "senderContactNumber"           => '',

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 0 - 28
             * Mandatory/Optional   - O
             * Comment              - Field and separator not used in SKEL files. Only relevant for DSCC and DSCA
             */        
            "senderVehicle"                 => '',

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 0 - 20
             * Mandatory/Optional   - O
             * Comment              - Field and separator not used in SKEL files. Only relevant for DSCC and DSCA
             */        
            "senderPaymentMethod"           => '',

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 0 - 8
             * Mandatory/Optional   - O
             * Comment              - Field and separator not used in SKEL files. Only relevant for DSCC and DSCA
             */        
            "senderPaymentValue"          => '',
            
            'filler_field_1'               => '',
         ),
        
 
        
        
        /*
        |-----------------------------------------------------------------------
        | Detail Record – Type 2 - Delivery details
        |-----------------------------------------------------------------------
        */
        
            /**
             * Consignment number providede by ParcelForce in format 2 alpha and 7 numeric
             * should be split into two parts 2 alpha and 7 numeric and entered below separately
             * 
             * @example AB1234567 - given consignment number
             *  'prefix'    => 'AB'
             *  'number'    => 1234567
             * 
             */        
        'dr_consignment_number' => array(
            
            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 2
             * Mandatory/Optional   - M
             * Comment              - Format: 2 alpha
             */        
            'prefix'                    => 'AB',

            /**
             * Fomat                - numeric 
             * Min/Max length       - 7
             * Mandatory/Optional   - M
             * Comment              - Format: 7 numeric
             */         
            'number'                    => 1234567,  
            
            
            /**
             * Fomat                - numeric 
             * Min/Max length       - 1
             * Mandatory/Optional   - M
             * Comment              - Generated on the server based on given algorithm
             */            
            'check_digit'     => 0,            
            
        ),
        
 
        
        

        "deliveryDetails" => array(

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 1
             * Mandatory/Optional   - M
             * Comment              - Set to 2
             */         
            'dr_record_type_indicator'        => 2,

            /**
             * Fomat                - numeric 
             * Min/Max length       - 2
             * Mandatory/Optional   - M
             * Comment              - Set to '02'
             */        
            'dr_file_version_number'          => '02',
            
            
            /**
             * Do set value for this parameter
             * value will be auto generated
             */
            'consignment_number'              => null,


            /**
             * Fomat                - alphanumeric  
             * Min/Max length       - 1 - 4
             * Mandatory/Optional   - M
             * Comment              - Code relating to selected service
             * 
             * Parcelforce Service  Service ID
             *  express09            S09
             *  express10            S10
             *  expressAM            S12
             *  expressPM            SPM
             *  express24            SND
             *  express48            SUP
             *  express48 large      SID
             *  expresscollect       SMS
             */         
            'dr_service_id'                   => 'SND',

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 0 - 4
             * Mandatory/Optional   - O
             * Comment              - Code indicating weekend handling requirements
             * 
             * Parcelforce Service      Service ID
             *  Saturday delivery         ESAT
             *  Saturday collection       ECSA
             *  Sunday Collection         ECSU
             * 
             */         
            'dr_weekend_handling_code'        => '++', 
            
            
            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 0 - 14
             * Mandatory/Optional   - O
             * Comment              - Customer reference that can be associated with each collection record
             */        
            "senderReference"               => 'SENDERS REFERENCE',            

            /**
             * Fomat                - numeric 
             * Min/Max length       - 1
             * Mandatory/Optional   - M
             * Comment              - Set to ‘1’ for SKEL Set to ‘0’ for DSCA & DSCC
             */
            'dr_location_id'                 => 0,
            
            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 0 - 8
             * Mandatory/Optional   - O
             * Comment              - Specific Contract Number under which despatch is sent.
             *                         (Mandatory if no Generic Contract Numberis supplied in the Header Record (field 5))
             */        
            #"contractNumber"                => '+',  
            
            /**
             * Fomat                - numeric 
             * Min/Max length       - 3 - 8
             * Mandatory/Optional   - O
             * Comment              - Expressed in 100ths of a kilogram. 
             *                          For example 3.5kg should be populated as 350         
             */        
            "consignmentWeight"             => '+',  
                    

            /**
             * Fomat                - numeric 
             * Min/Max length       - 3 - 7
             * Mandatory/Optional   - M
             * Comment              - Number of physical packages in the consignment
             */        
            "numberOfItems"                 => 1,             
                        
            'filler_field_1'                => '',

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 1 - 30
             * Mandatory/Optional   - M
             * Comment              - Consignee name or business name
             */        
            'receiverName'                => null,

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 1 - 30
             * Mandatory/Optional   - M
             * Comment              - Consignee address line 1
             */                
            'receiverAddress1'            => null,

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 0 - 30
             * Mandatory/Optional   - O
             * Comment              - Consignee address line 2
             */                
            'receiverAddress2'            => '',

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 0 - 30
             * Mandatory/Optional   - O
             * Comment              - Consignee address line 3
             */                
            'receiverAddress3'            => '',   

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 0 - 30
             * Mandatory/Optional   - O
             * Comment              - Consignee address line 2
             */                
            'receiverPostTown'          => '+',        

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 6 - 8
             * Mandatory/Optional   - M
             * Comment              - Outward and inward parts should be separated by a space
             */        
            'receiverPostcode'            => null,
            
            'filler_field_2'              => '',            
                        
         
          ),
        
        /*
        |-----------------------------------------------------------------------
        | Trailer Record – Type 9
        |-----------------------------------------------------------------------
        */         
        
        'footer_record' => array(
            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 1
             * Mandatory/Optional   - M
             * Comment              - Set to 9
             */         
            'trailer_record_type_indicator'   => 9,

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 2
             * Mandatory/Optional   - M
             * Comment              - Set to '02'
             */         
            'trailer_file_version_number'     => '02',

            /**
             * Fomat                - alphanumeric 
             * Min/Max length       - 1 - 6
             * Mandatory/Optional   - M
             * Comment              - The total number of records present in the batch, 
             *                          including the Header, Sender andTrailer Records.
             */         
            'trailer_record_count'            => 2, 
            
            'filler_field'                    => '',            
         ),
        
        /*
        |-----------------------------------------------------------------------
        | Global Settings
        |-----------------------------------------------------------------------
        */        
        
        /**
         * Fomat                - delimiter requiried
         * Min/Max length       - 0
         * Comment              - data not requiried
         */         
        'delimiterChar'          => '+',         
        
        /**
         * Location consignment files
         */        
        'filePath'                        => __DIR__.'/files/',
        
        /**
         * File name prefix - supplied by ParcelForce
         */        
        'fileName'                        => 'ABC9',                
        
        /**
         * File number, start with 1 and increment thereafter
         */
        'fileNumber'                      => 1,
        
        /**
         * Collection date - default tomorrow
         */
        'collectionDate'                  => 'tomorrow',
        
        /**
         * Time zone - defauklt Europe/London
         */
        'timeZone'                        => 'Europe/London',
        /**
         * FTP User name
         */
        'ftpUser'                        => 'USERNAME',        
        
        /**
         * FTP Password
         */
        'ftpPass'                        => 'PASSWORD',        
        
        /**
         * FTP Upload Path
         */
        'ftpUploadPath'                  => '/upload/path',   
        
        /**
         * FTP Location Path
         * Parcelforce requires moving files to another path after upload
         */
        'ftpLocationPath'                => '/location/path',        
        
     
        
       
    );      
