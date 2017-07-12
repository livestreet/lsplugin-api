--
-- Структура таблицы `prefix_api_session`
--

CREATE TABLE `prefix_api_session` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_create` datetime NOT NULL,
  `hash` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `prefix_api_session`
--
ALTER TABLE `prefix_api_session`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `hash` (`hash`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `prefix_api_session`
--
ALTER TABLE `prefix_api_session`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;