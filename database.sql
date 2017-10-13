-- Apretaste!
-- RETOS subservice database
-- by @kumahacker
-- 2017-10-05

-- example
-- +--------------------+---------+---------------------+-------+-------------+
-- | email              | type    | goal                | prize | status      |
-- +--------------------+---------+---------------------+-------+-------------+
-- | html@apretaste.com | initial | 2000-01-01 00:00:00 |     2 | 11111111111 |
-- | html@apretaste.com | weekly  | 2017-10-12 08:01:31 |     0 | 0001100     |
-- +--------------------+---------+---------------------+-------+-------------+

DROP TABLE IF EXISTS _retos;

CREATE TABLE _retos (

  -- email of the user
  email varchar(255) not null,

  -- type: "initial", "weekly", etc...
  `type` varchar(20) not null,

  -- goal is the date of week when user win the prize
  -- if goal is null, then goal is the initial goal
  goal timestamp default '2000-01-01 00:00:00',

  -- prize wined by the user, take value after complete the goals
  prize float default 0,

  -- status of the goal, strings of "0"s and "1"s, for goal's checker cache
  -- each goal have an index, then if goal 3 is done, then status is like as '00100000'
  -- ... select length(replace(status,'0','')) is the total of complete goals
  -- ... select length(replace(status,'1','')) is the total of incomplete goals
  -- ... select length(status) is the total of goals
  -- ... select substr(status, 1, 1) is the bit of goal #1 (in php the position start in 0)
  status varchar(20),

  -- user can win only first goal and one goal for each week
  primary key (email, goal)
);