Prosty w obsłudze orm zakładający dziedziczenie z klasy \SO\Model.
Aby zainicjalizować obiekt bazy należy przed pierwszym wywołaniem modelu 
zainicjalizować obiekt bazy danych np.:
    Db::setConnection(self::HOST, self::LOGIN, self::PASSWORD, self::DATABASE);

Potem wystarczy zekstendować się z \SO\Model w celu stworzenie własnego modelu np.:

    class SampleModel extends SO\Model{

        protected function getPrimaryKey() {
            return 'id';
        }

        protected function getTableName() {
            return 'test_table';
        }

        /**
         * @param \stdClass|null $attributes
         * @return SampleModel
         */
        public static function model(\stdClass $attributes = null) {
            return parent::model($attributes);
        }


    }

nadpisanie metody model z odpowiednim komentarzem pozwala nie gubić się netbeansowi
z tym na jakim modelu operuje