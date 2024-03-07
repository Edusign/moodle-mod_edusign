<?php

/**
 * @package     mod_edusign
 * @author      Sébastien Lampazona
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @docs        https://ext.edusign.fr/doc/
 */

namespace mod_edusign\classes\commons;

use Exception;
use stdClass;

class ApiCaller {

    public static function call($method, $url, $queryParams = [], $bodyParams = []) : stdClass{
        $method = strtoupper($method);
        // Initialiser cURL
        $ch = curl_init();

        // Construire l'URL avec les query params si nécessaire
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        // Configuration de cURL
        curl_setopt($ch, CURLOPT_URL, get_config('mod_edusign', 'apiurl') . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        // Configurer l'en-tête d'autorisation
        $headers = array(
            'Authorization: Bearer ' . get_config('mod_edusign', 'apikey'),
        );
        
        // Configurer la méthode HTTP
        if ($method === 'POST') {
            array_push($headers, 'Content-Type:application/json');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bodyParams));
        }
        else if ($method === 'PUT' || $method === 'PATCH') {
            array_push($headers, 'Content-Type:application/json');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bodyParams));
        }
        else if ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        else if ($method !== 'GET') {
            // Rien à configurer ici pour une requête GET
            // Le reste est une erreur de saisie
            throw new Exception('Unsupported method');
        }

        
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Exécuter la requête cURL
        $response = curl_exec($ch);
        // Gérer les erreurs si nécessaire
        if (curl_errno($ch)) {
            // Gérer l'erreur cURL
            throw new Exception(curl_error($ch));
        }
        else if ($response === "Internal Server Error" || $response === "Not Found" || $response === "Unauthorized" || $response === "Forbidden") {
            throw new Exception($response);
        }

        // Fermer la session cURL
        curl_close($ch);

        try {
            $responseJSON = json_decode($response);
            if ($response && !$responseJSON) throw new Exception($response);
        } catch (\Exception $e) {
            throw new Exception($response);
        }
        
        if ($responseJSON->status === 'error') {
            if (is_array($responseJSON->message)){
                throw new Exception('Validation errors');
            }
            throw new Exception($responseJSON->message);
        }
        // Retourner la réponse de l'API
        return $responseJSON;
    }

    public static function test()
    {
        return self::call('GET', '/v1/school');
    }
}