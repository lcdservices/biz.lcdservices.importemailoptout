CREATE TABLE `civicrm_ia_emailoptout_log` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `status` varchar(32) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `civicrm_ia_emailoptout_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `status` (`status`);

ALTER TABLE `civicrm_ia_emailoptout_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
