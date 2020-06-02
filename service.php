<?php

use Apretaste\Request;
use Apretaste\Response;
use Apretaste\Challenges;

class Service
{

	/**
	 * Display the daily challenge
	 *
	 * @param Request $request
	 * @param Response $response
	 *
	 * @throws \Framework\Alert
	 * @author salvipascual
	 */
	public function _main(Request $request, Response $response)
	{
		$content = (array) Challenges::getCurrent($request->person->id);

		//$content['name'] = utf8_encode($content['name']);
		//$content['description'] = utf8_encode($content['description']);

		// send data to the view
		if (trim($content['completed']) !== '') {
			//$response->setCache('day');
			$response->setTemplate('closed.ejs', $content);
			return;
		}

		$response->setTemplate('open.ejs', $content);
	}

	/**
	 * Display the challenges completed
	 *
	 * @param Request $request
	 * @param Response $response
	 *
	 * @throws \Framework\Alert
	 * @author salvipascual
	 */
	public function _done(Request $request, Response $response)
	{
		$content = [
			'total' => Challenges::earned($request->person->id),
			'challenges' => Challenges::history($request->person->id)
		];

		// send data to the view
		//$response->setCache('day');
		$response->setTemplate('done.ejs', $content);
	}

	/**
	 * Skips the current challenge and charges the user
	 *
	 * @param Request $request
	 * @param Response $response
	 *
	 * @throws \FeedException
	 * @throws \Framework\Alert
	 * @throws \Exception
	 * @author salvipascual
	 */
	public function _skip(Request $request, Response $response)
	{
		$result = false;

		try {
			$result = Challenges::jump($request->person->id);
		} catch (Exception $alert) {
			$response->setTemplate('message.ejs', [
			  'header' => 'Ha ocurrido un error',
			  'icon' => 'sentiment_very_dissatisfied',
			  'text' => 'El equipo t&eacute;nico ha sido notificado'
			]);
			throw $alert;
		}

		// if user do not have enough credits
		if ($result === false) {
			if (($request->person->credit ?? 0) < 0.2) {
				$response->setTemplate('message.ejs', [
					'header' => 'No tiene créditos',
					'icon' => 'sentiment_very_dissatisfied',
					'text' => 'Usted no tiene §0.2 de crédito que cuesta saltar un reto, por lo cual no pudimos continuar.',
				]);
			} else {
				$response->setTemplate('message.ejs', [
				  'header' => 'Ha ocurrido un error',
				  'icon' => 'sentiment_very_dissatisfied',
				  'text' => 'No se pudo saltar el reto. Espere a mañana.'
				]);
			}
			return;
		}

		$this->_main($request, $response);
	}
}
