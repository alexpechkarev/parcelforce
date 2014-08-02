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


use Illuminate\Support\Facades\Config;
use Alexpechkarev\Parcelforce\Parcelforce;
use Carbon\Carbon;

class ParcelforceTest extends TestCase{    
    
    
    protected $pf;
    protected $config;
    
    public function setUp() {
        parent::setUp();
        $this->pf = new Parcelforce(Config::get('parcelforce::config'));
        $this->config = Config::get('parcelforce::config');
    }
    
    /**
     * Instantiate Parcelforce class
     * @test
     */
    public function test_is_instantiable(){
        $this->assertInstanceOf('Alexpechkarev\Parcelforce\Parcelforce', $this->pf);
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
        $this->assertClassHasAttribute("dateObj", 'Alexpechkarev\Parcelforce\Parcelforce');
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
}
