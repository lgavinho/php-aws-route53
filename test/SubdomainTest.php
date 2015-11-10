<?php
/**
 * Created by PhpStorm.
 * User: lgavinho
 * Date: 09/11/15
 * Time: 1:08 PM
 */
namespace Cloud\Test;

require 'Subdomain.php';

use Cloud\Subdomain;

class SubdomainTest extends \PHPUnit_Framework_TestCase
{
    protected $hostedZoneDomain = '';
    protected $hostedZoneId = '';
    protected $profileCredentials = '';

    protected function setUp()
    {
        $this->hostedZoneId = 'ZB9ZZP2BF96CI';
        $this->hostedZoneDomain = 'smartlab.club';
        $this->profileCredentials = 'smartlab-dev';
    }

    public function testCredentialsSuccess()
    {
        $subdomain = new Subdomain($this->hostedZoneId, $this->hostedZoneDomain, $this->profileCredentials);
        $this->assertInstanceOf('\Cloud\Subdomain', $subdomain);
    }

    public function testGetRecordsetsSuccess()
    {
        $subdomain = new Subdomain($this->hostedZoneId, $this->hostedZoneDomain, $this->profileCredentials);
        $recordsets = $subdomain->getRecordSetsAsArray();
        $this->assertNotFalse($recordsets);
        $this->assertEquals($this->hostedZoneDomain . '.', $recordsets[0]['Name']);
    }

    public function testCreateSubdomainSuccess()
    {
        $name_subdomain = 'test';
        $destination = 'smartlab.club';
        $subdomain = new Subdomain($this->hostedZoneId, $this->hostedZoneDomain, $this->profileCredentials);
        $response = $subdomain->create($name_subdomain, $destination);
        $this->assertTrue($response);

        $exist = $subdomain->exist($name_subdomain);
        $this->assertTrue($exist);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateSubdomainFail()
    {
        $name_subdomain = '%invalid@name$';
        $destination = 'smartlab.club';
        $subdomain = new Subdomain($this->hostedZoneId, $this->hostedZoneDomain, $this->profileCredentials);
        $response = $subdomain->create($name_subdomain, $destination);
    }

    public function testDeleteSubdomainSuccess()
    {
        $name_subdomain = 'test';
        $subdomain = new Subdomain($this->hostedZoneId, $this->hostedZoneDomain, $this->profileCredentials);
        $response = $subdomain->delete($name_subdomain);
        $this->assertTrue($response);

        $exist = $subdomain->exist($name_subdomain);
        $this->assertFalse($exist);
    }

}

