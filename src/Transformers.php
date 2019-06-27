<?php

namespace GiordanoLima\Fipe;

use Illuminate\Support\Collection;

/**
 * Transforma a resposta da API da FIPE em Collection|object.
 */	
class Transformers {

	/**
	 * Coleção de Marcas.
	 * 
	 * @param  $response
	 * @return Collection
	 */	
	public static function marcas($response) {
		$marcas = new Collection();
		
		foreach ($response as $key => $marca) {
			$marcas->push((object) [
				'codigo' => $marca->Value,
				'nome' => $marca->Label,
			]);
		}

		return $marcas;
	}

	/**
	 * Coleção de Modelos.
	 * 
	 * @param  $response
	 * @return Collection
	 */	
	public static function modelos($response) {
		$modelos = new Collection();
		
		foreach ($response as $key => $modelo) {
			$modelos->push((object) [
				'codigo' => $modelo->Value,
				'nome' => $modelo->Label,
			]);
		}

		return $modelos;
	}

	/**
	 * Coleção de Anos.
	 * 
	 * @param  $response
	 * @return Collection
	 */
	public static function anos($response) {
	    if ($response != Fipe::FIPE_ERRO){

            $anos = new Collection();

            foreach ($response as $key => $ano) {
                $codigoAno = Helpers::separarCodigoAno($ano->Value);

                $anos->push((object) [
                    'codigo' => $ano->Value,
                    'nome' => $ano->Label,
                    'ano' => $codigoAno['ano'], // Veículos com ano 32000 significam que são Zero KM
                    'codigo_combustivel' => $codigoAno['combustivel'],
                ]);
            }

            return $anos;
        }
		return false;
	}

	/**
	 * Veículo.
	 * 
	 * @param  $response
	 * @return object
	 */
	public static function veiculo($response) {
		$veiculo = [
			'codigo' => $response->CodigoFipe,
			'valor' => $response->Valor,
			'marca' => $response->Marca,
			'modelo' => $response->Modelo,
			'ano' => $response->AnoModelo,
			'referencia' => $response->MesReferencia,
			'tipo' => $response->TipoVeiculo,
			'combustivel' => $response->Combustivel,
			'combustivel_sigla' => $response->SiglaCombustivel,
		];

		return (object) $veiculo;
	}

}