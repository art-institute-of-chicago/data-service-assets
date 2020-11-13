<?php

namespace App;

trait SourceCallable
{

    public function authenticate()
    {

        $request = [
            'id' => "authenticate__data-service-assets__" . config('app.env') . date("Y-m-d_H:i:s"),
            'method' => "authenticate",
            'params' => [config('source.username'), config('source.password')],
            'dataContext' => "json",
            'jsonrpc' => "2.0"
        ];

        $response = json_decode($this->call(json_encode($request)));
        return $response->result->sessionKey ?? null;
    }

    protected function call($request)
    {

        $url = config('source.api_url');

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE );
        curl_setopt($ch, CURLOPT_POST,           TRUE );
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $request );
        curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: application/json'));

        // WEB-874: If connection or response take longer than 30 seconds, give up
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // TODO: Fix this networking error on target server?
        // Curl error: Unable to communicate securely with peer: requested domain name does not match the server's certificate.
        // if (config('app.env') == 'local') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // }

        //execute post
        $result = curl_exec($ch);
        if($result === false)
        {
            echo 'Curl error: ' . curl_error($ch);
        }

        //close connection
        curl_close($ch);

        return $result;

    }

}
