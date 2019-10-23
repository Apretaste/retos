<?php

class Service
{
	/**
	 * Display the daily challenge
	 *
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @author salvipascual
	 */
	public function _main(Request $request, Response $response)
	{
		$content = Challenges::getCurrent($request->person->id);

		// send data to the view
		$response->setTemplate('open.ejs', (array) $content);
	}

	/**
	 * Display the challenges completed
	 *
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @author salvipascual
	 */
	public function _done(Request $request, Response $response)
	{
		$content = [
			"total"      => Challenges::earned($request->person->id),
			"challenges" => Challenges::history($request->person->id)
		];

		// send data to the view
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
	public function _skip(Request $request, Response $response)
	{
		$result = Challenges::jump($request->person);
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
