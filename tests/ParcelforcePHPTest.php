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



use Parcelforce\ExpressTransfer\PHP\Parcelforce;
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
        
        
        
        $this->pf = new Parcelforce(include(__DIR__.'/../src/Parcelforce/ExpressTransfer/PHP/config.php'));
        $this->config = $this->pf->getConfig();
        $this->senderData = array(
            array(
                "deliveryDetails"=>array(
                    'receiverName'      =>"MR CUSTOMER",
                    'receiverAddress1'  =>'100 CUSTOMER SOLUTIONS STREET',
                    'receiverPostTown'  =>'MILTON KEYNES',
                    'receiverPostcode'  =>'MK9 9AB'
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
        $this->assertInstanceOf('Parcelforce\ExpressTransfer\PHP\Parcelforce', $this->pf);
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
        $this->assertClassHasAttribute("dateObj", 'Parcelforce\ExpressTransfer\PHP\Parcelforce');
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
     * Testing setHeader method
     * @test
     */
    public function test_get_header(){
        
        $header = implode($this->config['delimiterChar'], $this->config['header_record'])."\r\n";
       
        $mock = m::mock('Parcelforce\ExpressTransfer\PHP\Parcelforce', $this->config);
        $mock->shouldReceive('getHeader')
                ->once()
                ->andReturn($this->pf->getHeader());
        
        $resp = strcmp( $header, $mock->getHeader() );
        $this->assertTrue( empty( $resp ) );
    }
    /***/
    
    
    /**
     * Testing getFooter method
     * @test
     */
    public function test_get_footer(){
        
        $this->pf->setRecord($this->senderData);
        $this->config = $this->pf->getConfig();
        $footer = implode($this->config['delimiterChar'], $this->config['footer_record']);
        
        $mock = m::mock('Parcelforce\ExpressTransfer\PHP\Parcelforce', $this->config);
        $mock->shouldReceive('getFooter')
                ->once()
                ->andReturn($this->pf->getFooter());
        
        $resp = strcmp($footer, $mock->getFooter());
        
        $this->assertTrue( empty( $resp ) );
       
    }
    /***/
    

}
