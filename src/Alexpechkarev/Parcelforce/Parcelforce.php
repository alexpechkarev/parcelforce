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

/**
 * Description of Parcelforce
 *
 * @author Alexander Pechkarev <alexpechkarev@gmail.com>
 */
namespace Alexpechkarev\Parcelforce;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use ConsNumber;
use FileNumber;

class Parcelforce {
    
    
   
    
    protected $config;
        
    /*    
    |-----------------------------------------------------------------------
    | Sender Record – Type 1 - Will be set at runtime
    |-----------------------------------------------------------------------
    */    
    protected $senderRecord = array(
        /**
         * Fomat                - alphanumeric 
         * Min/Max length       - 1 - 18
         * Mandatory/Optional   - M
         * Comment              - Initialize with sender name
         */         
        "senderName"=>null, 
        
        /**
         * Fomat                - alphanumeric 
         * Min/Max length       - 1 - 24
         * Mandatory/Optional   - M
         * Comment              - Initialize with sender address line 1
         */         
        "senderAddress1"=>null, 
        
        /**
         * Fomat                - alphanumeric 
         * Min/Max length       - 0 - 24
         * Mandatory/Optional   - 0
         */         
        "senderAddress2"=>'+',
        
        /**
         * Fomat                - alphanumeric 
         * Min/Max length       - 0 - 24
         * Mandatory/Optional   - 0
         */        
        "senderAddress3"=>'+',
        
        /**
         * Fomat                - alphanumeric 
         * Min/Max length       - 0 - 24
         * Mandatory/Optional   - 0
         */        
        "senderAddress4"=>'+',
        
        /**
         * Fomat                - alphanumeric 
         * Min/Max length       - 0 - 24
         * Mandatory/Optional   - 0
         */        
        "senderAddress5"=>'+',
        
        /**
         * Fomat                - alphanumeric 
         * Min/Max length       - 1 - 18
         * Mandatory/Optional   - M
         * Comment              - Initialize with sender post town
         */        
        "senderPostTown"=>null,
        
        /**
         * Fomat                - alphanumeric 
         * Min/Max length       - 6 - 8
         * Mandatory/Optional   - M
         * Comment              - Initialize with sender postcode
         */          
        "senderPostcode"=>null,
        
        /**
         * Fomat                - alphanumeric 
         * Min/Max length       - 0 - 40
         * Mandatory/Optional   - O
         * Comment              - Field and separator not used in SKEL files. Only relevant for DSCC and DSCA
         */         
        "senderContactName"=>'+', 
        
        /**
         * Fomat                - numeric 
         * Min/Max length       - 0 - 20
         * Mandatory/Optional   - O
         * Comment              - Field and separator not used in SKEL files. Only relevant for DSCC and DSCA
         */        
        "senderContactNumber"=>'+',
        
        /**
         * Fomat                - alphanumeric 
         * Min/Max length       - 0 - 28
         * Mandatory/Optional   - O
         * Comment              - Field and separator not used in SKEL files. Only relevant for DSCC and DSCA
         */        
        "senderVehicle"=>'+',
        
        /**
         * Fomat                - alphanumeric 
         * Min/Max length       - 0 - 20
         * Mandatory/Optional   - O
         * Comment              - Field and separator not used in SKEL files. Only relevant for DSCC and DSCA
         */        
        "senderPaymentMethod"=>'+',
        
        /**
         * Fomat                - alphanumeric 
         * Min/Max length       - 0 - 8
         * Mandatory/Optional   - O
         * Comment              - Field and separator not used in SKEL files. Only relevant for DSCC and DSCA
         */        
        "senderPaymentValue"=>'+'
        );
    

    /*
    |-----------------------------------------------------------------------
    | Detail Record – Type 2 Specified at runtime
    |-----------------------------------------------------------------------
    */   
    
    protected $recordDetails = array(
        
        /**
         * Fomat                - delimiter requiried
         * Min/Max length       - 0
         * Comment              - data not requiried
         */         
        'fillerField'          => '+',        
        
        /**
         * Fomat                - alphanumeric 
         * Min/Max length       - 0 - 14
         * Mandatory/Optional   - O
         * Comment              - Customer reference that can be associated with each collection record
         */        
        "senderReference"=>'+',

        /**
         * Fomat                - alphanumeric 
         * Min/Max length       - 0 - 8
         * Mandatory/Optional   - O
         * Comment              - Specific Contract Number under which despatch is sent.
         *                         (Mandatory if no Generic Contract Numberis supplied in the Header Record (field 5))
         */        
        "contractNumber"=>'+',  
        
        /**
         * Fomat                - numeric 
         * Min/Max length       - 3 - 8
         * Mandatory/Optional   - O
         * Comment              - Expressed in 100ths of a kilogram. 
         *                          For example 3.5kg should be populated as 350         
         */        
        "consignmentWeight"=>'+',  
        
        /**
         * Fomat                - numeric 
         * Min/Max length       - 3 - 7
         * Mandatory/Optional   - M
         * Comment              - Number of physical packages in the consignment
         */        
        "numberOfItems"=>1,  
        
        /**
         * Fomat                - numeric 
         * Min/Max length       - 1
         * Mandatory/Optional   - M
         * Comment              - Check digit
         */        
        'check_digit'     => 0        
        
    );
    
    
    
    /*
    |--------------------------------------------------------------------------
    | Global
    |--------------------------------------------------------------------------
    */    
    protected $fileContent;
    protected $dateObj;
   
    
    
    /**
     * Class constructor
     */
   public function __construct($config) {
        
       $this->config = $config;
       
       $this->dateObj = Carbon::parse($this->config['collectionDate']);  
        
       $this->config['header_dispatch_date'] = $this->dateObj->format('Ymd');
        
       #$this->init();
        
       $this->setup();
        
        
    }
    
    /**
     * Perform necessary checks
     * @return boolean
     * @throws \RuntimeException
     */
    public function setup(){
        
        
        // Check if files directory has been created
        if(!File::isDirectory($this->config['filePath'])):
            throw new \RuntimeException('It looks like config file has not been published. Use php artisan config:publish command.');
        endif;
        
        // Check if files directory has been created
        if(!File::isWritable($this->config['filePath'])):
            throw new \RuntimeException('Please make sure that files directory is writable.');
        endif;
        
        
        // initialized fileNumber with database on first run
        if( FileNumber::all()->count() === 0):
            FileNumber::create(array('filenum' => $this->config['fileNumber'] ));
        else:
            $this->setFileName();
        endif;
        
        // initialized dr_consignment_number with database on first run
        if( ConsNumber::all()->count() === 0):
            ConsNumber::create(array('consnum' => $this->config['dr_consignment_number'] ));            
        else:
            $this->getConsignmentNumber();
        endif;        
        
        
        #$this->createFile();
        #dd($this->config);
        
        return true;
    }
    /***/
    
    
    public function init(){
        
        
            
            $this->setRecod();
            
            
            $this->setFileName();
            $this->generateFile();
            $this->sendFile();        
    }
    
   
    
    public function setRecord(){
        
        $data = func_get_args();
        if(!is_array($data) || count($data) < 1):
            throw new \InvalidArgumentException("Invaild argument given. Expecting an array.");
        endif;
        
//0+02+DSCC+ABC1234+A123456+0001+20100302+080000+170000+
//1+02+PARCELFORCE WORLDWIDE+LYTHAM HOUSE+28 CALDECOTTE LAKE DRIVE+CALDECOTTE+++MILTON KEYNES+MK7 8LE++++++
//2+02+AB1234567+SND++++SENDERS REFERENCE+0+++1++MR CUSTOMER+100 CUSTOMER SOLUTIONS STREET+++MILTON KEYNES+MK9 9AB+
//9+02+4+
        
        
        foreach($data as $key=>$val):
            // merge with Sender Record array
            $record = array_merge($this->senderRecord, $val); 
            // check that mandatory fields specified [not null]
            try{
                array_count_values($record);
            }catch(\ErrorException $e){
                throw new \InvalidArgumentException("Mandatory field ". array_search(null, $record, true). " must not be NULL!");

            }        
            
            $this->config['trailer_record_count']++;
            $this->fileContent.= 
                                $this->config['sender_record_type_indicator']
                               .$this->recordDetails['fillerField']
                               .$this->recordDetails['fillerField'];
            
        
            
        endforeach;
        
        dd($this->fileContent);
    }



    /**
     * Get file name
     */
    public function setFileName(){
            $fn = FileNumber::orderBy('id', 'DESC')->take(1)->get()->toArray();
            $this->config['fileNumber'] = $fn[0]['filenum'];
            // pad number left with zeros
            $num = (strlen((string)$fn[0]['filenum'])+4) - strlen((string)$fn[0]['filenum']);
            //set file name
            $this->config['fileName'].= str_pad($fn[0]['filenum'], $num, 0, STR_PAD_LEFT).'.tmp';  
            
            // incremnt file number for next run
            $this->setFileNumber();
    }
    /***/  
    
    /**
     * Get file name
     */
    public function setFileNumber(){   
            FileNumber::create(array('filenum' => ++$this->config['fileNumber'] ));
    }
    /***/     
   

    
    /**
     * Get consignment number from databse and assign to config
     * Format integer
     * Max/Min - 6 
     */
    public function getConsignmentNumber(){
        
        $fn = ConsNumber::orderBy('id', 'DESC')->take(1)->get()->toArray();
        $this->config['dr_consignment_number'] = $fn[0]['consnum'];
        // set check digit for new consignment number
        $this->setCheckDigit();
        
    }
    /***/
       
    
    /**
     * Set consignment number by incrementing it's value by 1 and store db
     * Format integer
     * Max/Min - 6 
     */
    public function setConsignmentNumber(){
        ConsNumber::create(array('consnum' => ++$this->config['dr_consignment_number'] ));
    }
    /***/    
    
    
    /**
     * Generate Check Digit
     * Thus, given a 6 digit number of 162738 the check digit calculation is as follows:

            1)      1  x  4  =   4
                    6  x  2  =  12
                    2  x  3  =   6
                    7  x  5  =  35
                    3  x  9  =  27
                    8  x  7  =  56

            2)	4 + 12 + 6 + 35 + 27 + 56  =  140

            3)	140  ¸  11  =  12  remainder 8

            4)	11 - 8  =  3
                 * 
            5)	Check digit = 3
     */
    public function setCheckDigit(){
        
        $sum =      ($this->config['dr_consignment_number'][0] * 4) 
                +   ($this->config['dr_consignment_number'][1] * 2) 
                +   ($this->config['dr_consignment_number'][2] * 3) 
                +   ($this->config['dr_consignment_number'][3] * 5) 
                +   ($this->config['dr_consignment_number'][4] * 9) 
                +   ($this->config['dr_consignment_number'][5] * 7) ;
        
        $rem = $sum % 11;
        $checkdigit = 0;
        
        if((11 -$rem) == 10):
            $checkdigit = 0;
        elseif((11 - $rem) == 11):
            $checkdigit = 5;
        else:
            $checkdigit = 11 - $rem;
        endif;
        
        $this->config['dr_consisgnment_check_digit'] = $checkdigit;
    }
    /***/     
    
    /**
     * Set hader
     * @return string
     */
    public function getHeader(){
        
        return $this->config['header_record_type_indicator']
                .'+'
                .$this->config['header_file_version_number']
                .'+'
                .$this->config['header_file_type']                
                .'+'
                .$this->config['header_customer_account']
                .'+'
                .$this->config['header_generic_contract']
                .'+'
                .$this->config['header_bath_number']
                .'+'
                .$this->config['header_dispatch_date']
                .'+'
                .$this->config['header_dispatch_time']
                .'+'
                .$this->config['header_last_collection']
                .'+';
    }
    /***/
    
    /**
     * Get trailer footer
     * @return type
     */
    public function getFooter(){
        
        return $this->config['trailer_record_type_indicator']
                .'+'
                .$this->config['trailer_file_version_number']
                .'+'
                .$this->config['trailer_record_count']
                .'+';
    }
    /***/    
    
    
    /**
     * Creating file and writing consignment details
     * 
     * @return boolean
     * @throws \RuntimeException
     */
    public function createFile(){
                                          
        // write to the file
        if (File::put($this->config['filePath'].$this->config['fileName'], 
                        $this->getHeader().$this->fileContent.$this->getFooter()) === false):
            throw new \RuntimeException('Unable to write to: '.$this->config['fileName']);
        endif;         
        
        return true;
        
    }
    /***/
    
}
