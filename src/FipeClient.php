<?php

namespace GiordanoLima\Fipe;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

class FipeClient {
	
	const API_URL = 'https://veiculos.fipe.org.br/api/veiculos/';

	const API_REFER_URL = 'https://veiculos.fipe.org.br';

	protected $client;

	/**
	 * Construtor.
	 */
	function __construct() {
		$this->client = new GuzzleClient([
			'base_uri' => self::API_URL,
			// 'timeout'  => 3.0,
			'headers'  => [
				'Referer' => self::API_REFER_URL
			]
		]);
	}

	/**
	 * 
	 * 
	 * @return [type] [description]
	 */
	public function post($endpoint, $params) {
		try {
			$request = $this->client->post($endpoint, [
	            'form_params' => $params,
	        ]);

	        $response = json_decode($request->getBody());

	        if (isset($response->codigo) && isset($response->erro)) {
            	//throw new \Exception($response->erro);
                return Fipe::FIPE_ERRO;
	        }

	        return $response;
		}  catch (ClientException $e) { 
            throw new \Exception('Fipe API error: `{$e->getResponse()->getBody()->getContents()}`');
        }
	}
	
}