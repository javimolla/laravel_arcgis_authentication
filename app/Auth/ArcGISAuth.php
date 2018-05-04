<?php

namespace App\Auth;

use GuzzleHttp\Client;

class ArcGISAuth {
    public static function attempt($username, $password, $expire) {
        $client = new Client();
        $response = $client->post(config('arcgis.token_url'), 
            ['form_params' => [
                'username' => $username,
                'password' => $password,
                'expiration' => $expire,
                'f' => 'json',
                'encrypted' => 'false',
                'client' => 'requestip',
                'referer' => '',
                'ip' =>  ''
            ]]
        );
        if ($response->getStatusCode() == 200) {
            $responseJSON = json_decode($response->getBody()->getContents(), true);
            if (!array_key_exists("error", $responseJSON)) {
                return $responseJSON["token"];
            }
        }

        return null;
    }
}