<?php

namespace ACPClient;

use ACPClient\RESTClientResponse;
use Illuminate\Support\Arr;

class RESTClient
{
    /**
    * @var array
    */
    protected $credentials;

    /**
    * @var string
    */
    protected $token;

    /**
    * @var array
    */
    protected $options = [];

    /**
    * @var array
    */
    protected $headers = [];

    /**
    * @var boolean
    */
    public $connected = false;

    /**
    * @var boolean
    */
    public $debug = false;

    /**
    * @var array
    */
    protected $HTTP_methods = ['get', 'post', 'delete', 'put', 'options'];

    /**
     * Creates a new REST client instance
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
    }


    /**
     * Execute HTTP methods
     */
    protected function process_method($url, $method = 'GET', $parameters = [], $headers = [])
    {
        isset($this->last_message) and $this->last_message = null;
        isset($this->last_error) and $this->last_error = null;

        $client = clone $this;
        $method = strtoupper($method);

        $curl_options = array(
            CURLOPT_URL => rtrim($this->endpoint, '/') .'/'. ltrim($url, '/'),
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPAUTH => CURLAUTH_ANY,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => isset($this->user_agent) ? $this->user_agent : @$_SERVER['HTTP_USER_AGENT']
        );

        #
        # initialise cURL
        #
        $client->curl_handle = curl_init();

        #
        # add authentication
        #
        if (isset($client->options['username']) and isset($client->options['password'])) {
            $curl_options[CURLOPT_USERPWD] = sprintf("%s:%s", $client->options['username'], $client->options['password']);
        }

        #
        # set headers
        #
        $headers = is_array($headers) ? array_merge($this->headers, $headers) : $headers;
        $headers and $curl_options[CURLOPT_HTTPHEADER] = $headers;

        # set options
        is_array($parameters) and $parameters = array_merge($this->options, $parameters);

        if ($method <> 'GET') {
            if ($method == 'POST') {
                $curl_options[CURLOPT_POST] = true;
            }
            else {
                $curl_options[CURLOPT_CUSTOMREQUEST] = $method;
            }

            $curl_options[CURLOPT_POSTFIELDS] = is_array($parameters) ? http_build_query($parameters) : $parameters;
        }
        else {
            is_array($parameters) and $curl_options[CURLOPT_URL] .= '?'. http_build_query($parameters);
        }

        curl_setopt_array($client->curl_handle, $curl_options);

        # set additional cURL options
        curl_setopt($client->curl_handle, CURLOPT_HTTPHEADER, $headers);

        # execute
        $this->process_response(curl_exec($client->curl_handle));

        $this->last_error = curl_errno ($client->curl_handle);
        $this->last_message = curl_error($client->curl_handle);

        curl_close($client->curl_handle);

        return $this;
    }

    /**
     * Process cURL response
     */
    public function process_response($response)
    {
        $this->response = new RESTClientResponse($response);

        return $this;
    }

    /**
     * Get connection status
     */
    public function connected()
    {
        return $this->connected;
    }

    /**
     * Get last error message
     */
    public function get_last_error()
    {
        $response = isset($this->response->error) ? $this->response->error : null;
        $message = is_array($response) && isset($response['message']) ? $response['message'] : $response;
        empty($message) and $message = $this->last_message;

        return $message;
    }

    /**
     * Handle call to __call method.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function __call($method, $arguments = [])
    {
        #
        # Set options/headers
        #
        if (in_array($method, ['options', 'headers'])) {
            $options = array_shift($arguments);

            if (is_array($options)) {
                $this->$method = array_merge($this->options, $options);
            }
            else {
                if (is_null($options)) {
                    $this->$method; // getter
                }
                else {
                    $this->$method[$option] = $arguments ? array_shift($arguments) : null;
                }
            }

            return $this->$method;
        }

        #
        # Process HTTP methods
        #
        if (in_array($method, $this->HTTP_methods)) {
            $url = array_shift($arguments);
            $parameters = [];
            $headers = [];

            $params = array_shift($arguments);
            if ($params) {
                isset($params['parameters']) and $parameters = $params['parameters'];
                isset($params['headers']) and $headers = $params['headers'];
            }

            return $this->process_method($url, $method, $parameters, $headers);
        }

        #
        # custom methods
        #
        switch ($method) {
            case 'authenticate':
                $proc = $this->process_method('authenticate', 'POST', $this->credentials);
                isset($this->response->token) and $this->connected = true;

                return $proc;
        }

        if (!method_exists($this, $method)) {
            throw new \Exception('Method not allowed');
        }

        return null;
    }
}
