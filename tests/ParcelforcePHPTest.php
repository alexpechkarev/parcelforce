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
 * Description of ParcelforceTest
 *
 * @author Alexander Pechkarev <alexpechkarev@gmail.com>
 */



use Alexpechkarev\Parcelforce\PHP\Parcelforce;
use Mockery as m;


class ParcelforcePHPTest extends PHPUnit_Framework_TestCase{    
    
    
    protected $pf;
    protected $config;
    protected $senderData;
    


    /**
     * Setting up
     */
    public function setUp() {
        parent::setUp();
        
        
        
        $this->pf = new Parcelforce(include(__DIR__.'/../src/Alexpechkarev/Parcelforce/PHP/config.php'));
        $this->config = $this->pf->getConfig();
        $this->senderData = array(
                    array(
                        "collectionDetails" =>array(
                            "senderName"    =>'PARCELFORCE WORLDWIDE', 
                            "senderAddress1"=>"LYTHAM HOUSE", 
                            "senderAddress2"=>'28 CALDECOTTE LAKE DRIVE',
                            "senderAddress3"=>'CALDECOTTE',
                            "senderPostTown"=>'MILTON KEYNES',        
                            "senderPostcode"=>"MK7 8LE"
                            ),
                        "deliveryDetails"=>array(
                            'receiverName'      =>"FedEx UK",
                            'receiverAddress1'  =>'UNITS 23 & 24',
                            'receiverAddress2'  =>'COMMON BANK EMPLOYMENT AREA',
                            'receiverPostTown' =>'CHORLEY',
                            'receiverPostcode'  =>'PR7 1NH'
                            )
                        ),

                    array(
                        "collectionDetails" =>array(
                            "senderName"    =>'FedEx UK', 
                            "senderAddress1"=>"UNITS 23 & 24", 
                            "senderAddress2"=>'COMMON BANK EMPLOYMENT AREA',
                            "senderPostTown"=>'CHORLEY',        
                            "senderPostcode"=>"PR7 1NH"
                            ),
                        "deliveryDetails"=>array(
                            'receiverName'      =>"PARCELFORCE WORLDWIDE",
                            'receiverAddress1'  =>'LYTHAM HOUSE',
                            'receiverAddress2'  =>'28 CALDECOTTE LAKE DRIVE',
                            'receiverAddress3'  =>'CALDECOTTE',
                            'receiverPostTown' =>'MILTON KEYNES',
                            'receiverPostcode'  =>'MK7 8LE'
                        )                
                   )           
                );
    }
    /***/
    
    /**
     * Close Mockery
     */
    public function tearDown() {
        parent::tearDown();
        m::close();
    }
    /***/
  
        

    /**
     * Instantiate Parcelforce class
     * @test
     */
    public function test_is_instantiable(){
        $this->assertInstanceOf('Alexpechkarev\Parcelforce\PHP\Parcelforce', $this->pf);
    }
    /***/
    
    
    
    /**
     * Is collection date set in config file
     * Is Parcelforce class has property dateObj
     * Is cillectionDate is not NULL
     * @test
     * @uses Carbon 
     */
    public function test_date_object_from_config(){
        $this->assertArrayHasKey('collectionDate', $this->config); 
        $this->assertNotNull($this->config['collectionDate']);
        $this->assertClassHasAttribute("dateObj", 'Alexpechkarev\Parcelforce\PHP\Parcelforce');
    }
    /***/
    
    /**
     * Testing package setup method
     *
     * @test
     * @return true
     */
    public function test_verify_package_setup(){
        $this->assertTrue($this->pf->setup());
    }
    /***/
    
    /**
     * Test addDelimiter method
     * @test
     */
    public function test_add_delimiter(){
        $arr1 = array("foo"=>"bar", "baz"=>"foo", "one"=>"+", "two"=>"+", "three"=>"three");
        $arr2 = array("foo"=>"bar", "baz"=>"foo", "one"=>"+", "two"=>"+", "three"=>"three");
        $this->pf->addDelimiter($arr1, $arr2);
        $this->assertStringStartsWith($this->config['delimiterChar'], $arr1['foo']);
        $this->assertStringStartsWith($this->config['delimiterChar'], $arr1['baz']);
        $this->assertStringStartsWith($this->config['delimiterChar'], $arr1['one']);
        $this->assertStringStartsWith($this->config['delimiterChar'], $arr1['two']);
        $this->assertStringStartsWith($this->config['delimiterChar'], $arr1['three']);
    }
    /***/
    
    /**
     * Testing setHeader method
     * @test
     */
    public function test_set_header(){
        
        $fileContent = $this->config['header_record_type_indicator']
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
       
        $mock = m::mock('Alexpechkarev\Parcelforce\PHP\Parcelforce', $this->config);
        $mock->shouldReceive('setHeader')
                ->once()
                ->andReturn($this->pf->getFileContent());
        
        $resp = strcmp( $fileContent, $mock->setHeader() );
        $this->assertTrue( empty( $resp ) );
    }
    /***/
    
    
    /**
     * Testing getFooter method
     * @test
     */
    public function test_set_footer(){
        
        $this->pf->setRecord($this->senderData);
        $this->config = $this->pf->getConfig();
        $footer = $this->config['trailer_record_type_indicator']
                .$this->config['delimiterChar']
                .$this->config['trailer_file_version_number']
                .$this->config['delimiterChar']
                .$this->config['trailer_record_count']
                .$this->config['delimiterChar'];
        
        $mock = m::mock('Alexpechkarev\Parcelforce\PHP\Parcelforce', $this->config);
        $mock->shouldReceive('getFooter')
                ->once()
                ->andReturn($this->pf->getFooter());
        
        $resp = strcmp($footer, $mock->getFooter());
        
        $this->assertTrue( empty( $resp ) );
       
    }
    /***/
    

}
