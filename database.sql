--
-- Table structure for table `_retos`
--

CREATE TABLE `_retos` (
  `id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `type` enum('initial','weekly') DEFAULT NULL,
  `week_number` char(3) DEFAULT NULL,
  `prize` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `_retos`
--
ALTER TABLE `_retos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `person_id` (`person_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `_retos`
--
ALTER TABLE `_retos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;