<?php

class Retos extends Service
{
	private $initialPrize = 2;
	private $weeklyPrize = 1;
	private $initialCompletedStatus = "1111111111";
	private $weeklyCompletedStatus = "111111";
	private $initialEmptyStatus = "0000000000";
	private $weeklyEmptyStatus = "000000";
	private $status;

	/**
	 * Main response
	 *
	 * @author salvipascual
	 * @param Request $request
	 * @return Response
	 */
	public function _main(Request $request)
	{
		//
		// INITIAL GOALS SECTION
		//

		// get the completion of the initial goals
		$res = Connection::query("SELECT prize, `status` FROM _retos WHERE person_id={$request->userId} AND `type`='initial'");
		$this->status = isset($res[0]) ? $res[0]->status : false;
		$prize = isset($res[0]) ? $res[0]->prize : 0;

		// create row if do not exist
		if( ! $this->status) {
			$this->status = $this->initialEmptyStatus;
			Connection::query("INSERT INTO _retos (person_id,`type`,`status`) VALUES ({$request->userId},'initial','{$this->status}')");
		}

		// check status and complte goals
		$goals = [];
		$goals[] = $this->goalTerminos(0, 'initial');
		$goals[] = $this->goalProfile(1, 'initial');
		$goals[] = $this->goalOrigin(2, 'initial');
		$goals[] = $this->goalHowToGetCredits(3, 'initial');
		$goals[] = $this->goalOpenSupport(4, 'initial');
		$goals[] = $this->goalWhoReferedMe(5, 'initial');
		$goals[] = $this->goalSurveys(6, 'initial');
		$goals[] = $this->goalContests(7, 'initial');
		$goals[] = $this->goalPizarra(8, 'initial');
		$goals[] = $this->goalRaffle(9, 'initial');

		// if goals were completed today
		if($this->status == $this->initialCompletedStatus) {
			// if prize is given, passes to weekly
			// if prize was not granted yet ...
			if($prize == 0) {
				// grant credits and mark prize as paid
				Connection::query("
					UPDATE person SET credit = credit + {$this->initialPrize} WHERE id={$request->userId};
					UPDATE _retos SET prize=1 WHERE person_id={$request->userId} AND `type`='initial';
				");
	
				// tell user know the prize was granted
				$response = new Response();
				$response->setResponseSubject("Tus retos");
				$response->createFromTemplate('completed.tpl', ["credit"=>$this->initialPrize, "showBtn"=>true]);
				return $response;
			}
		// if goals were not completed
		} else {
			// send information to the view
			$response = new Response();
			$response->setResponseSubject("Tus retos");
			$response->createFromTemplate('challenges.tpl', ['goals'=>$goals, "credit"=>$this->initialPrize]);
			return $response;
		}

		//
		// WEEKLY GOALS SECTION
		//

		// get the completion of the weekly goals
		$res = Connection::query("SELECT prize, `status` FROM _retos WHERE person_id={$request->userId} AND `type`='weekly' AND week_number=WEEK(CURRENT_TIMESTAMP)");
		$this->status = isset($res[0]) ? $res[0]->status : false;
		$prize = isset($res[0]) ? $res[0]->prize : 0;

		// create row if do not exist
		if( ! $this->status) {
			$this->status = $this->weeklyEmptyStatus;
			Connection::query("INSERT INTO _retos (person_id,`type`,`status`,week_number) VALUES ({$request->userId},'weekly','{$this->status}',WEEK(CURRENT_TIMESTAMP))");
		}

		// check status and complete goals
		$goals = [];
		$goals[] = $this->goalAppUsage(0, 'weekly');
		$goals[] = $this->goalGiveFeedback(1, 'weekly');
		$goals[] = $this->goalPostPizarra(2, 'weekly');
		$goals[] = $this->goalChat(3, 'weekly');
		$goals[] = $this->goalReferFriend(4, 'weekly');
		$goals[] = $this->goalBuyRaffleTickets(5, 'weekly');

		// if goals were completed today
		if($this->status == $this->weeklyCompletedStatus) {
			if($prize == 0) {
				// grant credits and mark prize as paid
				Connection::query("
					UPDATE person SET credit = credit + {$this->weeklyPrize} WHERE id={$request->userId};
					UPDATE _retos SET prize=1 WHERE person_id={$request->userId} AND `type`='weekly' AND week_number=WEEK(CURRENT_TIMESTAMP);
				");
			}

			// tell user goals were completed
			$response = new Response();
			$response->setResponseSubject("Tus retos");
			$response->createFromTemplate('completed.tpl', ["credit"=>$this->weeklyPrize, "showBtn"=>false]);
			return $response;
		// if goals were not completed
		} else {
			// send information to the view
			$response = new Response();
			$response->setResponseSubject("Tus retos");
			$response->createFromTemplate('challenges.tpl', ['goals'=>$goals, "credit"=>$this->weeklyPrize]);
			return $response;
		}
	}

	/**
	 * goal CHECK TERMINOS
	 * 
	 * @author salvipascual
	 * @param Int $pos, position in this->status bit
	 * @param String $type [initial, weekly]
	 * */
	private function goalTerminos($pos, $type)
	{
		// if the goal is not ready, check completion
		if( ! $this->status[$pos]) {
			$count = Connection::query("SELECT COUNT(id) AS cnt FROM delivery WHERE id_person='{$this->request->userId}' AND request_service='terminos'");
			if($count[0]->cnt >= 1) $this->markGoalAsDone($pos, $type);
		}

		return ["caption"=>"Leer los Terminos del servicio", "completed"=>$this->status[$pos], "link"=>"TERMINOS"];
	}

	/**
	 * goal FILL PERFIL
	 * 
	 * @author salvipascual
	 * @param Int $pos, position in this->status bit
	 * @param String $type [initial, weekly]
	 * */
	private function goalProfile($pos, $type)
	{
		// if the goal is not ready, check completion
		if( ! $this->status[$pos]) {
			$count = Connection::query("SELECT COUNT(id) as cnt FROM person WHERE id='{$this->request->userId}' AND updated_by_user = 1");
			if($count[0]->cnt >= 1) $this->markGoalAsDone($pos, $type);
		}

		return ["caption"=>"Completar su perfil de usuario y poner foto", "completed"=>$this->status[$pos], "link"=>"PERFIL EDITAR"];
	}

	/**
	 * goal SET ORIGIN
	 * 
	 * @author salvipascual
	 * @param Int $pos, position in this->status bit
	 * @param String $type [initial, weekly]
	 * */
	private function goalOrigin($pos, $type)
	{
		// if the goal is not ready, check completion
		if( ! $this->status[$pos]) {
			$count = Connection::query("SELECT COUNT(id) as cnt FROM person WHERE id='{$this->request->userId}' AND origin IS NOT NULL");
			if($count[0]->cnt >= 1) $this->markGoalAsDone($pos, $type);
		}

		return ["caption"=>"Dinos donde escuch&oacute; sobre la app", "completed"=>$this->status[$pos], "link"=>"PERFIL ORIGEN"];
	}

	/**
	 * goal HOW TO GET CREDITS
	 * 
	 * @author salvipascual
	 * @param Int $pos, position in this->status bit
	 * @param String $type [initial, weekly]
	 * */
	private function goalHowToGetCredits($pos, $type)
	{
		// if the goal is not ready, check completion
		if( ! $this->status[$pos]) {
			$count = Connection::query("SELECT COUNT(id) AS cnt FROM delivery WHERE id_person={$this->request->userId} AND request_service='credito'");
			if($count[0]->cnt >= 1) $this->markGoalAsDone($pos, $type);
		}

		return ["caption"=>"Leer las maneras de ganar cr&eacute;ditos", "completed"=>$this->status[$pos], "link"=>"CREDITO"];
	}

	/**
	 * goal OPEN SUPPORT
	 * 
	 * @author salvipascual
	 * @param Int $pos, position in this->status bit
	 * @param String $type [initial, weekly]
	 * */
	private function goalOpenSupport($pos, $type)
	{
		// if the goal is not ready, check completion
		if( ! $this->status[$pos]) {
			$count = Connection::query("SELECT COUNT(id) as cnt FROM delivery WHERE id_person={$this->request->userId} AND request_service = 'soporte'");
			if($count[0]->cnt >= 1) $this->markGoalAsDone($pos, $type);
		}

		return ["caption"=>"Abrir el Soporte por primera vez", "completed"=>$this->status[$pos], "link"=>"SOPORTE"];
	}

	/**
	 * goal ADD WHO REFERRED YOU
	 * 
	 * @author salvipascual
	 * @param Int $pos, position in this->status bit
	 * @param String $type [initial, weekly]
	 * */
	private function goalWhoReferedMe($pos, $type)
	{
		// if the goal is not ready, check completion
		if( ! $this->status[$pos]) {
			$count = Connection::query("SELECT count(id) as cnt FROM _referir WHERE user='{$this->request->email}'");
			if($count[0]->cnt >= 1) $this->markGoalAsDone($pos, $type);
		}

		return ["caption"=>"Referir a un amigo y ganar creditos", "completed"=>$this->status[$pos], "link"=>"REFERIR"];
	}

	/**
	 * goal REFER FRIEND
	 * 
	 * @author salvipascual
	 * @param Int $pos, position in this->status bit
	 * @param String $type [initial, weekly]
	 * */
	private function goalReferFriend($pos, $type)
	{
		// if the goal is not ready, check completion
		if( ! $this->status[$pos]) {
			$count = Connection::query("
				SELECT COUNT(id) AS cnt 
				FROM _referir 
				WHERE father = '{$this->request->email}' 
				AND WEEK(inserted) = WEEK(CURRENT_TIMESTAMP) 
				AND YEAR(inserted) = YEAR(CURRENT_TIMESTAMP)");
			if($count[0]->cnt >= 1) $this->markGoalAsDone($pos, $type);
		}

		return ["caption"=>"Referir a un amigo y ganar creditos", "completed"=>$this->status[$pos], "link"=>"REFERIR"];
	}

	/**
	 * goal CHECK SURVEYS
	 * 
	 * @author salvipascual
	 * @param Int $pos, position in this->status bit
	 * @param String $type [initial, weekly]
	 * */
	private function goalSurveys($pos, $type)
	{
		// if the goal is not ready, check completion
		if( ! $this->status[$pos]) {
			$count = Connection::query("SELECT COUNT(id) AS cnt FROM delivery WHERE id_person={$this->request->userId} AND request_service='encuesta'");
			if($count[0]->cnt >= 1) $this->markGoalAsDone($pos, $type);
		}

		return ["caption"=>"Ver las Encuestas activas y listas para ganar creditos", "completed"=>$this->status[$pos], "link"=>"ENCUESTA"];
	}

	/**
	 * goal CHECK CONTESTS
	 * 
	 * @author salvipascual
	 * @param Int $pos, position in this->status bit
	 * @param String $type [initial, weekly]
	 * */
	private function goalContests($pos, $type)
	{
		// if the goal is not ready, check completion
		if( ! $this->status[$pos]) {
			$count = Connection::query("SELECT COUNT(id) AS cnt FROM delivery WHERE id_person={$this->request->userId} AND request_service='concurso'");
			if($count[0]->cnt >= 1) $this->markGoalAsDone($pos, $type);
		}

		return ["caption"=>"Ver la lista de Concursos abiertos", "completed"=>$this->status[$pos], "link"=>"CONCURSOS"];
	}

	/**
	 * goal CHECK PIZARRA
	 * 
	 * @author salvipascual
	 * @param Int $pos, position in this->status bit
	 * @param String $type [initial, weekly]
	 * */
	private function goalPizarra($pos, $type)
	{
		// if the goal is not ready, check completion
		if( ! $this->status[$pos]) {
			$count = Connection::query("SELECT COUNT(id) AS cnt FROM delivery WHERE id_person={$this->request->userId} AND request_service='pizarra'");
			if($count[0]->cnt >= 1) $this->markGoalAsDone($pos, $type);
		}

		return ["caption"=>"Revisar las notas en la Pizarra", "completed"=>$this->status[$pos], "link"=>"PIZARRA"];
	}

	/**
	 * goal CHECK THE RAFFLE
	 * 
	 * @author salvipascual
	 * @param Int $pos, position in this->status bit
	 * @param String $type [initial, weekly]
	 * */
	private function goalRaffle($pos, $type)
	{
		// if the goal is not ready, check completion
		if( ! $this->status[$pos]) {
			$count = Connection::query("SELECT COUNT(id) AS cnt FROM delivery WHERE id_person={$this->request->userId} AND request_service='rifa'");
			if($count[0]->cnt >= 1) $this->markGoalAsDone($pos, $type);
		}

		return ["caption"=>"Revisar la Rifa y aprender como adquirir tickets ", "completed"=>$this->status[$pos], "link"=>"RIFA"];
	}

	/**
	 * goal USE APP
	 * 
	 * @author salvipascual
	 * @param Int $pos, position in this->status bit
	 * @param String $type [initial, weekly]
	 * */
	private function goalAppUsage($pos, $type)
	{
		// if the goal is not ready, check completion
		$daysCount = false;
		if( ! $this->status[$pos]) {
			// get the number of days used
			$days = Connection::query("
				SELECT COUNT(id) AS nbr
				FROM delivery 
				WHERE id_person = {$this->request->userId}
				AND WEEK(request_date) = WEEK(CURRENT_TIMESTAMP)
				AND YEAR(request_date) = YEAR(CURRENT_TIMESTAMP)
				GROUP BY DATE(request_date)");
			$daysCount = count($days);
			if($daysCount >= 7) $this->markGoalAsDone($pos, $type);
		}

		$weekDays = $daysCount ? "($daysCount/7)" : "";
		return ["caption"=>"Usar la app los siete d&iacute;as de la semana $weekDays", "completed"=>$this->status[$pos], "link"=>""];
	}

	/**
	 * goal GIVE FEEDBACK
	 * 
	 * @author salvipascual
	 * @param Int $pos, position in this->status bit
	 * @param String $type [initial, weekly]
	 * */
	private function goalGiveFeedback($pos, $type)
	{
		// if the goal is not ready, check completion
		if( ! $this->status[$pos]) {
			$count = Connection::query("
				SELECT COUNT(id) AS cnt 
				FROM delivery 
				WHERE id_person={$this->request->userId} 
				AND request_service = 'sugerencias' 
				AND (request_subservice = 'crear' OR request_subservice = 'votar')");
			if($count[0]->cnt >= 1) $this->markGoalAsDone($pos, $type);
		}

		return ["caption"=>"Escribir o votar por una Sugerencia", "completed"=>$this->status[$pos], "link"=>"SUGERENCIAS"];
	}

	/**
	 * goal POST PIZARRA
	 * 
	 * @author salvipascual
	 * @param Int $pos, position in this->status bit
	 * @param String $type [initial, weekly]
	 * */
	private function goalPostPizarra($pos, $type)
	{
		// if the goal is not ready, check completion
		if( ! $this->status[$pos]) {
			$count = Connection::query("
				SELECT COUNT(id) AS cnt 
				FROM _pizarra_notes 
				WHERE email = '{$this->request->email}' 
				AND WEEK(inserted) = WEEK(CURRENT_TIMESTAMP)
				AND YEAR(inserted) = YEAR(CURRENT_TIMESTAMP)");
			if($count[0]->cnt >= 1) $this->markGoalAsDone($pos, $type);
		}

		return ["caption"=>"Escribir una nota en la Pizarra", "completed"=>$this->status[$pos], "link"=>"PIZARRA"];
	}

	/**
	 * goal CHAT
	 * 
	 * @author salvipascual
	 * @param Int $pos, position in this->status bit
	 * @param String $type [initial, weekly]
	 * */
	private function goalChat($pos, $type)
	{
		// if the goal is not ready, check completion
		if( ! $this->status[$pos]) {
			$count = Connection::query("
				SELECT COUNT(id) AS cnt 
				FROM _note 
				WHERE from_user = '{$this->request->email}' 
				AND WEEK(send_date) = WEEK(CURRENT_TIMESTAMP)
				AND YEAR(send_date) = YEAR(CURRENT_TIMESTAMP)");
			if($count[0]->cnt >= 1) $this->markGoalAsDone($pos, $type);
		}

		return ["caption"=>"Chatear con otro usuario", "completed"=>$this->status[$pos], "link"=>"CHAT ONLINE"];
	}

	/**
	 * goal PLAY RAFFLE
	 * 
	 * @author salvipascual
	 * @param Int $pos, position in this->status bit
	 * @param String $type [initial, weekly]
	 * */
	private function goalBuyRaffleTickets($pos, $type)
	{
		// if the goal is not ready, check completion
		if( ! $this->status[$pos]) {
			$count = Connection::query("
				SELECT COUNT(ticket_id) AS cnt 
				FROM ticket 
				WHERE email = '{$this->request->email}' 
				AND origin = 'PURCHASE' 
				AND WEEK(creation_time) = WEEK(CURRENT_TIMESTAMP)
				AND YEAR(creation_time) = YEAR(CURRENT_TIMESTAMP)");
			if($count[0]->cnt >= 1) $this->markGoalAsDone($pos, $type);
		}

		return ["caption"=>"Comprar tickets para la Rifa", "completed"=>$this->status[$pos], "link"=>"RIFA"];
	}



	/**
	 * Update a goal in the database
	 * 
	 * @author salvipascual
	 * @param Int $pos, position in this->status bit
	 * @param String $type [initial, weekly]
	 * */
	private function markGoalAsDone($pos, $type)
	{
		// change bit position on the goal
		$this->status[$pos] = '1';

		// change for initial goals
		if($type == 'initial') {
			Connection::query("
				UPDATE _retos SET `status`='{$this->status}' 
				WHERE person_id={$this->request->userId} 
				AND `type`='initial'");
		} 

		// change for weekly goals
		if($type == 'weekly') {
			Connection::query("
				UPDATE _retos SET `status`='{$this->status}' 
				WHERE person_id={$this->request->userId} 
				AND `type`='weekly' 
				AND week_number=WEEK(CURRENT_TIMESTAMP)");
		} 
	}
}
