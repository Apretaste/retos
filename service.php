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
	public $now = null; // using "unique now" during the execution of the sevice
	public $big_ban = '2000-01-01 00:00:00';
	private $connection = null;

	public function __construct()
	{
		$this->now = date("Y-m-d h:i:s");
		//parent::__construct();
	}

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
	 * $this->q("SELECT * FROM TABLE"); // (more readable / SQL is autodescriptive)
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
				'filter_unique' => "email = '{$request->email}' AND goal = '{$this->big_ban}'",
				'get_last' => function($request)
				{
					$bad_status = str_repeat('0', count($this->goals['initial']['goals']));
					$r          = $this->q("SELECT *, length(replace(status, '0', '')) AS completed FROM _retos WHERE {$this->goals['initial']['filter_unique']}");
					if(isset($r[0])) return $r[0];

					$this->q("INSERT INTO _retos (email, `type`, goal, prize, status) VALUES ('{$request->email}', 'initial', '{$this->big_ban}', 0, '$bad_status');");

					$r            = new stdClass();
					$r->email     = $request->email;
					$r->completed = 0;
					$r->goal      = $this->big_ban;
					$r->prize     = 0;
					$r->status    = $bad_status;

					return $r;
				},
				'checker' => function($request)
				{
					return $this->goals['initial']['get_last']($request)->completed == count($this->goals['initial']['goals']);
				},
				'update_status' => function()
				{
					$this->q("UPDATE _retos SET status = '{$this->goals['initial']['status']}' WHERE {$this->goals['initial']['filter_unique']}");
				},
				'goals' => [
					0 => [
						'caption' => 'Leer los [Terminos] del servicio',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM delivery WHERE `user` = '{$request->email}' AND request_service = 'terminos'"
						]
					],
					1 => [
						'caption' => 'Completar su perfil de usuario y poner foto',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM person WHERE email = '{$request->email}' AND updated_by_user = 1"
							/*
							'data' => function($data)
							{
								return $this->utils->getProfileCompletion($data['request']->email) == 100;
							}*/
						]
					],
					2 => [
						'caption' => 'Leer las maneras de ganar [credito]',
						'link' => "WEB credito.apretaste.com",
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM delivery WHERE `user` = '{$request->email}' AND request_service = 'web' AND locate('credito.apretaste.com', lower(request_query)) > 0"
						]
					],
					3 => [
						'caption' => 'Abrir el [Soporte] por primera vez',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM delivery WHERE `user` = '{$request->email}' AND request_service = 'soporte'"
						]
					],
					4 => [
						'caption' => '[Referir] a un amigo y ganar creditos',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM _referir WHERE user = '{$request->email}'"
						]
					],
					5 => [
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
					6 => [
						'caption' => 'Ver la lista de [Concursos] abiertos',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM delivery WHERE `user` = '{$request->email}' AND request_service = 'concurso'"
						]
					],
					7 => [
						'caption' => 'Revisar las notas en la [Pizarra]',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM delivery WHERE `user` = '{$request->email}' AND request_service = 'pizarra'"
						]
					],
					8 => [
						'caption' => 'Escribir o votar por una [Sugerencia]',
						'link' => "SUGERENCIAS",
						'checker' => [
							'type' => 'count', // "sugerencias" contiene "sugerencia" y este es alias del servicio
							'data' => "SELECT count(*) as total FROM delivery WHERE `user` = '{$request->email}' AND request_service = 'sugerencias' AND request_subservice = 'crear' OR request_subservice = 'votar'"
						]
					],
					9 => [
						'caption' => 'Comprar un ticket para la [Rifa]',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM ticket WHERE email = '{$request->email}' AND origin = 'PURCHASE'"
						]
					],
					10 => [
						'caption' => 'Revisar los articulos de la [Tienda]',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM delivery WHERE `user` = '{$request->email}' AND request_service = 'tienda'"
						]
					]
				],
				'completion' => function(Request $request)
				{
					$prize = $this->goals['initial']['prize'];

					if($this->goals['initial']['checker']($request) == false)
					{
						// increase credit
						$this->q("UPDATE person SET credit = credit + 2 WHERE email = '{$request->email}';");

						// update prize
						$this->q("UPDATE _retos SET prize = $prize WHERE {$this->goals['initial']['filter_unique']}");

						// send notification
						$text = 'Usted complet&oacute; los retos iniciales y gano &sect;2.00, ahora le ofreceremos retos cada semana';
						$this->utils->addNotification($request->email, 'RETOS', $text);
					}
				},
				'completion_text' => false
			],
			'weekly' => [
				'title' => 'Retos semanales',
				'prize' => 1,
				'filter_unique' => "email = '{$request->email}' AND week('{$this->now}') = week(goal) AND year(goal) = year('{$this->now}')",
				'get_last' => function($request)
				{
					$bad_status = str_repeat('0', count($this->goals['weekly']['goals']));

					$r = $this->q("SELECT *, length(replace(status, '0', '')) AS completed FROM _retos WHERE {$this->goals['weekly']['filter_unique']}");
					if(isset($r[0])) return $r[0];

					$this->q("INSERT INTO _retos (email, `type`, goal, prize, status) VALUES ('{$request->email}', 'weekly', '{$this->now}', 0, '$bad_status');");

					$r            = new stdClass();
					$r->email     = $request->email;
					$r->completed = 0;
					$r->goal      = $this->now;
					$r->prize     = 0;
					$r->status    = $bad_status;

					return $r;
				},
				'checker' => function($request)
				{
					return $this->goals['weekly']['get_last']($request)->completed == count($this->goals['weekly']['goals']);
				},
				'update_status' => function($request)
				{
					$this->q("UPDATE _retos SET status = '{$this->goals['initial']['status']}' WHERE {$this->goals['weekly']['filter_unique']}");
				},
				'goals' => [
					0 => [
						'caption' => 'Usar la app los siete d&iacute;as de la semana ({count}/7)',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(fecha) as total FROM (
										 SELECT
										 count(*) AS total,
										 date_format(request_date, '%Y-%m-%d') AS fecha
										 FROM delivery
										 WHERE environment = 'app' AND user = '{$request->email}' AND week('{$this->now}') = week(request_date) AND
										 year(request_date) = year('{$this->now}')
										 GROUP BY date_format(request_date, '%Y-%m-%d')
										) subq",
							'cmp' => function($value)
							{
								return $value == 7;
							}
						]
					],
					1 => [
						'caption' => 'Escribir o votar por una [Sugerencia]',
						'link' => "SUGERENCIAS",
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM delivery WHERE `user` = '{$request->email}' AND request_service = 'sugerencias' AND (request_subservice = 'crear' OR request_subservice = 'votar') AND week('{$this->now}') = week(request_date) AND year(request_date) = year('{$this->now}')"
						]
					],
					2 => [
						'caption' => 'Referir un amigo a usar la app',
						'link' => 'REFERIR',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM _referir WHERE father = '{$request->email}' AND week('{$this->now}') = week(inserted) AND year(inserted) = year('{$this->now}')"
						]
					],
					3 => [
						'caption' => 'Escribir una nota publica en la [Pizarra]',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM _pizarra_notes WHERE email = '{$request->email}' AND week('{$this->now}') = week(inserted) AND year(inserted) = year('{$this->now}')"
						]
					],
					4 => [
						'caption' => 'Chatear con otro usuario',
						'link' => 'CHAT',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM _note WHERE from_user = '{$request->email}' AND week('{$this->now}') = week(send_date) AND year(send_date) = year('{$this->now}')"
						]
					],
					5 => [
						'caption' => 'Contestar una [encuesta] y ganar creditos',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM (
										 SELECT id, questions, answers, questions - answers as unanswered
										 FROM ( SELECT id,
										 (SELECT COUNT(C.id) as total FROM _survey_question C WHERE survey = A.id) as questions,
										 (SELECT COUNT(D.answer) as answers FROM _survey_answer_choosen D WHERE survey = A.id AND email = '{$request->email}' AND week('{$this->now}') = week(date_choosen) AND year(date_choosen) = year('{$this->now}')) as answers
										 FROM _survey A WHERE active = 1) B
										 WHERE questions > 0 AND questions - answers <= 0
									 ) E WHERE unanswered <= 0;"
						]
					],
					6 => [
						'caption' => 'Comprar tickets para la [Rifa]',
						'checker' => [
							'type' => 'count',
							'data' => "SELECT count(*) as total FROM ticket WHERE email = '{$request->email}' AND origin = 'PURCHASE' AND week('{$this->now}') = week(creation_time) AND year(creation_time) = year('{$this->now}')"
						]
					]
				],
				'completion' => function(Request $request)
				{
					$prize = $this->goals['weekly']['prize'];

					if($this->goals['weekly']['checker']($request) == false)
					{
						// increase credit
						$this->q("UPDATE person SET credit = credit + $prize WHERE email = '{$request->email}';");

						// update prize
						$this->q("UPDATE _retos SET prize = $prize WHERE {$this->goals['weekly']['filter_unique']}");

						// send notification
						$text = 'Usted complet&oacute; los retos de la semana y gan&oacute; &sect;' . number_format($prize, 2) . '. Vuelva con el mismo entusiasmo la semana pr&oacute;xima.';
						$this->utils->addNotification($request->email, 'RETOS', $text);
					}
				},
				'completion_text' => 'Usted ya complet&oacute; los retos de la semana. Vuelva con el mismo entusiasmo la semana pr&oacute;xima.'
			]
		];

		// checking each goal
		$last_section = false;
		$no_more      = false;
		foreach($this->goals as $s => $section)
		{
			$this->goals[ $s ]['total']          = count($section['goals']);
			$this->goals[ $s ]['complete_count'] = 0;
			$this->goals[ $s ]['visible']        = false;
			$this->goals[ $s ]['status']         = $this->goals[ $s ]['get_last']($request)->status;
			$all_complete                        = true;

			if($no_more) continue;

			$verify = true;
			if(isset($section['checker']))
			{
				$r = $section['checker']($request);
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

				$this->goals[ $s ]['goals'][ $g ]['caption'] = str_replace([
					'[',
					']'
				], '', $this->goals[ $s ]['goals'][ $g ]['caption']);
			}

			// checkers
			if($verify) foreach($section['goals'] as $g => $goal)
			{
				$complete = true;
				if($this->goals[ $s ]['status'][ $g ] == '0') // check only not completed goals
				{
					$count                                       = 0;
					$complete                                    = $this->checkGoal($goal, $request, $count);
					$this->goals[ $s ]['goals'][ $g ]['caption'] = str_replace('{count}', "$count", $this->goals[ $s ]['goals'][ $g ]['caption']);
					$this->goals[ $s ]['status'][ $g ]           = $complete ? 1 : 0;
				}

				$all_complete                                 = $all_complete && $complete;
				$this->goals[ $s ]['goals'][ $g ]['complete'] = $complete;
				$this->goals[ $s ]['complete_count']          += $complete ? 1 : 0;
			}

			$this->goals[ $s ]['complete'] = $all_complete;

			// save status
			$this->q("UPDATE _retos SET status = '{$this->goals[$s]['status']}' WHERE {$this->goals[$s]['filter_unique']}");

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
				$no_more                      = true;
				continue;
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
	 * @param integer $count
	 *
	 * @return bool
	 */
	public function checkGoal($goal, $request, &$count = 0)
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
					$count  = $r[0]->total;
					$result = $cmp($count);
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
