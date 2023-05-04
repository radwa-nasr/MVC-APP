<?php

namespace PROJECT\MODELS;

use PROJECT\LIB\DATABASE\DatabaseHandler;

class AbstractModel
{
  const DATA_TYPE_BOOL = \PDO::PARAM_BOOL;
  const DATA_TYPE_STR = \PDO::PARAM_STR;
  const DATA_TYPE_INT = \PDO::PARAM_INT;
  const DATA_TYPE_DECIMAL = 4;
  const DATA_TYPE_DATE = 5;

  // VALID DATE RANGE IS 1000-01-01 TO 9999-12-31
  const VALIDATE_DATE_STRING = '/^[1-9][1-9][1-9][1-9]-[0-1]?[0-2]-(?:[0-2]?[1-9]|[3][0-1])$/';

  // CHECK THE VALID DATES IN MYSQL TO CREATE PROPER PATTERN
  const VALIDATE_DATE_NUMERIC = '^\d{6,8}$';
  const DEFAULT_MYSQL_DATE = '1970-01-01';

  private static $db;

  private function prepareValues(\PDOStatement &$stmt)
  {
    foreach (static::$tableScheme as $columnName => $type) {
      if ($type == 4) {
        $sanitizedValue = filter_var($this->$columnName, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $stmt->bindValue(":{$columnName}", $sanitizedValue);
      } else {
        $stmt->bindValue(":{$columnName}", $this->$columnName, $type);
      }
    }
  }

  private function buildNamePatternsSQL()
  {
    $namedParams = '';
    foreach (static::$tableScheme as $columnName => $type) {
      $namedParams .= $columnName . ' = :' . $columnName . ', ';
    }
    return trim($namedParams, ', ');
  }

  private function create()
  {
    $sql = 'INSERT INTO ' . static::$tableName . ' SET ' . $this->buildNamePatternsSQL();
    $stmt = DatabaseHandler::factory()->prepare($sql);
    $this->prepareValues($stmt);
    return $stmt->execute();
  }

  private function update()
  {
    $sql = 'UPDATE ' . static::$tableName . ' SET ' . $this->buildNamePatternsSQL() .
      ' WHERE ' . static::$primaryKey . ' = ' . $this->{static::$primaryKey};
    $stmt = DatabaseHandler::factory()->prepare($sql);
    $this->prepareValues($stmt);
    return $stmt->execute();
  }

  private function save()
  {
    return $this->{static::$primaryKey} === null ? $this->create() : $this->update();
  }

  private function delete()
  {
    $sql = 'DELETE FROM ' . static::$tableName . ' WHERE ' .
      static::$primaryKey . ' = ' . $this->{static::$primaryKey};
    $stmt = DatabaseHandler::factory()->prepare($sql);
    return $stmt->execute();
  }

  public static function getAll()
  {
    $sql = 'SELECT * FROM ' . static::$tableName;
    $stmt = DatabaseHandler::factory()->prepare($sql);
    $stmt->execute();
    if (method_exists(get_called_class(), '__construct')) {
      $results = $stmt->fetchAll(
        \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
        get_called_class(),
        array_keys(static::$tableName)
      );
    } else {
      $results = $stmt->fetchAll(\PDO::FETCH_CLASS, get_called_class());
    }
    if (is_array($results) && !empty($results)) {
      $generator = function () use ($results) {
        foreach ($results as $result) {
          yield $result;
        }
      };
      return $generator;
    }
    return false;
  }

  public static function getByPK($pk)
  {
    $sql = 'SELECT FROM ' . static::$tableName . ' WHERE ' . static::$primaryKey . ' = "' . $pk . '"';
    $stmt = DatabaseHandler::factory()->prepare($sql);
    if ($stmt->execute() === true) {
      $obj = $stmt->fetchAll(
        \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
        get_called_class(),
        array_keys(static::$tableScheme)
      );
      return array_shift($obj);
    }
    return false;
  }

  public static function get($sql, $options = array())
  {
    $stmt = DatabaseHandler::factory()->prepare($sql);
    if (!empty($options)) {
      foreach ($options as $columnName => $type) {
        if ($type[0] == 4) {
          $sanitizedValue = filter_var($type[1], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
          $stmt->bindValue(":{$columnName}", $sanitizedValue);
        } elseif ($type[0] == 5) {
          if (!preg_match(self::VALIDATE_DATE_STRING, $type[1]) || !preg_match(self::VALIDATE_DATE_NUMERIC, $type[1])) {
            $stmt->bindValue(":{$columnName}", $type[1]);
          }
        } else {
          $stmt->bindValue(":{$columnName}", $type[1], $type[0]);
        }
      }
    }
    $stmt->execute();
    if (method_exists(get_called_class(), '__construct')) {
      $results = $stmt->fetchAll(
        \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
        get_called_class(),
        array_keys(static::$tableScheme)
      );
    } else {
      $results = $stmt->fetchAll(\PDO::FETCH_CLASS, get_called_class());
    }
    if (is_array($results) && !empty($results)) {
      $generator = function () use ($results) {
        foreach ($results as $result) {
          yield $result;
        }
      };
      return $generator;
    }
    return false;
  }
}
