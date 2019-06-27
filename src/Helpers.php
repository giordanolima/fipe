<?php

namespace GiordanoLima\Fipe;

class Helpers {

	/**
	 * Separa o mês e ano através baseado em uma string no formado mês/ano (Ex: junho/2017).
	 * 
	 * @param  string $mesAno
	 * @return array
	 */
	public static function separarMesAno(string $mesAno) {
		$mesAno = explode('/', trim($mesAno));
		$mes = $mesAno[0];
		$ano = $mesAno[1];

		return compact('mes', 'ano');
	}

	/**
	 * Obtém o número do mês através de seu nome.
	 * 
	 * @return string  $mes
	 * @return integer
	 */
	public static function numeroDoMes(string $mes) {
		$meses = [
			'janeiro' => 1,
			'fevereiro' => 2,
			'março' => 3,
			'abril' => 4,
			'maio' => 5,
			'junho' => 6,
			'julho' => 7,
			'agosto' => 8,
			'setembro' => 9,
			'outubro' => 10,
			'novembro' => 11,
			'dezembro' => 12
		];

		$mes = mb_strtolower($mes);

		return isset($meses[$mes]) ? $meses[$mes] : null;
	}

	public static function separarCodigoAno(string $codigoAno) {
		$codigoAno = explode('-', trim($codigoAno));
		$ano = $codigoAno[0];
		$combustivel = $codigoAno[1];

		return compact('ano', 'combustivel');
	}

}