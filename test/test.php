<?php
require_once('../WistiaApi.class.php');

class WistiaApiTest extends PHPUnit_Framework_TestCase
{
	private function setSendMethodResponse($response)
	{
	
	}
	public function setup()
	{
		$this->stub = $this->getMockBuilder('WistiaApi')
			->disableOriginalConstructor()
			->setMethods(array('__send'))
			->getMock();
		
	}
	public function testWistiaAccountRead()
	{
		$response = file_get_contents('responseAccountRead');

        $this->stub->expects($this->any())
        	->method('__send')
        	->will($this->returnValue($response));
        
        $expected = new stdClass();
        $expected->id = '1234';
        $expected->name = 'test';
        $expected->url = 'http://test.wistia.com';
        
        $this->assertEquals($expected,$this->stub->accountRead());		
	}
	public function testWistiaMediaList()
	{
		$response = file_get_contents('responseMediaList');

        $this->stub->expects($this->any())
        	->method('__send')
        	->will($this->returnValue($response));
        
        $this->assertEquals('8',count($this->stub->mediaList()));		
	}
	public function testMediaShowStats()
	{
		$response = file_get_contents('responseMediaShowStats');

        $this->stub->expects($this->any())
        	->method('__send')
        	->will($this->returnValue($response));
        $expected = json_decode($response);
        
        $this->assertEquals($expected,$this->stub->mediaShowStats('770853'));		
	}
	public function testMediaEventRead()
	{
		$response = file_get_contents('responseEventRead');

        $this->stub->expects($this->any())
        	->method('__send')
        	->will($this->returnValue($response));
        $expected = json_decode($response);
        
        $this->assertEquals($expected,$this->stub->eventRead('770853'));		
	}
	public function testWistiaProjectList()
	{
		$response = file_get_contents('responseProjectList');
        $this->stub->expects($this->any())
        	->method('__send')
        	->will($this->returnValue($response));
        $this->assertEquals('3',count($this->stub->projectList()));		
	}
}