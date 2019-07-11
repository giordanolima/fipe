<?php

namespace GiordanoLima\Fipe;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use GiordanoLima\Fipe\FipeClient;

class Fipe {

    const FIPE_ERRO = 'erro';

	/**
	 * Adaptação do GuzzleClient para a Fipe.
	 */
	protected $client;

	/**
	 * Tipo de veículo que será utilizado na busca. 
	 */
	protected $tipo;

	/**
	 * Código da referência (mês/ano).
	 */
	protected $referencia;

	/**
	 * Cache: Lista de referências.
	 */
	protected $referencias;

	/**
	 * Cache: Referência vigente baseada na data atual.
	 */
	protected $referenciaAtual;
	
	/**
	 * Construtor.
	 */
	function __construct() {
		$this->client = new FipeClient;
	}

	/**
	 * Define o tipo de veículo que será utilizado na busca.
	 * 
	 * @param  integer $referencia ID do tipo de veículo.
	 * @return FipeLib
	 */
	public function definirTipo($tipo) {
		$this->tipo = $tipo;
		return $this;
	}

	/**
	 * Buscar o tipo de veículo que será utilizado na busca.
	 * 
	 * @param  integer $referencia ID do tipo de veículo.
	 * @return FipeLib
	 */
	public function getTipo() {
		return $this->tipo;
	}

	/**
	 * Define o mês/ano de referência que será utilizada na busca.
	 * 
	 * @param  integer $referencia ID da referência.
	 * @return FipeLib
	 */
	public function definirReferencia($referencia) {
		$this->referencia = $referencia;
		return $this;
	}

	/**
	 * Obtém o código da referência definida.
	 * Se a referência não foi definida será utilizada a do mês/ano vigente.
	 * 
	 * @return FipeLib
	 */
	public function referencia() {
		if ($this->referencia) {
			return $this->referencia;
		} else if ($this->referenciaAtual()) {
			return $this->referenciaAtual()->codigo;
		}

		return null;
	}

	/**
	 * Lista de referências (mês/ano) da Fipe.
	 * 
	 * @return Collection
	 */
	public function referencias() {
		if ($this->referencias) return $this->referencias;

		$response = $this->client->post('ConsultarTabelaDeReferencia', []);
		$referencias = new Collection();

		foreach ($response as $key => $referencia) {
			$mesAno = Helpers::separarMesAno($referencia->Mes);

			$referencias->push((object) [
				'codigo' => $referencia->Codigo,
				'referencia' => $referencia->Mes,
				'mes' => Helpers::numeroDoMes($mesAno['mes']),
				'ano' => $mesAno['ano'],
			]);
		}

		return $this->referencias = $referencias;
	}

	/**
	 * Obtém a referência vigente baseada na data atual.
	 * 
	 * @return object
	 */
	public function referenciaAtual() {
		if ($this->referenciaAtual) return $this->referenciaAtual;

		$referenciaMaisRecente = $this->referencias()->first();
		$dataAtual = Carbon::now();

		if ($referenciaMaisRecente->mes == $dataAtual->month && $referenciaMaisRecente->ano == $dataAtual->year) {
			return $this->referenciaAtual = $referenciaMaisRecente;
		}

		return $this->referenciaAtual = null;
	}

	/**
	 * Obtém a referência da data informada.
	 *
	 * @param integer $mes
	 * @param integer $ano
	 * @return object
	 */
	public function referenciaDaData($mes, $ano) {
		return $this->referencias()->where('mes', $mes)->where('ano', $ano)->first();
	}

	/**
	 * Lista de marcas.
	 * 
	 * @return Collection
	 */
	public function marcas() {
		$response = $this->client->post('ConsultarMarcas', [
			'codigoTipoVeiculo' => $this->tipo,
			'codigoTabelaReferencia' => $this->referencia(),
		]);

		return Transformers::marcas($response);
	}

	/**
	 * Modelos de uma marca.
	 * 
	 * @return Collection
	 */
	public function modelos($codigoMarca) {
		$response = $this->client->post('ConsultarModelos', [
			'codigoTipoVeiculo' => $this->tipo,
			'codigoTabelaReferencia' => $this->referencia(),
			'codigoMarca' => $codigoMarca,
		]);

		return Transformers::modelos($response->Modelos);
	}

	/**
	 * Anos de um modelo.
	 * 
	 * @return Collection
	 */
	public function anos($codigoMarca, $codigoModelo) {
		$response = $this->client->post('ConsultarAnoModelo', [
			'codigoTipoVeiculo' => $this->tipo,
			'codigoTabelaReferencia' => $this->referencia(),
			'codigoModelo' => $codigoModelo,
			'codigoMarca' => $codigoMarca
		]);

		return Transformers::anos($response);
	}

	/**
	 * Anos de um modelo através do código da Fipe do veículo.
	 * 
	 * @return Collection
	 */
	public function anosPorCodigoFipe($codigoFipe) {
		$response = $this->client->post('ConsultarAnoModeloPeloCodigoFipe', [
			'codigoTipoVeiculo' => $this->tipo,
			'codigoTabelaReferencia' => $this->referencia(),
			'modeloCodigoExterno' => $codigoFipe,
		]);

		return Transformers::anos($response);
	}

	/**
	 * Anos de modelo através da marca.
	 *
	 * @return Collection
	 */
	public function anosPorMarca($codigoMarca) {
		$response = $this->client->post('ConsultarModelos', [
			'codigoTipoVeiculo' => $this->tipo,
			'codigoTabelaReferencia' => $this->referencia(),
			'codigoMarca' => $codigoMarca,
		]);

        if ($response === Fipe::FIPE_ERRO) {
            return Transformers::anos($response);
        }

		return Transformers::anos($response->Anos);
	}

	/**
	 * Busca o valor médio de um veículo.
	 * 
	 * @param  integer $tipo        
	 * @param  integer $referencia   
	 * @param  integer $codigoMarca  
	 * @param  integer $codigoModelo 
	 * @param  string  $codigoAno
	 * @return object
	 */
	public function veiculo($codigoMarca, $codigoModelo, $codigoAno) {
		$codigoAno = Helpers::separarCodigoAno($codigoAno);

		$response = $this->client->post('ConsultarValorComTodosParametros', [
			'codigoTipoVeiculo' => $this->tipo,
			'codigoTabelaReferencia' => $this->referencia(),
			'codigoMarca' => $codigoMarca,
			'codigoModelo' => $codigoModelo,
			'anoModelo' => $codigoAno['ano'],
			'codigoTipoCombustivel' => $codigoAno['combustivel'],
			'tipoConsulta' => 'tradicional'
		]);

		return Transformers::veiculo($response);
	}

	/**
	 * Busca o valor médio de um veículo através do código da Fipe.
	 * 
	 * @param  integer $tipo
	 * @param  integer $referencia
	 * @param  string  $codigoAno
	 * @param  string  $codigoFipe
	 * @return object
	 */
	public function veiculoPorCodigoFipe($codigoAno, $codigoFipe) {
		$codigoAno = Helpers::separarCodigoAno($codigoAno);

		$response = $this->client->post('ConsultarValorComTodosParametros', [
			'codigoTipoVeiculo' => $this->tipo,
			'codigoTabelaReferencia' => $this->referencia(),
			'anoModelo' => $codigoAno['ano'],
			'codigoTipoCombustivel' => $codigoAno['combustivel'],
			'tipoConsulta' => 'codigo',
			'modeloCodigoExterno' => $codigoFipe
		]);

		return Transformers::veiculo($response);
	}

}