'database'     => WRITEPATH . 'base.db',
'DBDriver'     => 'SQLite3',
php spark make:migration CreateTables
php spark migrate