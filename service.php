<?php

class RetosService extends ApretasteService
{
	/**
	 * Display the daily challenge
	 *
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @author salvipascual
	 */
	public function _main(Request $request, Response &$response)
	{
		$content = (array) Challenges::getCurrent($request->person->id);

		//$content['name'] = utf8_encode($content['name']);
		//$content['description'] = utf8_encode($content['description']);

		// send data to the view
		if (trim($content['completed']) !=='') {
			$this->response->setCache('day');
			$this->response->setTemplate('closed.ejs', $content);
			return;
		}

		$this->response->setTemplate('open.ejs', $content);
	}

	/**
	 * Display the challenges completed
	 *
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @author salvipascual
	 */
	public function _done(Request $request, Response &$response)
	{
		$content = [
			"total"      => Challenges::earned($request->person->id),
			"challenges" => Challenges::history($request->person->id)
		];

		// send data to the view
		$response->setCache('day');
		$response->setTemplate('done.ejs', $content);
	}

	/**
	 * Skips the current challenge and charges the user
	 *
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @throws \Exception
	 * @author salvipascual
	 */
	public function _skip(Request $request, Response &$response)
	{
		$result = Challenges::jump($request->person->id);
		// if user do not have enough credits
		if ($result===false) {
			$response->setTemplate('message.ejs', [
				'header' => 'No tiene créditos',
				'icon'   => 'sentiment_very_dissatisfied',
				'text'   => 'Usted no tiene §0.2 de crédito que cuesta saltar un reto, por lo cual no pudimos continuar.',
			]);

			return;
		}

		$this->_main($request, $response);
	}
}
