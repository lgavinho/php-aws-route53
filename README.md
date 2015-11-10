# php-aws-route53

Wrapper to manage subdomains in AWS using AWS SDK PHP.

## Install dependences using composer

Run [composer](https://getcomposer.org/) install.

## Setup AWS Credentials

Create a new file **credentials** in **~/.aws/**

The content example:

```
[smartlab-dev]
aws_access_key_id = YOUR_AWS_ACCESS_KEY
aws_secret_access_key = YOUR_AWS_SECRET_KEY
```

In that example **smartlab-dev** is the name of **profile** AWS Credentials.

Change it if you want.

***Do not forget to change it in **\test\SubdomainTest > setUp method > property $profileCredentials**.

```
protected function setUp()
{
    $this->hostedZoneId = 'ZB9ZZP2BF96CI';
    $this->hostedZoneDomain = 'smartlab.club';
    $this->profileCredentials = 'smartlab-dev';
}
```

## Setup your Route53 Hosted Zone

Change in the same **\test\SubdomainTest > setUp method** the properties: hostedZoneDomain and hostedZoneId.

You can get this information in your AWS Console Route 53 > Hosted Zones.

## Subdomain Class

Method | Description
--- | ---
create | Create a new subdomain.
delete | Delete a subdomain.
exist | Check if subdomain exist.

### Example

```
$name_subdomain = 'subdomain';
$destination = 'example.com';
$subdomain = new Subdomain($hostedZoneId, $hostedZoneDomain, $profileCredentials);
$response = $subdomain->create($name_subdomain, $destination);
```


