<?php

class Service
{

    private $initialPrize = 2;

    private $weeklyPrize = 1;

    private $initialCompletedStatus = "1111111111";

    private $weeklyCompletedStatus = "111111";

    private $initialEmptyStatus = "0000000000";

    private $weeklyEmptyStatus = "000000";

    private $status;

    private $request;

    /**
     * Main response
     *
     * @param Request  $request
     * @param Response $response
     *
     * @author salvipascual
     */
    public function _main(Request $request, Response &$response)
    {
        // add request to the clase to be used for helper functions
        $this->request = $request;

        //
        // INITIAL GOALS SECTION
        //

        // get the completion of the initial goals
        $res = Connection::query("SELECT prize, `status` FROM _retos WHERE person_id={$request->person->id} AND `type`='initial'");
        $this->status = isset($res[0]) ? $res[0]->status : false;
        $prize = isset($res[0]) ? $res[0]->prize : 0;

        // create row if do not exist
        if (!$this->status) {
            $this->status = $this->initialEmptyStatus;
            Connection::query("INSERT INTO _retos (person_id,`type`,`status`) VALUES ({$request->person->id},'initial','{$this->status}')");
        }

        // check status and complte goals
        $goals = [];
        $goals[] = $this->goalTerminos(0, 'initial');
        $goals[] = $this->goalProfile(1, 'initial');
        $goals[] = $this->goalOrigin(2, 'initial');
        $goals[] = $this->goalHowToGetCredits(3, 'initial');
        $goals[] = $this->goalOpenSupport(4, 'initial');
        //$goals[] = $this->goalWhoReferedMe(5, 'initial');
        $goals[] = $this->goalSurveys(6, 'initial');
        //		$goals[] = $this->goalContests(7, 'initial');
        $goals[] = $this->goalPizarra(8, 'initial');
        $goals[] = $this->goalRaffle(9, 'initial');

        // if goals were completed today
        if ($this->status == $this->initialCompletedStatus || $this->status = '1111101011') {
            // if prize is given, passes to weekly
            // if prize was not granted yet ...
            if ($prize == 0) {
                // grant credits and mark prize as paid
                Connection::query("
					UPDATE person SET credit = credit + {$this->initialPrize} WHERE id={$request->person->id};
					UPDATE _retos SET prize=1 WHERE person_id={$request->person->id} AND `type`='initial';");

                // tell user know the prize was granted
                return $response->setTemplate('completed.ejs', ["credit" => $this->initialPrize, "showBtn" => true]);
            }
            // if goals were not completed
        } else {
            return $response->setTemplate('challenges.ejs', ['goals' => $goals, "credit" => $this->initialPrize]);
        }

        //
        // WEEKLY GOALS SECTION
        //

        // get the completion of the weekly goals
        $res = Connection::query("SELECT prize, `status` FROM _retos WHERE person_id={$request->person->id} AND `type`='weekly' AND week_number=WEEK(CURRENT_TIMESTAMP)");
        $this->status = isset($res[0]) ? $res[0]->status : false;
        $prize = isset($res[0]) ? $res[0]->prize : 0;

        // create row if do not exist
        if (!$this->status) {
            $this->status = $this->weeklyEmptyStatus;
            Connection::query("INSERT INTO _retos (person_id,`type`,`status`,week_number) VALUES ({$request->person->id},'weekly','{$this->status}',WEEK(CURRENT_TIMESTAMP))");
        }

        // check status and complete goals
        $goals = [];
        $goals[] = $this->goalAppUsage(0, 'weekly');
        //$goals[] = $this->goalGiveFeedback(1, 'weekly');
        $goals[] = $this->goalPostPizarra(2, 'weekly');
        //		$goals[] = $this->goalChat(3, 'weekly');
        $goals[] = $this->goalReferFriend(4, 'weekly');
        $goals[] = $this->goalBuyRaffleTickets(5, 'weekly');

        // if goals were completed today
        if ($this->status == $this->weeklyCompletedStatus || $this->status == '111011' || $this->status == '101011') {
            if ($prize == 0) {
                // grant credits and mark prize as paid
                Connection::query("
					UPDATE person SET credit = credit + {$this->weeklyPrize} WHERE id={$request->person->id};
					UPDATE _retos SET prize=1 WHERE person_id={$request->person->id} AND `type`='weekly' AND week_number=WEEK(CURRENT_TIMESTAMP);
				");
            }

            // tell user goals were completed
            return $response->setTemplate('completed.ejs', ["credit" => $this->weeklyPrize, "showBtn" => false]);
            // if goals were not completed
        } else {
            return $response->setTemplate('challenges.ejs', ['goals' => $goals, "credit" => $this->weeklyPrize]);
        }
    }

    /**
     * goal CHECK TERMINOS
     *
     * @param Int    $pos  , position in this->status bit
     * @param String $type [initial, weekly]
     *                     *@author salvipascual
     */
    private function goalTerminos($pos, $type)
    {
        // if the goal is not ready, check completion
        if (!$this->status[$pos]) {
            $count = Connection::query("SELECT COUNT(id) AS cnt FROM delivery WHERE id_person='{$this->request->person->id}' AND request_service='terminos'");
            if ($count[0]->cnt >= 1) {
                $this->markGoalAsDone($pos, $type);
            }
        }

        return ["caption" => "Leer los Terminos del servicio", "completed" => $this->status[$pos], "link" => "TERMINOS"];
    }

    /**
     * goal FILL PERFIL
     *
     * @param Int    $pos  , position in this->status bit
     * @param String $type [initial, weekly]
     *                     *@author salvipascual
     */
    private function goalProfile($pos, $type)
    {
        // if the goal is not ready, check completion
        if (!$this->status[$pos]) {
            $count = Connection::query("SELECT COUNT(id) as cnt FROM person WHERE id='{$this->request->person->id}' AND updated_by_user = 1");
            if ($count[0]->cnt >= 1) {
                $this->markGoalAsDone($pos, $type);
            }
        }

        return ["caption" => "Completar su perfil de usuario y poner foto", "completed" => $this->status[$pos], "link" => "PERFIL EDITAR"];
    }

    /**
     * goal SET ORIGIN
     *
     * @param Int    $pos  , position in this->status bit
     * @param String $type [initial, weekly]
     *                     *@author salvipascual
     */
    private function goalOrigin($pos, $type)
    {
        // if the goal is not ready, check completion
        if (!$this->status[$pos]) {
            $count = Connection::query("SELECT COUNT(id) as cnt FROM person WHERE id='{$this->request->person->id}' AND origin IS NOT NULL");
            if ($count[0]->cnt >= 1) {
                $this->markGoalAsDone($pos, $type);
            }
        }

        return ["caption" => "Dinos donde escuchó sobre la app", "completed" => $this->status[$pos], "link" => "PERFIL ORIGEN"];
    }

    /**
     * goal HOW TO GET CREDITS
     *
     * @param Int    $pos  , position in this->status bit
     * @param String $type [initial, weekly]
     *                     *@author salvipascual
     */
    private function goalHowToGetCredits($pos, $type)
    {
        // if the goal is not ready, check completion
        if (!$this->status[$pos]) {
            $count = Connection::query("SELECT COUNT(id) AS cnt FROM delivery WHERE id_person={$this->request->person->id} AND request_service='credito'");
            if ($count[0]->cnt >= 1) {
                $this->markGoalAsDone($pos, $type);
            }
        }

        return ["caption" => "Leer las maneras de ganar créditos", "completed" => $this->status[$pos], "link" => "CREDITO OBTENER"];
    }

    /**
     * goal OPEN SUPPORT
     *
     * @param Int    $pos  , position in this->status bit
     * @param String $type [initial, weekly]
     *                     *@author salvipascual
     */
    private function goalOpenSupport($pos, $type)
    {
        // if the goal is not ready, check completion
        if (!$this->status[$pos]) {
            $count = Connection::query("SELECT COUNT(id) as cnt FROM delivery WHERE id_person={$this->request->person->id} AND request_service = 'soporte'");
            if ($count[0]->cnt >= 1) {
                $this->markGoalAsDone($pos, $type);
            }
        }

        return ["caption" => "Abrir el Soporte por primera vez", "completed" => $this->status[$pos], "link" => "SOPORTE"];
    }

    /**
     * goal ADD WHO REFERRED YOU
     *
     * @param Int    $pos  , position in this->status bit
     * @param String $type [initial, weekly]
     *                     *@author salvipascual
     */

    private function goalWhoReferedMe($pos, $type)
    {
        // TODO: este reto ya no tiene sentido pues el servicio REFERIR ahora lo que hace es INVITAR
        // if the goal is not ready, check completion
        if (!$this->status[$pos]) {
            $count = Connection::query("SELECT count(id) as cnt FROM _email_invitations WHERE user='{$this->request->person->email}'");
            if ($count[0]->cnt >= 1) {
                $this->markGoalAsDone($pos, $type);
            }
        }

        return ["caption" => "Referir a alguien", "completed" => $this->status[$pos], "link" => "REFERIR"];
    }

    /**
     * goal REFER FRIEND
     *
     * @param Int    $pos  , position in this->status bit
     * @param String $type [initial, weekly]
     *
     * @return array
     */
    private function goalReferFriend($pos, $type)
    {
        // if the goal is not ready, check completion
        if (!$this->status[$pos]) {
            /*$count = Connection::query("
                SELECT COUNT(id) AS cnt
                FROM _referir
                WHERE father = '{$this->request->person->email}'
                AND WEEK(inserted) = WEEK(CURRENT_TIMESTAMP)
                AND YEAR(inserted) = YEAR(CURRENT_TIMESTAMP)");*/

            $count = Connection::query("
				SELECT COUNT(id) AS cnt 
				FROM _email_invitations 
				WHERE id_from = '{$this->request->person->id}' 
				AND WEEK(send_date) = WEEK(CURRENT_TIMESTAMP) 
				AND YEAR(send_date) = YEAR(CURRENT_TIMESTAMP)
				-- AND accepted = 1");


            if ($count[0]->cnt >= 1) {
                $this->markGoalAsDone($pos, $type);
            }
        }

        return ["caption" => "Invita y Gana", "completed" => $this->status[$pos], "link" => "REFERIR"];
    }

    /**
     * goal CHECK SURVEYS
     *
     * @param Int    $pos  , position in this->status bit
     * @param String $type [initial, weekly]
     *                     *@author salvipascual
     */
    private function goalSurveys($pos, $type)
    {
        // if the goal is not ready, check completion
        if (!$this->status[$pos]) {
            $count = Connection::query("SELECT COUNT(id) AS cnt FROM delivery WHERE id_person={$this->request->person->id} AND request_service='encuesta'");
            if ($count[0]->cnt >= 1) {
                $this->markGoalAsDone($pos, $type);
            }
        }

        return ["caption" => "Ver las Encuestas activas y listas para ganar créditos", "completed" => $this->status[$pos], "link" => "ENCUESTA"];
    }

    /**
     * goal CHECK CONTESTS
     *
     * @param Int    $pos  , position in this->status bit
     * @param String $type [initial, weekly]
     *                     *@author salvipascual
     */
    private function goalContests($pos, $type)
    {
        // if the goal is not ready, check completion
        if (!$this->status[$pos]) {
            $count = Connection::query("SELECT COUNT(id) AS cnt FROM delivery WHERE id_person={$this->request->person->id} AND request_service='concurso'");
            if ($count[0]->cnt >= 1) {
                $this->markGoalAsDone($pos, $type);
            }
        }

        return ["caption" => "Ver la lista de Concursos abiertos", "completed" => $this->status[$pos], "link" => "CONCURSOS"];
    }

    /**
     * goal CHECK PIZARRA
     *
     * @param Int    $pos  , position in this->status bit
     * @param String $type [initial, weekly]
     *                     *@author salvipascual
     */
    private function goalPizarra($pos, $type)
    {
        // if the goal is not ready, check completion
        if (!$this->status[$pos]) {
            $count = Connection::query("SELECT COUNT(id) AS cnt FROM delivery WHERE id_person={$this->request->person->id} AND request_service='pizarra'");
            if ($count[0]->cnt >= 1) {
                $this->markGoalAsDone($pos, $type);
            }
        }

        return ["caption" => "Revisar las notas en la Pizarra", "completed" => $this->status[$pos], "link" => "PIZARRA"];
    }

    /**
     * goal CHECK THE RAFFLE
     *
     * @param Int    $pos  , position in this->status bit
     * @param String $type [initial, weekly]
     *                     *@author salvipascual
     */
    private function goalRaffle($pos, $type)
    {
        // if the goal is not ready, check completion
        if (!$this->status[$pos]) {
            $count = Connection::query("SELECT COUNT(id) AS cnt FROM delivery WHERE id_person={$this->request->person->id} AND request_service='rifa'");
            if ($count[0]->cnt >= 1) {
                $this->markGoalAsDone($pos, $type);
            }
        }

        return ["caption" => "Revisar la Rifa y aprender como adquirir tickets ", "completed" => $this->status[$pos], "link" => "RIFA"];
    }

    /**
     * goal USE APP
     *
     * @param Int    $pos  , position in this->status bit
     * @param String $type [initial, weekly]
     *                     *@author salvipascual
     */
    private function goalAppUsage($pos, $type)
    {
        // if the goal is not ready, check completion
        $daysCount = false;
        if (!$this->status[$pos]) {
            // get the number of days used
            $days = Connection::query("
				SELECT COUNT(id) AS nbr
				FROM delivery 
				WHERE id_person = {$this->request->person->id}
				AND WEEK(request_date) = WEEK(CURRENT_TIMESTAMP)
				AND YEAR(request_date) = YEAR(CURRENT_TIMESTAMP)
				GROUP BY DATE(request_date)");
            $daysCount = count($days);
            if ($daysCount >= 7) {
                $this->markGoalAsDone($pos, $type);
            }
        }

        $weekDays = $daysCount ? "($daysCount/7)" : "";

        return ["caption" => "Usar la app los siete días de la semana $weekDays", "completed" => $this->status[$pos], "link" => ""];
    }

    /**
     * goal GIVE FEEDBACK
     *
     * @param Int    $pos  , position in this->status bit
     * @param String $type [initial, weekly]
     *                     *@author salvipascual
     */
    private function goalGiveFeedback($pos, $type)
    {
        // if the goal is not ready, check completion
        if (!$this->status[$pos]) {
            $count = Connection::query("
				SELECT COUNT(id) AS cnt 
				FROM delivery 
				WHERE id_person={$this->request->person->id} 
				AND request_service = 'sugerencias' 
				AND (request_subservice = 'crear' OR request_subservice = 'votar')");
            if ($count[0]->cnt >= 1) {
                $this->markGoalAsDone($pos, $type);
            }
        }

        return ["caption" => "Escribir o votar por una Sugerencia", "completed" => $this->status[$pos], "link" => "SUGERENCIAS"];
    }

    /**
     * goal POST PIZARRA
     *
     * @param Int    $pos  , position in this->status bit
     * @param String $type [initial, weekly]
     *                     *@author salvipascual
     */
    private function goalPostPizarra($pos, $type)
    {
        // if the goal is not ready, check completion
        if (!$this->status[$pos]) {
            $count = Connection::query("
				SELECT COUNT(id) AS cnt 
				FROM _pizarra_notes 
				WHERE id_person = '{$this->request->person->id}' 
				AND WEEK(inserted) = WEEK(CURRENT_TIMESTAMP)
				AND YEAR(inserted) = YEAR(CURRENT_TIMESTAMP)");
            if ($count[0]->cnt >= 1) {
                $this->markGoalAsDone($pos, $type);
            }
        }

        return ["caption" => "Escribir una nota en la Pizarra", "completed" => $this->status[$pos], "link" => "PIZARRA"];
    }

    /**
     * goal CHAT
     *
     * @param Int    $pos  , position in this->status bit
     * @param String $type [initial, weekly]
     *                     *@author salvipascual
     */
    private function goalChat($pos, $type)
    {
        // if the goal is not ready, check completion
        if (!$this->status[$pos]) {
            $count = Connection::query("
				SELECT COUNT(id) AS cnt 
				FROM _note 
				WHERE from_user = '{$this->request->person->email}' 
				AND WEEK(send_date) = WEEK(CURRENT_TIMESTAMP)
				AND YEAR(send_date) = YEAR(CURRENT_TIMESTAMP)");
            if ($count[0]->cnt >= 1) {
                $this->markGoalAsDone($pos, $type);
            }
        }

        return ["caption" => "Chatear con otro usuario", "completed" => $this->status[$pos], "link" => "CHAT ONLINE"];
    }

    /**
     * goal PLAY RAFFLE
     *
     * @param Int    $pos  , position in this->status bit
     * @param String $type [initial, weekly]
     *                     *@author salvipascual
     */
    private function goalBuyRaffleTickets($pos, $type)
    {
        // if the goal is not ready, check completion
        if (!$this->status[$pos]) {
            $count = Connection::query("
				SELECT COUNT(ticket_id) AS cnt 
				FROM ticket 
				WHERE email = '{$this->request->person->email}' 
				AND origin = 'PURCHASE' 
				AND WEEK(creation_time) = WEEK(CURRENT_TIMESTAMP)
				AND YEAR(creation_time) = YEAR(CURRENT_TIMESTAMP)");
            if ($count[0]->cnt >= 1) {
                $this->markGoalAsDone($pos, $type);
            }
        }

        return ["caption" => "Comprar tickets para la Rifa", "completed" => $this->status[$pos], "link" => "RIFA"];
    }

    /**
     * Update a goal in the database
     *
     * @param Int    $pos  , position in this->status bit
     * @param String $type [initial, weekly]
     *                     *@author salvipascual
     */
    private function markGoalAsDone($pos, $type)
    {
        // change bit position on the goal
        $this->status[$pos] = '1';

        // change for initial goals
        if ($type == 'initial') {
            Connection::query("
				UPDATE _retos SET `status`='{$this->status}' 
				WHERE person_id={$this->request->person->id} 
				AND `type`='initial'");
        }

        // change for weekly goals
        if ($type == 'weekly') {
            Connection::query("
				UPDATE _retos SET `status`='{$this->status}' 
				WHERE person_id={$this->request->person->id} 
				AND `type`='weekly' 
				AND week_number=WEEK(CURRENT_TIMESTAMP)");
        }
    }
}
