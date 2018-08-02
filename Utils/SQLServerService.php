<?php


namespace garinlu\SQLServerBundle\Utils;


class SQLServerService
{
    /** @var string $serverName */
    private $serverName;
    /** @var array $connectionOptions */
    private $connectionOptions;

    public function __construct($serverName, $uid, $pwd)
    {
        $this->serverName = $serverName;
        $this->connectionOptions = [
            "Database" => "IDS",
            "UID" => $uid,
            "PWD" => $pwd,
            "Authentication" => "ActiveDirectoryPassword"
        ];
    }

    /**
     * Start a connection between your server (serverName) and your php process
     *
     * @param $serverName
     * @param $connectionOptions
     * @return false|resource
     * @throws \Exception
     */
    public function connectTo($serverName = null, $connectionOptions = null)
    {
        if (null === $serverName || null === $connectionOptions)
        {
            $serverName = $this->serverName;
            $connectionOptions = $this->connectionOptions;
        }
        $conn = sqlsrv_connect($serverName, $connectionOptions);

        if (!$conn)
        {
            throw new \Exception("The connection to the SQL server failed.");
        }

        return $conn;
    }

    /**
     * Execute a SELECT query
     *
     * @param $connection
     * @param $tableName
     * @param array $columns
     * @param array $criteria
     * @return array
     * @throws \Exception
     */
    public function get($connection, $tableName, array $columns, $criteria = [])
    {
        $SELECT = "";
        foreach ($columns as $column)
        {
            if ($SELECT !== "")
                $SELECT .= ", ";
            $SELECT .= strval($column);
        }

        $WHERE = "";
        foreach ($criteria as $criterion)
        {
            if ($WHERE !== "")
                $WHERE .= " AND ";
            $WHERE .= strval($criterion);
        }

        $queryString = "SELECT $SELECT FROM $tableName";
        if ("" !== $WHERE)
            $queryString .= " WHERE $WHERE";

        $resultSet = sqlsrv_query($connection, $queryString, array());
        if (!$resultSet)
        {
            dump(sqlsrv_errors());
            throw new \Exception("Your query is not correct.");
        }
        $results = [];
        while ($row = sqlsrv_fetch_array($resultSet))
        {
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Show tables
     *
     * @param $connection
     * @param null $criteria
     * @return array|null
     */
    public function showTables($connection, $criteria = null)
    {
        $queryString = "SELECT TABLE_NAME
FROM INFORMATION_SCHEMA.TABLES
WHERE ";

        $WHERE = "TABLE_TYPE = 'BASE TABLE' AND TABLE_CATALOG='IDS'";
        if (null !== $criteria && 'null' !== $criteria)
        {
            foreach ($criteria as $column => $value)
            {
                $WHERE .= " AND $column = '$value'";
            }
        }
        $queryString .= $WHERE;


        $resultSet = sqlsrv_query($connection, $queryString, array());
        if (!$resultSet)
        {
            dump(sqlsrv_errors());
            return null;
        }
        $results = [];
        while ($row = sqlsrv_fetch_array($resultSet))
        {
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Try to CREATE a table
     *
     * @param $connection
     * @param $tableName
     * @param $fields
     * @param $foreignKeys
     */
    public function createTable($connection, $tableName, $fields, $foreignKeys)
    {
        $queryString = "CREATE TABLE [dbo].[$tableName] ";

        $COLUMNS = "";
        if (empty($fields))
            return;
        foreach ($fields as $columns)
        {
            foreach ($columns as $column => $type)
            {
                if ("" !== $COLUMNS)
                    $COLUMNS .= ", ";

                $COLUMNS .= "$column $type";
            }
        }

        $KEYS = "";
        if (!empty($foreignKeys))
        {
            foreach ($foreignKeys as $foreignKey)
            {
                $KEYS .= ", ";
                $KEYS .= "$foreignKey";
            }
        }
        $queryString .= "($COLUMNS $KEYS);";
        print_r("Query : ");
        print_r($queryString);
        $resultSet = sqlsrv_query($connection, $queryString, array());
        if (!$resultSet)
        {
            print_r("ERROR!");
            print_r(sqlsrv_errors());
            return;
        }
        print_r("Return : ");
        print_r(sqlsrv_execute($resultSet));
        return;
    }

    /**
     * Try to DROP a table
     *
     * @param $connection
     * @param $tableName
     */
    public function dropTable($connection, $tableName)
    {
        $queryString = "DROP TABLE [dbo].[$tableName] ";

        print_r("Query : ");
        print_r($queryString);

        $resultSet = sqlsrv_query($connection, $queryString, array());
        if (!$resultSet)
        {
            print_r("\n\nERROR!\n");
            print_r(sqlsrv_errors() . "\n");
            return;
        }

        print_r("Return : ");
        print_r(sqlsrv_execute($resultSet));
        return;
    }

    /**
     * Execute a custom query. Be careful on what you are going to do with it.
     *
     * @param $connection
     * @param $queryString
     * @return array
     * @throws \Exception
     */
    public function execute($connection, $queryString)
    {
        $resultSet = sqlsrv_query($connection, $queryString, array());
        if (!$resultSet)
        {
            throw new \Exception("Your query is not correct.");
        }
        $results = [];
        while ($row = sqlsrv_fetch_array($resultSet))
        {
            $results[] = $row;
        }

        return $results;
    }
}