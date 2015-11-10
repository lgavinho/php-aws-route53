<?php
/**
 * Created by PhpStorm.
 * User: lgavinho
 * Date: 09/11/15
 * Time: 12:35 PM
 */

namespace Cloud;

require 'vendor/autoload.php';

use Aws\Route53\Route53Client;
use Aws\Route53\Exception;

class Subdomain
{
    public $client = NULL;
    private $hostedZoneId = '';
    private $domain = '';

    /**
     * @param string $hostedZoneId
     * @param string $hostedZoneDomain
     * @param string $profileCredentials
     * @param string $region
     * @throws Exception
     */
    public function __construct($hostedZoneId, $hostedZoneDomain, $profileCredentials, $region = 'us-east-1')
    {
        $this->hostedZoneId = $hostedZoneId;
        $this->domain = $hostedZoneDomain;
        try {
            $this->client = Route53Client::factory(array(
                'profile'   => $profileCredentials,
                'region'    => $region,
                'version'   => '2013-04-01'
            ));
        }
        catch (Exception $e) {
            throw new Exception('AWS Client connection failed.');
        }
    }

    /**
     * Get all record sets from the hosted zone defined in hostedZoneId
     *
     * @return array
     * @throws Exception
     */
    public function getRecordSetsAsArray() {
        try {
            $result = $this->client->listResourceRecordSets(array(
                'HostedZoneId' => $this->hostedZoneId, // HostedZoneId is required
                //            'StartRecordName' => 'string',
                //            'StartRecordType' => 'CNAME',
                //            'StartRecordIdentifier' => 'string',
                //            'MaxItems' => '10',
            ));
            $recordsets = $result->toArray();
            return $recordsets['ResourceRecordSets'];
        }
        catch (Exception $e) {
            throw new Exception('Error when get Record Sets.');
        }
    }

    /**
     * Complete the subdomain with complete domain.
     *
     * Example: school -> school.smartlab.club
     *
     * @param string $subdomain
     * @return string
     */
    private function fullSubDomainURL($subdomain)
    {
        $pos = strpos($subdomain, $this->domain);
        if (!$pos) {
            $subdomain = $subdomain . '.' . $this->domain;
        }
        return $subdomain;
    }

    /**
     * Verify if subdomain already exist.
     * return true if exist.
     *
     * @param string $subdomain
     * @return bool
     * @throws Exception
     */
    public function exist($subdomain)
    {
        $subdomain = $this->fullSubDomainURL($subdomain);
        $recordsets = $this->getRecordSetsAsArray();
        foreach ($recordsets as $k => $recordset) {
            if ($subdomain . '.' == $recordset['Name']) {
                return True;
            }
        }
        return False;
    }

    /**
     * Validate if subdomain (name) is in valid format.
     *
     * @param string $name
     * @return bool
     */
    private function validateName($name)
    {
        $url = 'http://' . $name;
        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            return True;
        }
        return False;
    }

    /**
     * Create a CNAME new subdomain if it not already exist.
     *
     * @param string $name The name of subdomain. Example: school
     * @param string $destination The full domain that subdomain will redirect to. Example: smartlab.club
     * @return bool
     * @throws Exception
     */
    public function create($name, $destination)
    {
        $cname = $this->fullSubDomainURL($name);
        if (!$this->validateName($cname)) {
            throw new \InvalidArgumentException("Invalid subdomain name format.");
        }
        if (!$this->validateName($destination)) {
            throw new \InvalidArgumentException("Invalid destination domain format.");
        }
        if ($this->exist($cname)) {
            return True;
        }
        try {
            $result = $this->client->changeResourceRecordSets(array(
                'HostedZoneId' => $this->hostedZoneId, // HostedZoneId is required
                // ChangeBatch is required
                'ChangeBatch' => array(
                    'Comment' => 'Create a new subdomain ' . $cname,
                    // Changes is required
                    'Changes' => array(
                        array(
                            'Action' => 'CREATE', // Action is required
                            // ResourceRecordSet is required
                            'ResourceRecordSet' => array(
                                'Name' => $cname, // Name is required
                                'Type' => 'CNAME', // Type is required
                                'TTL' => 300,
                                'ResourceRecords' => array(
                                    array(
                                        'Value' => $destination, // Value is required
                                    ),
                                    // ... repeated
                                ),
                            ),
                        ),
                        // ... repeated
                    ),
                ),
            ));
            return True;
        }
        catch (Aws\Route53\Exception $e) {
            throw new Exception('Error when create the subdomain ' . $cname . '. ' . $e->getMessage());
        }
    }

    /**
     * Delete a exist subdomain.
     *
     * @param string $name  The name of subdomain.
     * @return bool
     * @throws Exception
     */
    public function delete($name)
    {
        $cname = $this->fullSubDomainURL($name);
        if (!$this->validateName($cname)) {
            throw new \InvalidArgumentException("Invalid subdomain name format.");
        }
        if (!$this->exist($cname)) {
            return True;
        }
        try {
            $result = $this->client->changeResourceRecordSets(array(
                'HostedZoneId' => $this->hostedZoneId, // HostedZoneId is required
                // ChangeBatch is required
                'ChangeBatch' => array(
                    'Comment' => 'Delete a subdomain ' . $cname,
                    // Changes is required
                    'Changes' => array(
                        array(
                            'Action' => 'DELETE', // Action is required
                            // ResourceRecordSet is required
                            'ResourceRecordSet' => array(
                                'Name' => $cname, // Name is required
                                'Type' => 'CNAME', // Type is required
                                'TTL' => 300,
                                'ResourceRecords' => array(
                                    array(
                                        'Value' => 'smartlab.club', // Value is required
                                    ),
                                    // ... repeated
                                ),
                            ),
                        ),
                        // ... repeated
                    ),
                ),
            ));
            return True;
        }
        catch (Aws\Route53\Exception $e) {
            throw new Exception('Error when create the subdomain ' . $cname . '. ' . $e->getMessage());
        }
    }


}