<?php

use Apretaste\Request;
use Apretaste\Response;
use Apretaste\Challenges;

class Service
{
	/**
	 * Display the list of challenges
	 *
	 * @param Request $request
	 * @param Response $response
	 * @author kumahacker
	 */
	public function _main(Request $request, Response $response)
	{
		// add new challenge for today
		Challenges::addChallenges($request->person->id);

		// get list of challenges
		$challenges = Challenges::getList($request->person->id);

		// send info to the view
		$response->setTemplate('open.ejs', ['challenges' => $challenges]);
	}

	/**
	 * Display the challenges completed
	 *
	 * @param Request $request
	 * @param Response $response
	 * @author kumahacker
	 */
	public function _done(Request $request, Response $response)
	{
		$content = [
			'total' => Challenges::earned($request->person->id),
			'challenges' => Challenges::history($request->person->id)
		];

		// send data to the view
		$response->setTemplate('done.ejs', $content);
	}

	/**
	 * Show the help docs
	 *
	 * @param Request $request
	 * @param Response $response
	 * @author kumahacker
	 */
	public function _ayuda(Request $request, Response $response)
	{
		$response->setTemplate('help.ejs');
	}

	/**
	 * Remove a challenge
	 *
	 * @param Request $request
	 * @param Response $response
	 * @author kumahacker
	 */
	public function _quitar(Request $request, Response $response)
	{
		Challenges::remove($request->person->id, $request->input->data->challenge);
		return $this->_main($request, $response);
	}
}
