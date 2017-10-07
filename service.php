<?php

/**
 * Apretaste!
 * Retos subservice
 *
 * @author  kumahacker <kumahavana@gmail.com>
 * @version 1.0
 */
class Retos extends Service
{
	public $goals = [];
	private $connection = null;

	/**
	 * Singleton connection to db
	 *
	 * @author kuma
	 * @return Connection
	 */
	private function connection()
	{
		if(is_null($this->connection))
		{
			$this->connection = new Connection();
		}

		return $this->connection;
	}

	/**
	 * Query assistant
	 *
	 * @author kuma
	 * @example
	 *      $this->q("SELECT * FROM TABLE"); // (more readable / SQL is autodescriptive)
	 *
	 * @param string $sql
	 *
	 * @return array
	 */
	private function q($sql)
	{
		return $this->connection()->deepQuery($sql);
	}

	/**
	 * Main response
	 *
	 * @param \Request $request
	 *
	 * @return \Response
	 */
	public function _main(Request $request)
	{
		$this->initGoals($request);

		$response = new Response();
		$response->setResponseSubject("Tus retos");
		$response->createFromTemplate('basic.tpl', [
			'goals' => $this->goals
		]);

		return $response;
	}

	/**
	 * Init the goals model
	 *
	 * @param Request $request
	 */
	public function initGoals($request)
	{
		$this->goals = [
			'initial' => [
				'title' => 'Retos iniciales',
				'prize' => 2,
				'checker' => function($request)
				{
					$r = $this->q("SELECT count(*) as total FROM _retos WHERE email = '{$request->email}';");

					return intval($r[0]->total) > 0;
				},
				'goals' => [
					[
						'caption' => 'Leer los [Terminos] del servicio',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM utilization WHERE requestor = '{$request->email}' AND service = 'terminos'"
						]
					],
					[
						'caption' => 'Completar su [perfil] de usuario y poner foto',
						'checker' => [
							'type' => 'callable',
							'data' => function($data)
							{
								return $this->utils->getProfileCompletion($data['request']->email) == 100;
							}
						]
					],
					[
						'caption' => 'Leer las maneras de ganar [credito]',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM utilization WHERE requestor = '{$request->email}' AND service = 'web' AND query = 'credito.apretaste.com'"
						]
					],
					[
						'caption' => 'Abrir el [Soporte] por primera vez',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM utilization WHERE requestor = '{$request->email}' AND service = 'soporte'"
						]
					],
					[
						'caption' => '[Referir] a un amigo y ganar creditos',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM _referir WHERE user = '{$request->email}'"
						]
					],
					[
						'caption' => 'Responder una [Encuesta] y ganar creditos',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM (
										  SELECT id, questions, answers, questions - answers as unanswered
										  FROM ( SELECT id,
										           (SELECT COUNT(C.id) as total FROM _survey_question C WHERE survey = A.id) as questions,
										           (SELECT COUNT(D.answer) as answers FROM _survey_answer_choosen D WHERE survey = A.id AND email='{$request->email}') as answers
										         FROM _survey A WHERE active = 1 ) B
										  WHERE questions > 0 AND questions - answers <= 0
									  ) E WHERE unanswered <= 0;"
						]
					],
					[
						'caption' => 'Ver la lista de [Concursos] abiertos',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM utilization WHERE requestor = '{$request->email}' AND service = 'concurso'"
						]
					],
					[
						'caption' => 'Revisar las notas en la [Pizarra]',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM utilization WHERE requestor = '{$request->email}' AND service = 'pizarra'"
						]
					],
					[
						'caption' => 'Escribir o votar por una [Sugerencia]',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM utilization WHERE requestor = '{$request->email}' AND service = 'sugerencias' AND (subservice = 'crear' OR subservice = 'votar')"
						]
					],
					[
						'caption' => 'Comprar un ticket para la [Rifa]',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM ticket WHERE email = '{$request->email}' AND origin = 'PURCHASE'"
						]
					],
					[
						'caption' => 'Revisar los articulos de la [Tienda]',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM utilization WHERE requestor = '{$request->email}' AND service = 'tienda'"
						]
					]
				],
				'completion' => function(Request $request)
				{
					$r = $this->q("SELECT count(*) as total FROM _retos WHERE email = '{$request->email}';");
					if(intval($r[0]->total) == 0)
					{
						// increase credit
						$this->q("UPDATE person SET credit = credit + 2 WHERE email = '{$request->email}';");

						// track the event
						$this->q("INSERT INTO _retos (email, goal, prize) VALUES ('{$request->email}', '2000-01-01 00:00:00', 2);");

						// send notification
						$text = 'Usted completo los retos iniciales y gano &sect;2.00, ahora le ofreceremos retos cada semana';
						$this->utils->addNotification($request->email, 'RETOS', $text);
					}
				},
				'completion_text' => false
			],
			'weekly' => [
				'title' => 'Retos semanales',
				'prize' => 1,
				'checker' => function($request)
				{
					$r = $this->q("SELECT count(*) as total FROM _retos WHERE email = '{$request->email}'  AND week(now()) = week(goal) AND year(goal) = year(now())");

					return intval($r[0]->total) > 0;

				},
				'goals' => [
					[
						'caption' => 'Usar la [app] los siete d&iacute;as de la semana (X/7)',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM delivery_received WHERE webhook = 'app' AND user = '{$request->email}' AND week(now()) = week(inserted) AND year(inserted) = year(now())",
							'cmp' => function($value)
							{
								return $value == 7;
							}
						]
					],
					[
						'caption' => 'Escribir o votar por una [Sugerencia]',
						'link' => "SUGERENCIAS",
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM utilization WHERE requestor = '{$request->email}' AND service = 'sugerencias' AND (subservice = 'crear' OR subservice = 'votar') AND week(now()) = week(request_time) AND year(request_time) = year(now())"
						]
					],
					[
						'caption' => 'Referir un amigo a usar la app',
						'link' => 'REFERIR @amigo',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM _referir WHERE user = '{$request->email}' AND week(now()) = week(inserted) AND year(inserted) = year(now())"
						]
					],
					[
						'caption' => 'Escribir una nota publica en la [Pizarra]',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM _pizarra_notes WHERE email = '{$request->email}' AND week(now()) = week(inserted) AND year(inserted) = year(now())"
						]
					],
					[
						'caption' => 'Enviar una nota privada a otro usuario',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM _note WHERE from_user = '{$request->email}' AND week(now()) = week(send_date) AND year(send_date) = year(now())"
						]
					],
					[
						'caption' => 'Contestar una [encuesta] y ganar creditos',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM (
										  SELECT id, questions, answers, questions - answers as unanswered
										  FROM ( SELECT id,
										           (SELECT COUNT(C.id) as total FROM _survey_question C WHERE survey = A.id) as questions,
										           (SELECT COUNT(D.answer) as answers FROM _survey_answer_choosen D WHERE survey = A.id AND email = '{$request->email}' AND week(now()) = week(date_choosen) AND year(date_choosen) = year(now())) as answers
										         FROM _survey A WHERE active = 1) B
										  WHERE questions > 0 AND questions - answers <= 0
									  ) E WHERE unanswered <= 0;"
						]
					],
					[
						'caption' => 'Comprar tickets para la [Rifa]',
						[
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM ticket WHERE email = '{$request->email}' AND origin = 'PURCHASE' AND week(now()) = week(creation_time) AND year(creation_time) = year(now())"
						]
					]
				],
				'completion' => function(Request $request)
				{
					$r = $this->q("SELECT count(*) as total FROM _retos WHERE email = '{$request->email}'  AND week(now()) = week(goal) AND year(goal) = year(now())");
					if(intval($r[0]->total) == 0)
					{
						// increase credit
						$this->q("UPDATE person SET credit = credit + 1 WHERE email = '{$request->email}';");

						// track the event
						$this->q("INSERT INTO _retos (email, goal, prize) VALUES ('{$request->email}', now(), 1);");

						// send notification
						$text = 'Usted completo los retos de la semana y gan&oacute; &sect;1.00. Vuelva con el mismo entusiasmo la semana pr&oacute;xima.';
						$this->utils->addNotification($request->email, 'RETOS', $text);
					}
				},
				'completion_text' => 'Usted ya complet&oacute; los retos de la semana. Vuelva con el mismo entusiasmo la semana pr&oacute;xima.'
			]
		];

		// checking each goal
		$last_section = false;
		foreach($this->goals as $s => $section)
		{
			$this->goals[ $s ]['total']          = count($section['goals']);
			$this->goals[ $s ]['complete_count'] = 0;
			$this->goals[ $s ]['visible']        = false;
			$all_complete                        = true;

			$verify = true;
			if(isset($section['checker']))
			{
				$call = $section['checker'];
				$r    = $call($request);
				if($r == true) $verify = false;
			}

			// prepare links
			foreach($section['goals'] as $g => $goal)
			{
				if( ! isset($goal['link']))
				{
					$c = $goal['caption'];
					$p = strpos($c, '[');
					if($p !== false)
					{
						$p1 = strpos($c, "]");
						if($p1 !== false) $this->goals[ $s ]['goals'][ $g ]['link'] = substr($c, $p + 1, $p1 - $p - 1);
						else
							$this->goals[ $s ]['goals'][ $g ]['link'] = false;
					}
					else
						$this->goals[ $s ]['goals'][ $g ]['link'] = false;
				}
			}

			// checkers
			if($verify) foreach($section['goals'] as $g => $goal)
			{
				$complete                                     = $this->checkGoal($goal, $request);
				$all_complete                                 = $all_complete && $complete;
				$this->goals[ $s ]['goals'][ $g ]['complete'] = $complete;
				$this->goals[ $s ]['complete_count']          += $complete ? 1 : 0;
			}

			$this->goals[ $s ]['complete'] = $all_complete;

			if($all_complete)
			{
				if(isset($section['completion']))
				{
					$call = $section['completion'];
					$call($request);
				}
			}
			else
			{
				// show first section not completed
				$this->goals[ $s ]['visible'] = true;
				break;
			}
			$last_section = $s;
		}

		if($last_section !== false) $this->goals[ $last_section ]['visible'] = true;
	}

	/**
	 * Check goal
	 *
	 * @param array   $goal
	 * @param Request $request
	 *
	 * @return bool
	 */
	public function checkGoal($goal, $request)
	{

		$result = false;
		if(isset($goal['checker']))
		{
			if( ! isset($goal['checker']['cmp'])) $goal['checker']['cmp'] = function($value) { return $value > 0; };

			$cmp = $goal['checker']['cmp'];

			switch($goal['checker']['type'])
			{
				case "count":
					$sql    = $goal['checker']['data'];
					$r      = $this->q($sql);
					$result = $cmp($r[0]->total);
					break;
				case 'callable':
					$call   = $goal['checker']['data'];
					$result = $call(['request' => $request]);
					break;
			}
		}

		return $result;
	}


}