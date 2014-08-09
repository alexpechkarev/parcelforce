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

namespace Alexpechkarev\Parcelforce\PHP;

use \PDO;
use \PDOException;
use \RuntimeException;
use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;

class Parcelforce extends PDO{

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
     * Class constructor
     */
   public function __construct($headerFileType = FALSE) {

       // loading config
       $this->config = include("config.php");
       
       
       // validating config
       if(!is_array($this->config) || count($this->config) < 1):
           throw new RuntimeException('Please check config.php exists.');
       endif;
       
       // init database connection
        try{
            parent::__construct('mysql:host='.$this->config['DB_HOST'].';dbname='.$this->config['DB_NAME'],
                    $this->config['DB_USER'],
                    $this->config['DB_PASS'],
                    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"));
            
        }catch(PDOException $e){
            throw new RuntimeException('Check database settings: '.$e->getMessage());
        }
        
       /**
        * Set header type to SKEL
        * Allowing UK Domestic services despatches only
        * 
        * default set to: DSCC - UK Domestic collection request
        */
       if(!empty($headerFileType)){
           $this->config['header_file_type'] = 'SKEL';
           $this->config['deliveryDetails']['dr_location_id'] = 1;
       }        
        
       // set date object
       $this->dateObj = new DateTime($this->config['collectionDate'], new DateTimeZone('Europe/London'));         
       // setting dispatch date
       $this->config['header_dispatch_date'] = $this->dateObj->format('Ymd');
       
       // initiate checks
       $this->setup();       
       // set header content
       $this->fileContent.= $this->getHeader(); 
              
        
    }
    /***/
    
    
    /**
     * Debug output method
     * @param mixed $d
     */
    public static function dd($d){
        
        die(var_dump($d));
    }
    /***/
    
    /**
     * Perform necessary checks
     * @return boolean
     * @throws \RuntimeException
     */
    public function setup(){
        
       
        // Check if files directory has been created
        if(!is_dir($this->config['filePath'])):
            throw new RuntimeException('Please ensure files directory exists.');
        endif;
        
        // Check if files directory has been created
        if(!is_writable($this->config['filePath'])):
            throw new RuntimeException('Please make sure that files directory is writable.');
        endif;
                
        // create table if not exists
        if( !$this->isTableExists($this->config['filenum_table'])):
            
            $this->createTable($this->config['filenum_table'], $this->config['fileNumber']); 
            $this->insertTableValue($this->config['filenum_table'], $this->config['fileNumber']);
            // increment for next run
            $this->insertTableValue($this->config['filenum_table'], ($this->config['fileNumber']+1) );
            $this->config['header_bath_number'] = $this->padWithZero(); 
            $this->config['fileName'].= $this->padWithZero().'.tmp';
            
        else:
            $this->setFileName();
        endif;        
        
        // initialized dr_consignment_number with database on first run
        if( !$this->isTableExists($this->config['consnum_table'])):
            $this->createTable($this->config['consnum_table']);
            $this->insertTableValue($this->config['consnum_table'], $this->config['deliveryDetails']['dr_consignment_number']);       
            // increment number for next call
            $this->insertTableValue($this->config['consnum_table'], ($this->config['deliveryDetails']['dr_consignment_number']+1) );            
        else:
            $this->setConsignmentNumber();
        endif;        
       
        
        return true;
    }
    /***/    
    
    
    
    /**
     * Initiate process
     * 
     * - generate file content
     * - generate footer content
     * - create consignment file 
     * - upload file
     * @param array $data - array of data
     * @return string - File content
     */
    public function process($data){
        $this->setRecord($data);
        $this->fileContent.= $this->getFooter();
        $this->createFile();
        
        $this->uploadFile();
       
        
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
            throw new InvalidArgumentException("Invaild number of arguments given. Expecting an array.");
        endif;
        
        $data = func_get_args(); 
        $cc = new \ArrayIterator($data[0]);
        
        if($cc->count() < 1):
            throw new InvalidArgumentException("Invaild collection data.");
        endif;
        
        while($cc->valid()):
            
            $item = $cc->current();
            $cc->next();
           
            // merge values with default Collection Details array
            $senderDetails = array_merge($this->config['collectionDetails'], $item['collectionDetails']);
            
            
            // check that mandatory fields specified [not null]
            try{
                array_count_values($senderDetails);
            }catch(ErrorException $e){
                throw new InvalidArgumentException("Mandatory field ". array_search(null, $senderDetails, true). " must not be NULL!");

            }            

            // prepend fields with delimiter characters when needed
            $this->addDelimiter($senderDetails, $item['collectionDetails']);
            
            //merge with default delivery details
            $deliveryDetails = array_merge($this->config['deliveryDetails'], $item['deliveryDetails']); 
            // check that mandatory fields specified [not null]
            try{
                array_count_values($deliveryDetails);
            }catch(ErrorException $e){
                throw new InvalidArgumentException("Mandatory field ". array_search(null, $deliveryDetails, true). " must not be NULL!");

            }             
            
            // prepend fields with delimiter characters when needed
            $this->addDelimiter($deliveryDetails, $item['deliveryDetails']);
            
            
            // Setting sender record - collect consignment from
            
            // increment record count
            $this->config['trailer_record_count']++;
            $this->fileContent.= 
                                $senderDetails['sender_record_type_indicator']
                               .$this->config['delimiterChar']
                               .$senderDetails['sender_file_version_number']
                               .$senderDetails['senderName']
                               .$senderDetails['senderAddress1']
                               .$senderDetails['senderAddress2']
                               .$senderDetails['senderAddress3']                               
                               .$senderDetails['senderAddress4']
                               .$senderDetails['senderAddress5']
                               .$senderDetails['senderPostTown']
                               .$senderDetails['senderPostcode']
                               .$senderDetails['senderContactName']
                               .$senderDetails['senderContactNumber'];
            
                               if($this->config['header_file_type'] === 'DSCC'):
                                  $this->fileContent.= $senderDetails['senderVehicle'] 
                                                        .$senderDetails['senderPaymentMethod']
                                                        .$senderDetails['senderPaymentValue']
                                                        .$this->config['delimiterChar'];
                               endif;
                                       
         $this->fileContent.= "\r\n";
             
            // Setting detail record - deliver consignment to
            
            // increment record count     
         
            $this->config['trailer_record_count']++;
            
//            Implement use of implode instead 
//            config.php need modifying and method that generates consignment number
//            
//            $this->fileContent.= implode($this->config['delimiterChar'], $deliveryDetails);
//            $this->fileContent.= $this->config['delimiterChar'] 
//                               ."\r\n"; 
            $this->fileContent.= 
                                $deliveryDetails['dr_record_type_indicator']
                               .$this->config['delimiterChar']
                               .$deliveryDetails['dr_file_version_number']
                               .$this->config['delimiterChar']
                               .$deliveryDetails['dr_consignment_prefix_number']
                               .$deliveryDetails['dr_consignment_number']
                               .$deliveryDetails['dr_consisgnment_check_digit']
                               .$this->config['delimiterChar']
                               .$deliveryDetails['dr_service_id']
                               .$deliveryDetails['dr_weekend_handling_code']
                               .$this->config['delimiterChar']
                               .$this->config['delimiterChar']
                               .$deliveryDetails['senderReference']
                               .$this->config['delimiterChar']                    
                               .$deliveryDetails['dr_location_id']
                               .$this->config['delimiterChar']                    
                               .$deliveryDetails['contractNumber']
                               .$this->config['delimiterChar']                     
                               .$deliveryDetails['numberOfItems']
                               .$deliveryDetails['consignmentWeight']
                               .$deliveryDetails['receiverName']
                               .$deliveryDetails['receiverAddress1']
                               .$deliveryDetails['receiverAddress2']
                               .$deliveryDetails['receiverAddress3']
                               .$deliveryDetails['receiverPostTown']
                               .$deliveryDetails['receiverPostcode']
                               .$this->config['delimiterChar'] 
                               ."\r\n";  
            
            // increment consignment number for next run
            $this->setConsignmentNumber();
                        
      endwhile;
        
        
        
        
    }
    /***/

    
    /*
    |-----------------------------------------------------------------------
    | Helper methods
    |-----------------------------------------------------------------------
    */     
    
    /**
     * Prepend array values with delimiter character when needed
     * @param array $arr - array of values
     * @param array $master - config array
     */
    public function addDelimiter(&$arr, &$master){
            
            array_walk($arr, function(&$it) use($master){                
                if(in_array($it, $master) && $it != '+'):
                    $it =  $this->config['delimiterChar'].$it;
                endif;
            }); 
            
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
     * Get consignment number from databse and assign to config
     * Format integer
     * Max/Min - 6 
     */
    public function setConsignmentNumber(){
        
        $row = $this->getTableValue($this->config['consnum_table']);
        $this->config['deliveryDetails']['dr_consignment_number'] = $row->{$this->config['consnum_table']['fieldName']};
        // set check digit for new consignment number
        $this->setCheckDigit();
        
        // increment number for next call
        $this->insertTableValue($this->config['consnum_table'], $row->{$this->config['consnum_table']['fieldName']}+1);
        
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
        
        $sum =      ($this->config['deliveryDetails']['dr_consignment_number'][0] * 4) 
                +   ($this->config['deliveryDetails']['dr_consignment_number'][1] * 2) 
                +   ($this->config['deliveryDetails']['dr_consignment_number'][2] * 3) 
                +   ($this->config['deliveryDetails']['dr_consignment_number'][3] * 5) 
                +   ($this->config['deliveryDetails']['dr_consignment_number'][4] * 9) 
                +   ($this->config['deliveryDetails']['dr_consignment_number'][5] * 7) ;
        
        $rem = $sum % 11;
        $checkdigit = 0;

        if((11 -$rem) == 10):
            $checkdigit = 0;
        elseif((11 - $rem) == 11):
            $checkdigit = 5;
        else:
            $checkdigit = 11 - $rem;
        endif;
        
        $this->config['deliveryDetails']['dr_consisgnment_check_digit'] = $checkdigit;
    }
    /***/     
    
    
    /*
    |-----------------------------------------------------------------------
    | HEader / Footer  methods
    |-----------------------------------------------------------------------
    */ 
    
    /**
     * Get header content
     * 
     * @return string
     */
    public function getHeader(){
        
       return $this->config['header_record_type_indicator']
                .$this->config['delimiterChar']
                .$this->config['header_file_version_number']
                .$this->config['delimiterChar']
                .$this->config['header_file_type']                
                .$this->config['delimiterChar']
                .$this->config['header_customer_account']
                .$this->config['delimiterChar']
                .$this->config['header_generic_contract']
                .$this->config['delimiterChar']
                .$this->config['header_bath_number']
                .$this->config['delimiterChar']
                .$this->config['header_dispatch_date']
                .$this->config['delimiterChar']
                .$this->config['header_dispatch_time']
                .$this->config['delimiterChar']
                .$this->config['header_last_collection']
                .$this->config['delimiterChar']
                ."\r\n";
    }
    /***/
    
    /**
     * Get trailer footer
     */
    public function getFooter(){
        
        return $this->config['trailer_record_type_indicator']
                .$this->config['delimiterChar']
                .$this->config['trailer_file_version_number']
                .$this->config['delimiterChar']
                .$this->config['trailer_record_count']
                .$this->config['delimiterChar'];
    }
    /***/    
    
    
    /*
    |-----------------------------------------------------------------------
    | File methods
    |-----------------------------------------------------------------------
    */     
    
    
    /**
     * Get file name
     * Also set batch number
     */
    public function setFileName(){
            
            $incrementFlag = true;
            $row = $this->getTableValue($this->config['filenum_table']);

            // reset file and batch numbers to 1 when reached 9999
            if($row->{$this->config['filenum_table']['fieldName']} == 9999):
                $row->{$this->config['filenum_table']['fieldName']} = 1;
                $incrementFlag = false;
                $this->resetTable($this->config['filenum_table']);
                $this->createTable($this->config['filenum_table']);
                $this->insertTableValue($this->config['filenum_table'], $row->{$this->config['filenum_table']['fieldName']} );
            endif;
            
            $this->config['fileNumber']         = $row->{$this->config['filenum_table']['fieldName']} ;
            // pad number left with zeros and set file name
            $this->config['fileName'].= $this->padWithZero().'.tmp';
            
            /**
             * Unique number per batch, to be created by the source system 
             * Start at 1 and increment by 1 per batch After 9999 is reached, restart at 1
             */
            // pad number left with zeros and set file name
            $this->config['header_bath_number'] = $this->padWithZero();            
          
            // increment file number for next run
            $this->insertTableValue($this->config['filenum_table'], $row->{$this->config['filenum_table']['fieldName']}+1 );
            
    }
    /***/ 
    
    
    /**
     * Creating file and writing consignment details
     * 
     * @return boolean
     * @throws RuntimeException
     */
    public function createFile(){
                                          
        // write to the file
        if(file_put_contents($this->config['filePath'].$this->config['fileName'], $this->fileContent) === false):
           throw new RuntimeException('Unable to write to: '.$this->config['fileName']);
        endif;         
        
        return true;
        
    }
    /***/
    
    /**
     * Uploading file to FTP
     * 
     * @throws RuntimeException
     */
    public function uploadFile(){
        
        // establish connection
        $this->ftpConn = ftp_connect($this->config['ftpHost']);
        
        
        if(empty($this->ftpConn)):
            throw new RuntimeException("Unable to connect to FTP - ".$this->config['ftpHost']);
        endif;

        // attempt login
         if(ftp_login($this->ftpConn, $this->config['ftpUser'], $this->config['ftpPass']) === false):
                 throw new RuntimeException("Unable to FTP login with - ".$this->config['ftpUser']);
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
    | Databse helper methods
    |-----------------------------------------------------------------------
    */  
    
    /**
     * Is given table exists
     * @param string $table
     * @return boolean
     */
    public function isTableExists($tableData){
        $st = parent::prepare('SHOW TABLES LIKE :table');
        $st->bindParam(':table', $tableData['tableName'], PDO::PARAM_STR);
        $st->execute();
        
        return ( $st->rowCount() > 0);
    }
    /***/
    
    /**
     * Retrive data from given table
     * @param array $tableData - ['tableName'=>'mytable', 'fieldName'=>'myfieldname']
     * @return object stdClass
     */
    public function getTableValue($tableData){
        
        $st = parent::prepare('SELECT '.$tableData['fieldName']
                .' FROM '.$tableData['tableName']
                .' ORDER BY id DESC LIMIT 1');
        $st->execute();
        return $st->fetchObject();
    }
    /***/
    
    /**
     * Insert value into given table
     * @param array $tableData - ['tableName'=>'mytable', 'fieldName'=>'myfieldname']
     * @param mixed $value
     * @return boolean
     */
    public function insertTableValue($tableData, $value){
        
        $st = parent::prepare('INSERT INTO '.$tableData['tableName']
                .' ('.$tableData['fieldName'].')'
                .' VALUES(:value)');
        
        $st->bindParam(':value', $value, PDO::PARAM_INT);
        return $st->execute();
    }
    /***/
    
    /**
     * Creating default table
     * @param array $tableData - ['tableName'=>'mytable', 'fieldName'=>'myfieldname']
     */
    public function createTable($tableData){
        
        $sqlCreate = "
            CREATE TABLE `".$tableData['tableName']."` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `".$tableData['fieldName']."` int(11) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
            ";        
        parent::exec($sqlCreate);
        
    }
    /***/
    
    /**
     * Drop and create given table with default value
     * @param array $tableData - ['tableName'=>'mytable', 'fieldName'=>'myfieldname']
     */
    public function dropTable($tableData){
        
        parent::exec('DROP TABLE IF EXISTS '.$tableData['tableName']);
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

