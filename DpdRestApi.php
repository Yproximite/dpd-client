<?php
namespace dpdClient;

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\Response;


/**
 * DPD REST API library/class
 * Interaction with DPD REST Web services.
 * documentation : http://getdpd.com/docs/api/DPDAPIReference.pdf
 */
class DpdRestApi
{
    private $url; // URL for RETS service.
    private $partnerName; // Name of your API partner.
    private $partnerToken; // Token of your API partner.
    private $username; // CustomerID of the DPD customer. Assigned by DPD.
    private $password; // Customer password of the DPD customer. Assigned by DPD.
    private $version; // Version.
    private $language; // Message language.

    /**
     * Constructor which initializes the consumer.
     *
     * @param string $partnerName  Name of your API partner.
     * @param string $partnerToken Token of your API partner.
     * @param string $username       CustomerID of the DPD customer. Assigned by DPD.
     * @param string $password    Customer password of the DPD customer. Assigned by DPD.
     * @param string $language     Default language of the API
     */
    public function __construct($partnerName, $partnerToken, $username, $password, $language = 'fr_FR')
    {
        $this->url          = 'https://api.getdpd.com/v2/';
        $this->partnerName  = $partnerName;
        $this->partnerToken = $partnerToken;
        $this->username     = $username;
        $this->password     = $password;
        $this->version      = '100';
        $this->language     = $language;
    }

    /**
     * Make a REST request to a DPD service.
     *
     * @param $methodUrl
     * @param $data
     * @param $method
     *
     * @return array;
     */
    public function makeRequest($methodUrl, $data = array(), $method = 'GET')
    {
        $guzzleClient   = new GuzzleHttp\Client(['base_uri' => $this->url]);

        if ($data != array()) {
            $guzzleResponse = $guzzleClient->request($method, $methodUrl, ['auth' => [$username, $password], 'query' => $data]);
        } else {
            $guzzleResponse = $guzzleClient->request($method, $methodUrl, ['auth' => [$username, $password]]);
        }

        $code = $response->getStatusCode();

        $status = $guzzleResponse->getStatusCode();

        if (200 != $status) {
            // error during request
            $log = "";
            foreach ($guzzleResponse->getHeaders() as $name => $values) {
                $log .= $name . ': ' . implode(', ', $values) . "\r\n";
            }

            throwError($status, $log);
        }

        return $guzzleResponse->getBody();
    }

    private function throwError($status, $log)
    {
        switch($status) {
            case 401:
                throw new Exception('Error occured during guzzle execution. Invalid credentials. Additional info: ' . $log);
                break;

            case 403:
                throw new Exception('Error occured during guzzle execution. You don\'t have access to the ressource. Additional info: ' . $log);
                break;

            case 404:
                throw new Exception('Error occured during guzzle execution. Ressource could not be found. Additional info: ' . $log);
                break;

            case 412:
                throw new Exception('Error occured during guzzle execution. Validation error. Additional info: ' . $log);
                break;

            case 500:
                throw new Exception('Error occured during guzzle execution. Server error. Additional info: ' . $log);
                break;

            case 503:
                throw new Exception('Error occured during guzzle execution. Service unavailable. Additional info: ' . $log);
                break;

            default:
                throw new Exception('Error occured during guzzle execution. Additional info: ' . $log);
                break;
        }
    }

    /**
     * @return array list of storefronts
     */
    public function getStorefronts()
    {
        return makeRequest('storefronts');
    }

    /**
     * return the specified storefront
     *
     * @param int $id
     *
     * @return array attributes of the storefront
     */
    public function getStorefront($id)
    {
        $methodUrl = 'storefronts/'.$id;
        return makeRequest($methodUrl);
    }

    /**
     * @param int $storefrontId
     *
     * @return array list of products
     */
    public function getProducts($storefrontId = "")
    {
        if ($storefrontId != "") {
            $data = ['storefront_id' => $storefrontId];
            return makeRequest('products', $data);
        }

        return makeRequest('products');
    }

    /**
     * return the specified product
     *
     * @param int $id
     *
     * @return array attributes of the product
     */
    public function getProduct($id)
    {
        $methodUrl = 'products/'.$id;
        return makeRequest($methodUrl);
    }

    /**
     * @param string $status            purchase status
     * @param string $productId
     * @param string $storefrontId
     * @param string $customerId
     * @param string $subscriberId
     * @param string $customerEmail
     * @param string $customerFirstName
     * @param string $customerLastName
     * @param string $dateMin           purchase created after
     * @param string $dateMax           purchase created before
     * @param string $total             purchase total
     * @param string $totalOp           how to compare the total param ("eq" => "=", "ne" => "!=", "gt" ">", "lt" => "<")
     * @param string $ship              purchase with tangible goods that have not been shipped
     *
     * @return array                   list of purchases
     */
    public function getPurchases(
        $status = "",
        $productId = "",
        $storefrontId = "",
        $customerId = "",
        $subscriberId = "",
        $customerEmail = "",
        $customerFirstName = "",
        $customerLastName = "",
        $dateMin = "",
        $dateMax = "",
        $total = "",
        $totalOp = "",
        $ship = ""
    )
    {
        $data = [];

        if ($status !=  "") {
            $data['status'] = $status;
        } elseif ($productId !=  "") {
            $data['product_id'] = $productId;
        } elseif ($storefrontId !=  "") {
            $data['storefront_id'] = $storefrontId;
        } elseif ($customerId !=  "") {
            $data['customer_id'] = $customerId;
        } elseif ($subscriberId !=  "") {
            $data['subscriber_id'] = $subscriberId;
        } elseif ($customerEmail !=  "") {
            $data['customer_email'] = $customerEmail;
        } elseif ($customerFirstName !=  "") {
            $data['customer_first_name'] = $customerFirstName;
        } elseif ($customerLastName !=  "") {
            $data['customer_last_name'] = $customerLastName;
        } elseif ($dateMin !=  "") {
            $data['date_min'] = $dateMin;
        } elseif ($dateMax !=  "") {
            $data['date_max'] = $dateMax;
        } elseif ($total !=  "") {
            $data['total'] = $total;
        } elseif ($totalOp !=  "") {
            $data['total_op'] = $totalOp;
        } elseif ($ship !=  "") {
            $data['ship'] = $ship;
        }

        if ($data != []) {
            return makeRequest('purchases', $data);
        }

        return makeRequest('purchases');
    }

    /**
     * return the specified purchase
     *
     * @param int $id
     *
     * @return array attributes of the purchase
     */
    public function getPurchase($id)
    {
        $methodUrl = 'purchases/'.$id;
        return makeRequest($methodUrl);
    }

    /**
     * @return array list of subcribers
     */
    public function getSubscribers($storefrontId)
    {
        $methodUrl = 'storefronts/'.$storefrontId.'/subscribers';
        return makeRequest($methodUrl);
    }

    /**
     * return the specified subscriber
     *
     * @param int $storefrontId
     * @param int $subscriberId
     * @param int $subscriberMail
     *
     * @return array attributes of the subscriber
     */
    public function getSubscriber($storefrontId, $subscriberId = "", $subscriberMail = "")
    {
        if ($subscriberId != "") {
            $methodUrl = 'storefronts/'.$storefrontId.'/subscribers/'.$subscriberId;
            return makeRequest($methodUrl);
        } elseif ($subscriberMail != "") {
            $methodUrl = 'storefronts/'.$storefrontId.'/subscribers';
            $data = ['username' => $subscriberMail];
            return makeRequest($methodUrl, $data);
        } else {
            throw new Exception('Please specify either the subscriber\'s id or the subscriber\'s mail');
        }
    }

    /**
     * return the status of a subscriber
     *
     * @param int    $storefrontId
     * @param int    $subscriberId
     * @param string $subscriberMail
     *
     * @return array status of the subscriber
     */
    public function verifySubscriber($storefrontId, $subscriberId = "", $subscriberMail = "")
    {
        $methodUrl = 'storefronts/'.$storefrontId.'/subscribers/verify';

        if ($subscriberId != "") {
            $data = ['id' => $subscriberId];
        } elseif ($subscriberMail != "") {
            $data = ['username' => $subscriberMail];
        } else {
            throw new Exception('Please specify either the subscriber\'s id or the subscriber\'s mail');
        }

        return makeRequest($methodUrl, $data);
    }

    /**
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $productId
     * @param boolean $receivesNewsletters
     * @param string $dateMin
     * @param string $dateMax
     *
     * @return array list of customers
     */
    public function getCustomers($email = "", $firstName = "", $lastName = "", $productId = "", $receivesNewsletters = "",  $dateMin = "", $dateMax = "")
    {
        $data = [];

        if ($email !=  "") {
            $data['email'] = $email;
        } elseif ($firstName !=  "") {
            $data['first_name'] = $firstName;
        } elseif ($lastName !=  "") {
            $data['last_name'] = $lastName;
        } elseif ($productId !=  "") {
            $data['product_id'] = $productId;
        } elseif ($receivesNewsletters !=  "") {
            $data['receives_newsletters'] = $receivesNewsletters;
        } elseif ($dateMin !=  "") {
            $data['date_min'] = $dateMin;
        } elseif ($dateMax !=  "") {
            $data['date_max'] = $dateMax;
        }

        if ($data != []) {
            return makeRequest('customers', $data);
        }

        return makeRequest('customers');
    }

    /**
     * return the specified customer
     *
     * @param int $id
     *
     * @return array attributes of the customer
     */
    public function getCustomer($id)
    {
        $methodUrl = 'customers/'.$id;
        return makeRequest($methodUrl);
    }
}
