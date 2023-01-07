<?php

namespace App\DataFixtures;

class Person
{
    private static array $lastName = [
        'Смирнов', 'Иванов', 'Кузнецов', 'Соколов', 'Попов', 'Лебедев', 'Козлов',
        'Новиков', 'Морозов', 'Петров', 'Волков', 'Соловьёв', 'Васильев', 'Зайцев',
        'Павлов', 'Семёнов', 'Голубев', 'Виноградов', 'Богданов', 'Воробьёв',
        'Фёдоров', 'Михайлов', 'Беляев', 'Тарасов', 'Белов', 'Комаров', 'Орлов',
        'Киселёв', 'Макаров', 'Андреев', 'Ковалёв', 'Ильин', 'Гусев', 'Титов',
        'Кузьмин', 'Кудрявцев', 'Баранов', 'Куликов', 'Алексеев', 'Степанов',
        'Яковлев', 'Сорокин', 'Сергеев', 'Романов', 'Захаров', 'Борисов', 'Королёв',
        'Герасимов', 'Пономарёв', 'Григорьев', 'Лазарев', 'Медведев', 'Ершов',
        'Никитин', 'Соболев', 'Рябов', 'Поляков', 'Цветков', 'Данилов', 'Жуков',
        'Фролов', 'Журавлёв', 'Николаев', 'Крылов', 'Максимов', 'Сидоров', 'Осипов',
        'Белоусов', 'Федотов', 'Дорофеев', 'Егоров', 'Матвеев', 'Бобров', 'Дмитриев',
        'Калинин', 'Анисимов', 'Петухов', 'Антонов', 'Тимофеев', 'Никифоров',
        'Веселов', 'Филиппов', 'Марков', 'Большаков', 'Суханов', 'Миронов', 'Ширяев',
        'Александров', 'Коновалов', 'Шестаков', 'Казаков', 'Ефимов', 'Денисов',
        'Громов', 'Фомин', 'Давыдов', 'Мельников', 'Щербаков', 'Блинов', 'Колесников',
        'Карпов', 'Афанасьев', 'Власов', 'Маслов', 'Исаков', 'Тихонов', 'Аксёнов',
        'Гаврилов', 'Родионов', 'Котов', 'Горбунов', 'Кудряшов', 'Быков', 'Зуев',
        'Третьяков', 'Савельев', 'Панов', 'Рыбаков', 'Суворов', 'Абрамов', 'Воронов',
        'Мухин', 'Архипов', 'Трофимов', 'Мартынов', 'Емельянов', 'Горшков', 'Чернов',
        'Овчинников', 'Селезнёв', 'Панфилов', 'Копылов', 'Михеев', 'Галкин', 'Назаров',
        'Лобанов', 'Лукин', 'Беляков', 'Потапов', 'Некрасов', 'Хохлов', 'Жданов',
        'Наумов', 'Шилов', 'Воронцов', 'Ермаков', 'Дроздов', 'Игнатьев', 'Савин',
        'Логинов', 'Сафонов', 'Капустин', 'Кириллов', 'Моисеев', 'Елисеев', 'Кошелев',
        'Костин', 'Горбачёв', 'Орехов', 'Ефремов', 'Исаев', 'Евдокимов', 'Калашников',
        'Кабанов', 'Носков', 'Юдин', 'Кулагин', 'Лапин', 'Прохоров', 'Нестеров',
        'Харитонов', 'Агафонов', 'Муравьёв', 'Ларионов', 'Федосеев', 'Зимин', 'Пахомов',
        'Шубин', 'Игнатов', 'Филатов', 'Крюков', 'Рогов', 'Кулаков', 'Терентьев',
        'Молчанов', 'Владимиров', 'Артемьев', 'Гурьев', 'Зиновьев', 'Гришин', 'Кононов',
        'Дементьев', 'Ситников', 'Симонов', 'Мишин', 'Фадеев', 'Комиссаров', 'Мамонтов',
        'Носов', 'Гуляев', 'Шаров', 'Устинов', 'Вишняков', 'Евсеев', 'Лаврентьев',
        'Брагин', 'Константинов', 'Корнилов', 'Авдеев', 'Зыков', 'Бирюков', 'Шарапов',
        'Никонов', 'Щукин', 'Дьячков', 'Одинцов', 'Сазонов', 'Якушев', 'Красильников',
        'Гордеев', 'Самойлов', 'Князев', 'Беспалов', 'Уваров', 'Шашков', 'Бобылёв',
        'Доронин', 'Белозёров', 'Рожков', 'Самсонов', 'Мясников', 'Лихачёв', 'Буров',
        'Сысоев', 'Фомичёв', 'Русаков', 'Стрелков', 'Гущин', 'Тетерин', 'Колобов',
        'Субботин', 'Фокин', 'Блохин', 'Селиверстов', 'Пестов', 'Кондратьев', 'Силин',
        'Меркушев', 'Лыткин', 'Туров',
    ];

    private static array $lastNameSuffix = ['а', ''];

    private static array $firstNameMale = [
        'Абрам', 'Август', 'Адам', 'Адриан', 'Аким', 'Александр', 'Алексей', 'Альберт', 'Ананий', 'Анатолий', 'Андрей', 'Антон', 'Антонин',
        'Аполлон', 'Аркадий', 'Арсений', 'Артемий', 'Артур', 'Артём', 'Афанасий', 'Богдан', 'Болеслав', 'Борис', 'Бронислав', 'Вадим',
        'Валентин', 'Валериан', 'Валерий', 'Василий', 'Вениамин', 'Викентий', 'Виктор', 'Виль', 'Виталий', 'Витольд', 'Влад', 'Владимир',
        'Владислав', 'Владлен', 'Всеволод', 'Вячеслав', 'Гавриил', 'Гарри', 'Геннадий', 'Георгий', 'Герасим', 'Герман', 'Глеб', 'Гордей',
        'Григорий', 'Давид', 'Дан', 'Даниил', 'Данила', 'Денис', 'Дмитрий', 'Добрыня', 'Донат', 'Евгений', 'Егор', 'Ефим',
        'Захар', 'Иван', 'Игнат', 'Игнатий', 'Игорь', 'Илларион', 'Илья', 'Иммануил', 'Иннокентий', 'Иосиф', 'Ираклий', 'Кирилл',
        'Клим', 'Константин', 'Кузьма', 'Лаврентий', 'Лев', 'Леонид', 'Макар', 'Максим', 'Марат', 'Марк', 'Матвей', 'Милан',
        'Мирослав', 'Михаил', 'Назар', 'Нестор', 'Никита', 'Никодим', 'Николай', 'Олег', 'Павел', 'Платон', 'Прохор', 'Пётр',
        'Радислав', 'Рафаил', 'Роберт', 'Родион', 'Роман', 'Ростислав', 'Руслан', 'Сава', 'Савва', 'Святослав', 'Семён', 'Сергей',
        'Спартак', 'Станислав', 'Степан', 'Стефан', 'Тарас', 'Тимофей', 'Тимур', 'Тит', 'Трофим', 'Феликс', 'Филипп', 'Фёдор',
        'Эдуард', 'Эрик', 'Юлиан', 'Юлий', 'Юрий', 'Яков', 'Ян', 'Ярослав', 'Милан',
    ];

    private static array $firstNameFemale = [
        'Александра', 'Алина', 'Алиса', 'Алла', 'Альбина', 'Алёна', 'Анастасия', 'Анжелика', 'Анна', 'Антонина', 'Анфиса', 'Валентина', 'Валерия',
        'Варвара', 'Василиса', 'Вера', 'Вероника', 'Виктория', 'Владлена', 'Галина', 'Дарья', 'Диана', 'Дина', 'Доминика', 'Ева',
        'Евгения', 'Екатерина', 'Елена', 'Елизавета', 'Жанна', 'Зинаида', 'Злата', 'Зоя', 'Изабелла', 'Изольда', 'Инга', 'Инесса',
        'Инна', 'Ирина', 'Искра', 'Капитолина', 'Клавдия', 'Клара', 'Клементина', 'Кристина', 'Ксения', 'Лада', 'Лариса', 'Лидия',
        'Лилия', 'Любовь', 'Людмила', 'Люся', 'Майя', 'Мальвина', 'Маргарита', 'Марина', 'Мария', 'Марта', 'Надежда', 'Наталья',
        'Нелли', 'Ника', 'Нина', 'Нонна', 'Оксана', 'Олеся', 'Ольга', 'Полина', 'Рада', 'Раиса', 'Регина', 'Рената',
        'Розалина', 'Светлана', 'Софья', 'София', 'Таисия', 'Тамара', 'Татьяна', 'Ульяна', 'Фаина', 'Федосья', 'Флорентина', 'Эльвира', 'Эмилия',
        'Эмма', 'Юлия', 'Яна', 'Ярослава',
    ];

    private static array $middleNameMale = [
        'Александрович', 'Алексеевич', 'Андреевич', 'Дмитриевич', 'Евгеньевич',
        'Сергеевич', 'Иванович', 'Фёдорович', 'Львович', 'Романович', 'Владимирович',
        'Борисович', 'Максимович',
    ];

    private static array $middleNameFemale = [
        'Александровна', 'Алексеевна', 'Андреевна', 'Дмитриевна', 'Евгеньевна',
        'Сергеевна', 'Ивановна', 'Фёдоровна', 'Львовна', 'Романовна', 'Владимировна',
        'Борисовна', 'Максимовна',
    ];

    public function getPersonInformation(): array
    {
        $lastName = static::randomElement(static::$lastName);
        $firstName = static::randomElement(static::$firstNameMale);
        $middleName = static::randomElement(static::$middleNameMale);
        $gender = mt_rand(1, 2);

        if (1 === $gender) {
            $lastName .= 'а';
            $firstName = static::randomElement(static::$firstNameFemale);
            $middleName = static::randomElement(static::$middleNameFemale);
        }

        return [
            'fullName' => $lastName.' '.$firstName.' '.$middleName,
            'gender' => $gender,
        ];
    }

    public static function randomElement($array = ['a', 'b', 'c'])
    {
        if (!$array || ($array instanceof \Traversable && !count($array))) {
            return null;
        }
        $elements = static::randomElements($array, 1);

        return $elements[0];
    }

    public static function randomElements($array = ['a', 'b', 'c'], $count = 1, $allowDuplicates = false): array
    {
        $traversables = [];

        if ($array instanceof \Traversable) {
            foreach ($array as $element) {
                $traversables[] = $element;
            }
        }

        $arr = count($traversables) ? $traversables : $array;

        $allKeys = array_keys($arr);
        $numKeys = count($allKeys);

        if (!$allowDuplicates && $numKeys < $count) {
            throw new \LengthException(sprintf('Cannot get %d elements, only %d in array', $count, $numKeys));
        }

        $highKey = $numKeys - 1;
        $keys = $elements = [];
        $numElements = 0;

        while ($numElements < $count) {
            $num = mt_rand(0, $highKey);

            if (!$allowDuplicates) {
                if (isset($keys[$num])) {
                    continue;
                }
                $keys[$num] = true;
            }

            $elements[] = $arr[$allKeys[$num]];
            ++$numElements;
        }

        return $elements;
    }
}
