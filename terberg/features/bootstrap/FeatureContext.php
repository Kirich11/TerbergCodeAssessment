<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use PHPUnit\Framework\Assert;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private Client $client;
    private $response;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'localhost:80']);
        $this->response = null;
    }

    /**
     * @Given /^I have the payload:$/
     */
    public function iHaveThePayload(PyStringNode $requestPayload)
    {
        $this->requestPayload = $requestPayload;
    }

    /**
    * @When /^I request "(GET|PUT|POST|DELETE|PATCH) ([^"]*)"$/
    */
    public function iRequest($method, $resource)
    {
        try {
            if($method === 'POST') {
                $this->response = $this
                    ->client
                    ->$method($resource, ['body' => $this->requestPayload]);
            } else {
                $this->response = $this
                    ->client
                    ->$method($resource);
            }
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
    
            $this->response = $e->getResponse();
        }
    }

    /**
     * @Then /^the response property "([^"]*)" equals "([^"]*)"$/
     */
    public function theResponseHasProperty($propertyPath, $assertedValue)
    {
        $decodedResponse = json_decode($this->response->getBody()->getContents(), true);

        $propertyValue = $this->arrayGet($decodedResponse, $propertyPath);

        Assert::assertEquals($propertyValue, $assertedValue);
    }

        /**
     * @Then /^the response has error '(.*)'$/
     */
    public function theResponseHasError($assertedValue)
    {
        $decodedResponse = json_decode($this->response->getBody()->getContents(), true);

        Assert::assertContains($assertedValue, $decodedResponse['errors']);
    }

     /**
     * Get an item from an array using "dot" notation.
     *
     * @copyright   Taylor Otwell
     * @link        http://laravel.com/docs/helpers
     * @param       array   $array
     * @param       string  $key
     * @param       mixed   $default
     * @return      mixed
     */
    protected function arrayGet($array, $key)
    {
        if (is_null($key)) {
            return $array;
        }

        foreach (explode('.', $key) as $segment) {

            if (is_object($array)) {
                if (! isset($array->{$segment})) {
                    return;
                }
                $array = $array->{$segment};

            } elseif (is_array($array)) {
                if (! array_key_exists($segment, $array)) {
                    return;
                }
                $array = $array[$segment];
            }
        }

        return $array;
    }
}
