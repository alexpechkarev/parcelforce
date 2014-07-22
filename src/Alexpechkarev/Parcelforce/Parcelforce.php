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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

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
    protected $detailRecord = array(
        
        /**
         * Fomat                - delimiter requiried
         * Min/Max length       - 0
         * Comment              - data not requiried
         */         
        'fillerField'          => '++',        
        
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
    |-----------------------------------------------------------------------
    | Trailer Record – Type 9
    |-----------------------------------------------------------------------
    */     
    protected $trailerRecord = array(
        
        /**
         * Fomat                - alphanumeric 
         * Min/Max length       - 1 - 6
         * Mandatory/Optional   - M
         * Comment              - The total number of records present in the batch, 
         *                          including the Header, Sender andTrailer Records.
         */         
        'trailer_record_count'            => 2        
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
       
        $this->dateObj = Carbon::parse('tomorrow');
        #$d = new Carbon();
        #dd($this->dateObj->format("Y-m-d"));
        #$date = $date = new DateTime('tomorrow', new DateTimeZone('Europe/London'));
        #$this->collectionDate = $date->format("Y-m-d");  
        #$this->collectionDate = '2014-07-04';    
        
        #$this->init();
        
        $this->setupCheck();
        
        
    }
    
    
    public function setupCheck(){
        
        
        // Check if files directory has been created
        if(!File::isDirectory($this->config['filePath'])):
            throw new \RuntimeException('It looks like config file has not been published. Use php artisan config:publish command.');
        endif;
        
        // Check if files directory has been created
        if(!File::isWritable($this->config['filePath'])):
            throw new \RuntimeException('Please make sure that files directory is writable.');
        endif;
        
        
        
        
        return true;
    }
    
    
    public function initConfigFile(){
        
        // write to config.txt file initial settings
        if (File::exists($this->config['filePath'].$this->config['configFile']) === false):
            $bw = File::put(
                                $this->config['filePath'].$this->config['configFile'], 
                                json_encode(array(
                                                "dr_consignment_number" => $this->config['dr_consignment_number'],
                                                "fileumber"=>$this->config['fileNumber']
                                            )
                                )
                            );
            
            if($bw === false):
                throw new \RuntimeException('Unable to write to: '.$this->config['configFile']);
            endif;
            
        else:
            // read config.txt file and re-assign its values to config
                array_merge($this->config, 
                            json_decode(File::get($this->config['filePath'].$this->config['configFile']), true) );
                dd($this->config);
        endif;        
        
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
        if (File::put($this->config['filePath'].$this->config['fileName'], $this->fileContent) === false):
            throw new \RuntimeException('Unable to write to: '.$this->config['fileName']);
        endif;
        
        return true;
        
    }
    /***/
    
}
