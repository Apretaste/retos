<?php

class Service
{
	/**
	 * Display the daily challenge
	 *
	 * @author salvipascual
	 * @param Request  $request
	 * @param Response $response
	 */
	public function _main (Request $request, Response $response)
	{
		$content = [
			"icon" => "chrome_reader_mode",
			"title" => "Leer los Términos de uso",
			"desc" => "Nuestros términos de uso le muestra reglas básicas de uso que cada usuario de Apretaste debe cumplir.",
			"credits" => "0.2",
			"link" => "TERMINOS",
		];

		// send data to the view
		$response->setTemplate('open.ejs', $content);
//		$response->setTemplate('closed.ejs', $content);
	}

	/**
	 * Display the challenges completed
	 *
	 * @author salvipascual
	 * @param Request  $request
	 * @param Response $response
	 */
	public function _done (Request $request, Response $response)
	{
		$content = [
			"total" => 3.5,
			"challenges" => []
		];

		$challenge = new stdClass();
		$challenge->icon = "ac_unit";
		$challenge->title = "Completar su perfil de la app y poner foto";
		$challenge->completed = "09/28/2019";
		$challenge->credits = 2;
		$content["challenges"][] = $challenge;

		$challenge = new stdClass();
		$challenge->icon = "all_inclusive";
		$challenge->title = "Decirnos donde escuchó sobre la app";
		$challenge->completed = "09/22/2019";
		$challenge->credits = 1;
		$content["challenges"][] = $challenge;

		$challenge = new stdClass();
		$challenge->icon = "beach_access";
		$challenge->title = "Leer como ganar crédito desde el servicio Crédito";
		$challenge->completed = "09/21/2019";
		$challenge->credits = 1.5;
		$content["challenges"][] = $challenge;

		// send data to the view
		$response->setTemplate('done.ejs', $content);
	}

	/**
	 * Skips the current challenge and charges the user
	 *
	 * @author salvipascual
	 * @param Request  $request
	 * @param Response $response
	 */
	public function _skip (Request $request, Response $response)
	{
		// if user do not have enough credits
		if(true) {
			return $response->setTemplate('message.ejs', [
				'header' => 'No tiene créditos',
				'icon' => 'sentiment_very_dissatisfied',
				'text' => 'Usted no tiene §0.2 de crédito que cuesta saltar un reto, por lo cual no pudimos continuar.',
			]);
		}
	}
}
