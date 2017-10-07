-- Apretaste!
-- RETOS subservice database
-- by @kumahacker
-- 2017-10-05

DROP TABLE IF EXISTS _retos;

CREATE TABLE _retos (

  -- email of the user
  email varchar(255) not null,

  -- goal is the date of week when user win the prize
  -- if goal is null, then goal is the initial goal
  goal timestamp default '2000-01-01 00:00:00',

  -- prize wined by the user
  prize float default 2,

  -- user can win only first goal and one goal for each week
  primary key (email, goal)
);