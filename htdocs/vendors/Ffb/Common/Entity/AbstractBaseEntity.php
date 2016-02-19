<?php

namespace Ffb\Common\Entity;

// Mapping types
// string: Type that maps an SQL VARCHAR to a PHP string.
// integer: Type that maps an SQL INT to a PHP integer.
// smallint: Type that maps a database SMALLINT to a PHP integer.
// bigint: Type that maps a database BIGINT to a PHP string.
// boolean: Type that maps an SQL boolean to a PHP boolean.
// decimal: Type that maps an SQL DECIMAL to a PHP double.
// date: Type that maps an SQL DATETIME to a PHP DateTime object.
// time: Type that maps an SQL TIME to a PHP DateTime object.
// datetime: Type that maps an SQL DATETIME/TIMESTAMP to a PHP DateTime
// object.
// text: Type that maps an SQL CLOB to a PHP string.
// object: Type that maps a SQL CLOB to a PHP object using serialize() and
// unserialize().
// array: Type that maps a SQL CLOB to a PHP object using serialize() and
// unserialize().
// float: Type that maps a SQL Float (Double Precision) to a PHP double.
// IMPORTANT: Works only with locale settings that use decimal points as
// separator.

// The Column annotation has some more attributes. Here is a complete list:
// type: (optional, defaults to string) The mapping type to use for the
// column.
// name: (optional, defaults to field name) The name of the column in the
// database.
// length: (optional, default 255) The length of the column in the database.
// String only.
// unique: (optional, default FALSE) Whether the column is a unique key.
// nullable: (optional, default FALSE) Whether the database column is
// nullable.
// precision: (optional, default 0) The precision for a decimal (exact
// numeric) column. Decimal only.
// scale: (optional, default 0) The scale for a decimal (exact numeric)
// column. (Applies only if a decimal column is used.)

// Here is the list of possible generation strategies:
// AUTO (default): Tells Doctrine to pick the strategy that is preferred by
// the used database platform. The preferred strategies are IDENTITY for
// MySQL, SQLite and MsSQL and SEQUENCE for Oracle and PostgreSQL. This
// strategy provides full portability.
// SEQUENCE: Tells Doctrine to use a database sequence for ID generation.
// This strategy does currently not provide full portability. Sequences are
// supported by Oracle and PostgreSql.
// IDENTITY: Tells Doctrine to use special identity columns in the database
// that generate a value on insertion of a row. This strategy does currently
// not provide full portability and is supported by the following platforms:
// MySQL/SQLite (AUTO_INCREMENT), MSSQL (IDENTITY) and PostgreSQL (SERIAL).
// TABLE: Tells Doctrine to use a separate table for ID generation. This
// strategy provides full portability. *This strategy is not yet
// implemented!*
// NONE: Tells Doctrine that the identifiers are assigned (and thus
// generated) by your code. The assignment must take place before a new
// entity is passed to EntityManager#persist. NONE is the same as leaving
// off the @GeneratedValue entirely.

/**
 * AbstractBaseEntity
 *
 * This is the base class for all entities of this application.
 *
 * @see http://docs.doctrine-project.org/en/latest/reference/annotations-reference.html
 */
abstract class AbstractBaseEntity {

    /**
     * Exchange black list
     *
     * @var array
     */
    protected $_exchangeBL = array();

    /**
     *
     * @return array
     */
    public function getArrayCopy() {
        return get_object_vars($this);
    }

    /**
     * Copy data from the passed in array to entities properties.
     *
     * @param array $data
     */
    public function exchangeArray(array $data) {
        foreach ($data as $key => $value) {
            // skip blacklisted properties
            if (in_array($key, $this->_exchangeBL)) {
                continue;
            }
            // check if setter exists
            $setter = 'set' . implode('', array_map(function ($v) {
                return ucfirst($v);
            }, explode('_', $key)));
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
    }

    /**
     * Return entity data as array.
     *
     * @return array $data
     */
    public function asArray() {

        $data = array();
        foreach (array_keys(get_object_vars($this)) as $variable) {

            $getter = 'get' . implode('', array_map(function ($v) {
                return ucfirst($v);
            }, explode('_', $variable)));

            $variable = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $variable));

            if (method_exists($this, $getter)) {
                $value = $this->$getter();
                $data[$variable] = $value;
            }
        }

        return $data;
    }
}
