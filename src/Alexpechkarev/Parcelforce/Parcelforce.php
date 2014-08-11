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
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Alexpechkarev\Parcelforce\models\ConsNumber;
use Alexpechkarev\Parcelforce\models\FileNumber;


class Parcelforce {

    /*
    |--------------------------------------------------------------------------
    | Global
    |--------------------------------------------------------------------------
    */    
    protected $config;  
    
    protected $fileContent;
    
    protected $dateObj;
    
    protected $ftpConn;
   
    
    
    /**
     * Init cinfig file and run config cheks
     */
   public function __construct($config) {
        
       $this->config = $config;       
       
       // initiate checks
       $this->setup();       
       
        
    }
    /***/
    
    
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
        if( !Schema::hasTable($this->config['filenum_table']['tableName']) ):
            
            $this->createTable($this->config['filenum_table']);           
            FileNumber::create(array($this->config['filenum_table']['fieldName'] => $this->config['fileNumber'] ));
            
            $this->config['header_record']['header_bath_number'] = $this->padWithZero(); 
            $this->config['fileName'].= $this->padWithZero().'.tmp';
            $this->setFileNumber();
            
        else:
            $this->setFileName();
        endif;
        
        // initialized dr_consignment_number with database on first run
        if( !Schema::hasTable($this->config['consnum_table']['tableName'])):
            
            $this->createTable($this->config['consnum_table']);             
            ConsNumber::create(array($this->config['consnum_table']['fieldName'] => $this->config['dr_consignment_number']['number'] ));            
        else:
            $this->getConsignmentNumber();
        endif;        
       
       /**
        * Set header type to SKEL
        * Allowing UK Domestic services despatches only
        * 
        * config default: DSCC - UK Domestic collection request
        */
       if($this->config['header_record']['header_file_type'] === 'SKEL'){
           $this->config['deliveryDetails']['dr_location_id'] = 1;
       }         
        
        return true;
    }
    /***/    
    
    
    /**
     * Initiate process
     * - generate file content
     * - generate footer content
     * - create consignment file 
     * - upload file
     * @param array $data - array of data
     * @return string File content
     */
    public function process($data, $upload = TRUE){
        
       // set collection date to default config value
        if($this->config['header_record']['header_dispatch_date'] === 'CCYYMMDD'):
            $this->setDate();
        endif;
       // set header record
        $this->fileContent = $this->getHeader();  
        // process consignment data
        $this->setRecord($data);
        // set trailer record
        $this->fileContent.= $this->getFooter();
        //create file
        $this->createFile();
        
        // upload file
        if(!empty($upload)):
            $this->uploadFile();
        endif;
       
        
        return $this->fileContent;
    }
    /***/

   
    
   
    /**
     * Generating file content based on the given data
     * 
     * @throws \InvalidArgumentException
     */
    public function setRecord(){
        
        if(func_num_args() != 1):
            throw new \InvalidArgumentException("Invaild number of arguments given. Expecting an array.");
        endif;
        
        $data = func_get_args();  
        $cc = new \ArrayIterator($data[0]);
        
        if($cc->count() < 1):
            throw new \InvalidArgumentException("Invaild collection data.");
        endif;
        
        while( $cc->valid() ):
            
            $item = $cc->current();        
            $cc->next();
           
            // if sender details are given as parameter than merge with default
            $senderDetails = isset($item['senderDetails'])
                            ? array_merge($this->config['senderDetails'], $item['senderDetails'])
                            : $this->config['senderDetails'];
            
            // for SKEL file type remove following fields
           if($this->config['header_record']['header_file_type'] === 'SKEL'):
              unset($senderDetails['senderContactName']);
              unset($senderDetails['senderContactNumber']);
              unset($senderDetails['senderVehicle']); 
              unset($senderDetails['senderPaymentMethod']);
              unset($senderDetails['senderPaymentValue']);
           endif;              
            
            
            // check that mandatory fields specified [not null]
            try{
                array_count_values($senderDetails);
            }catch(\ErrorException $e){
                throw new \InvalidArgumentException("Mandatory field ". array_search(null, $senderDetails, true). " must not be NULL!");

            }            

            // Setting sender record 
            
            // increment record count
            $this->config['footer_record']['trailer_record_count']++;
            $this->fileContent.= implode($this->config['delimiterChar'], $senderDetails)."\r\n";
            
            
            
            
            
            // generate consignment number
            $this->config['deliveryDetails']['consignment_number'] = implode('', $this->config['dr_consignment_number']);
            // increment consignment number for next package
            $this->getConsignmentNumber();
            $this->setConsignmentNumber();
            
            
            //merge with default delivery details
            $deliveryDetails = array_merge($this->config['deliveryDetails'], $item['deliveryDetails']); 
            
            // check that mandatory fields specified [not null]
            try{
                array_count_values($deliveryDetails);
            }catch(\ErrorException $e){
                throw new \InvalidArgumentException("Mandatory field ". array_search(null, $deliveryDetails, true). " must not be NULL!");

            }             
            

            // increment record count     
            $this->config['footer_record']['trailer_record_count']++;
            
            // Setting delivery record 
            $this->fileContent.= implode($this->config['delimiterChar'], $deliveryDetails)."\r\n";  
            
                        
      endwhile;
        
        
        
        
    }
    /***/

    
    /*
    |-----------------------------------------------------------------------
    | Helper methods
    |-----------------------------------------------------------------------
    */      
    
    /**
     * Setting collection date at run time
     * @param type $date
     */
    public function setDate($date = FALSE){
        
       $this->config['collectionDate'] = $date ? $date : $this->config['collectionDate'];
       // set date object
       $this->dateObj = Carbon::parse($this->config['collectionDate'], $this->config['timeZone']);         
       // setting dispatch date
       $this->config['header_record']['header_dispatch_date'] = $this->dateObj->format('Ymd');        
    }
    /***/    
   

    /**
     * Pad left with zeros
     * @return string
     */
    public function padWithZero(){
        
        $num = (strlen((string)$this->config['fileNumber'] )+4) - strlen((string)$this->config['fileNumber'] );
        return str_pad($this->config['fileNumber'], $num, 0, STR_PAD_LEFT);
    }
    /***/
    
    /**
     * Set file name
     * Also set batch number
     */
    public function setFileName(){
            
            $this->config['fileNumber'] = FileNumber::orderBy('id', 'DESC')
                                            ->first()
                                            ->{$this->config['filenum_table']['fieldName']};
            
            // reset file and batch numbers to 1 when reached 9999
            if($this->config['fileNumber'] == 10000):
                $this->config['fileNumber'] = 1;
                $this->dropTable($this->config['filenum_table']);
                $this->createTable($this->config['filenum_table']);
                FileNumber::create(array($this->config['filenum_table']['fieldName'] => $this->config['fileNumber'] )); 
            endif;
            
            // pad number left with zeros and set file name
            $this->config['fileName'].= $this->padWithZero().'.tmp';
            
            /**
             * Unique number per batch, to be created by the source system 
             * Start at 1 and increment by 1 per batch After 9999 is reached, restart at 1
             */
            // pad number left with zeros and set file name
            $this->config['header_record']['header_bath_number'] = $this->padWithZero();            
          
            // incremnt file number for next run
            $this->setFileNumber(); 
            
    }
    /***/  
    
    /**
     * Get file name
     */
    public function setFileNumber(){   
            FileNumber::create(array($this->config['filenum_table']['fieldName'] => ($this->config['fileNumber']+1) ));
    }
    /***/     
   

    
    /**
     * Get consignment number from databse and assign to config
     * Format integer
     * Max/Min - 6 
     */
    public function getConsignmentNumber(){
        
        $this->config['dr_consignment_number']['number'] = ConsNumber::orderBy('id', 'DESC')                                            
                                            ->first()
                                            ->{$this->config['consnum_table']['fieldName']};
       
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
        ConsNumber::create(array($this->config['consnum_table']['fieldName'] => ($this->config['dr_consignment_number']['number']+1) ));
    }
    /***/    
    
    /**
     * Drop database tables
     */
    public function reset(){
        // drop file number table
        $this->dropTable($this->config['filenum_table']);
        // drop consignment number table
        $this->dropTable($this->config['consnum_table']);
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
        
        $sum =      ($this->config['dr_consignment_number']['number'][0] * 4) 
                +   ($this->config['dr_consignment_number']['number'][1] * 2) 
                +   ($this->config['dr_consignment_number']['number'][2] * 3) 
                +   ($this->config['dr_consignment_number']['number'][3] * 5) 
                +   ($this->config['dr_consignment_number']['number'][4] * 9) 
                +   ($this->config['dr_consignment_number']['number'][5] * 7) ;
        
        $rem = $sum % 11;
        $checkdigit = 0;
        
        if((11 -$rem) == 10):
            $checkdigit = 0;
        elseif((11 - $rem) == 11):
            $checkdigit = 5;
        else:
            $checkdigit = 11 - $rem;
        endif;
        
        $this->config['dr_consignment_number']['check_digit'] = $checkdigit;
    }
    /***/     
    
    
    /*
    |-----------------------------------------------------------------------
    | Header / Footer  methods
    |-----------------------------------------------------------------------
    */ 
    
    /**
     * Get hader record
     */
    public function getHeader(){
        return implode($this->config['delimiterChar'], $this->config['header_record'])."\r\n";
    }
    /***/
    
    /**
     * Get trailer record
     */
    public function getFooter(){        
        return implode($this->config['delimiterChar'], $this->config['footer_record']);
    }
    /***/    
    
    

    
    /*
    |-----------------------------------------------------------------------
    | Databse helper methods
    |-----------------------------------------------------------------------
    */ 
    
    
    /**
     * Creating database table
     * @param array $tbl - ["tableName"=>"tbl_name", "fieldName"=>"fld_name"]
     * @throws \InvalidArgumentException
     */
    public function createTable($tbl){
        
        if(is_array($tbl) 
                && array_key_exists('tableName', $tbl) 
                && array_key_exists('fieldName', $tbl)):
        
        Schema::create($tbl['tableName'], function(Blueprint $table) use($tbl)
        {
            $table->increments('id');
            $table->integer($tbl['fieldName']);
        }); 
        
        else:
            throw new \InvalidArgumentException('Invalid agruments given. Expecting array ["tableName"=>"tbl_name", "fieldName"=>fld_name"]');
        endif;
    }
    /***/
    
    
    
    /**
     * Drop database table
     * @param array $tbl - ["tableName"=>"tbl_name", "fieldName"=>fld_name"]
     * @throws \InvalidArgumentException
     */
    public function dropTable($tbl){
        
        if(is_array($tbl) 
                && array_key_exists('tableName', $tbl) 
                && array_key_exists('fieldName', $tbl)):
        
        Schema::dropIfExists($tbl['tableName']);
        
        else:
            throw new \InvalidArgumentException('Invalid agruments given. Expecting array ["tableName"=>"tbl_name, "fieldName"=>fld_name]');
        endif;
    }
    /***/    
    
    /*
    |-----------------------------------------------------------------------
    | File methods
    |-----------------------------------------------------------------------
    */    
    
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
    
    /**
     * Uploading file to FTP
     * 
     * @throws \RuntimeException
     */
    public function uploadFile(){
        
        // establish connection
        $this->ftpConn = ftp_connect($this->config['ftpHost']);
        
        
        if(empty($this->ftpConn)):
            throw new \RuntimeException("Unable to connect to FTP - ".$this->config['ftpHost']);
        endif;

        // attempt login
         if(ftp_login($this->ftpConn, $this->config['ftpUser'], $this->config['ftpPass']) === false):
                 throw new \RuntimeException("Unable to FTP login with - ".$this->config['ftpUser']);
         endif;
                 
         // turn passive mode on
         ftp_pasv($this->ftpConn, true);
         
         // upload file
         if( ftp_put($this->ftpConn, $this->config['ftpUploadPath']."/".$this->config['fileName'], 
                 $this->config['filePath'].$this->config['fileName'], FTP_ASCII)){
                 
                // get file info 
                 $info = pathinfo($this->config['fileName']);
                 $new_file_name = $info['filename'];
                 // remove file extension and put in the final location path
                 if(ftp_rename($this->ftpConn, $this->config['ftpUploadPath']."/".$this->config['fileName'], 
                         $this->config['ftpLocationPath']."/".$new_file_name)):
                 endif;
         }else{
             
             // Error uploading file
         }
         
         // close conection
         ftp_close($this->ftpConn);
    }
    /***/
    
    
    
    /*
    |-----------------------------------------------------------------------
    | Getters
    |-----------------------------------------------------------------------
    */ 
    
    /**
     * Get file content
     * @return string
     */
    public function getFileContent(){
        return $this->fileContent;
    }
    /***/
    
    /**
     * Get date object
     * @return Carbon object
     */
    public function getDateObj(){
        return $this->dateObj;
    }
    /***/
    
    /**
     * Get config file of current instance
     * @return array
     */
    public function getConfig(){
        return $this->config;
    }
    /***/
    
}
