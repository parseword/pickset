<?php

namespace parseword\pickset;

/**
 * DatabaseConnection is a singleton wrapper for a PDO database connection. All
 * public methods are static; you don't instantiate this class, but instead call
 * its methods statically.
 *
 * Prior to using the connection, you MUST first set the connection string
 * (Data Source Name, or DSN) and any necessary credentials. This only needs
 * to be done once in a given execution context, preferably at the top of your
 * application's main controller or a common include file.
 *
 * MySQL/MariaDB example, requires credentials:
 *
 * DatabaseConnection::setDsn('mysql:host=127.0.0.1;dbname=mydb;charset=latin1');
 * DatabaseConnection::setUsername('your-username');
 * DatabaseConnection::setPassword('your-strong-password');
 * // ...
 * $stmt = DatabaseConnection::prepare(...);
 * $stmt->bindParam(...);
 * $results = $stmt->execute();
 *
 * SQLite example, doesn't require credentials:
 *
 * DatabaseConnection::setDsn('sqlite:/var/www/secure/db/my-database.sqlite3');
 * // ...
 * $stmt = DatabaseConnection::prepare(...);
 * $stmt->bindParam(...);
 * $results = $stmt->execute();
 *
 * *****************************************************************************
 * This file is part of pickset, a collection of PHP utilities.
 *
 * Copyright 2006, 2019 Shaun Cummiskey <shaun@shaunc.com> <https://shaunc.com/>
 *
 * Repository: <https://github.com/parseword/pickset/>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
class DatabaseConnection
{

    /**
     * A reference to the singleton instance of this class.
     *
     * @var DatabaseConnection
     */
    private static $instance = null;

    /**
     * The PDO object.
     *
     * @var \PDO
     */
    private static $connection = null;

    /**
     * The DSN connection string.
     *
     * @var string
     */
    private static $dsn = null;

    /**
     * The database server username, if required.
     *
     * @var string
     */
    private static $username = null;

    /**
     * The database server password, if required.
     *
     * @var string
     */
    private static $password = null;

    /**
     * A private constructor to satisfy the singleton pattern.
     *
     * @throws Exception\LoggedException
     */
    private function __construct() {

        /* Connect to the database */
        if (empty(self::$dsn)) {
            throw new Exception\LoggedException('No connection string has been '
                    . 'configured; you MUST call DatabaseConnection::setDsn()');
        }
        try {
            self::$connection = new \PDO(
                    self::$dsn, self::$username, self::$password
            );
        }
        catch (\PDOException $ex) {
            /* Re-throw as a LoggedException */
            throw new Exception\LoggedException($ex->getMessage(),
                    $ex->getCode(), $ex->getPrevious());
        }
    }

    /**
     * This forces the singleton instance to be created, which attempts to
     * connect to the database using whatever DSN, username, and password have
     * been configured.
     *
     * Calling this method isn't required; the instance will be created
     * automatically the first time any of ::prepare(), ::query(), or ::exec()
     * are called. However, calling DatabaseConnection::init() somewhere in your
     * common include file is an easy way to catch and handle authentication
     * problems early on.
     *
     * @return void
     */
    public static function init(): void {
        self::getInstance();
    }

    /**
     * Return the singleton instance of this class, creating it first if needed.
     *
     * @return DatabaseConnection The class instance
     */
    protected static function getInstance(): DatabaseConnection {
        if (empty(self::$instance)) {
            self::$instance = new DatabaseConnection();
        }
        return self::$instance;
    }

    /**
     * Return the PDO object.
     *
     * This is not truly deprecated: that label is intended to flag that maybe
     * you're doing something which could be accomplished more easily. If you
     * intend to call exec(), prepare(), or query() on the PDO object, you can
     * use the convenience methods in this class.
     *
     * @deprecated
     * @return \PDO The PDO object
     */
    public static function getConnection(): \PDO {
        return self::getInstance()::$connection;
    }

    /**
     * Set the connection string (Data Source Name) for the PDO connection
     *
     * @param string $dsn The connection string
     * @return void
     */
    public static function setDsn(string $dsn): void {
        self::$dsn = $dsn;
    }

    /**
     * Set the username to use when connecting to the database.
     *
     * @param string $username The username for the PDO connection
     * @return void
     */
    public static function setUsername(string $username): void {
        self::$username = $username;
    }

    /**
     * Set the password to use when connecting to the database.
     *
     * @param string $password The password for the PDO connection
     * @return void
     */
    public static function setPassword(string $password): void {
        self::$password = $password;
    }

    /**
     * Convenience method to access the PDO object's prepare() without
     * having to explicitly call getConnection(). InspectDNS uses prepared
     * statements for all of its database operations, and so should you.
     *
     * @param string $statement The statement to prepare
     * @return \PDOStatement The prepared statement object
     */
    public static function prepare(string $statement) {
        return self::getConnection()->prepare($statement);
    }

    /**
     * DO NOT PASS USER INPUT INTO THIS METHOD! EVER! YOU HAVE BEEN WARNED!
     *
     * Convenience method to access the PDO object's exec() without
     * having to explicitly call getConnection().
     *
     * @param string $statement The statement to execute
     * @return int The number of affected rows
     */
    public static function exec(string $statement) {
        return self::getConnection()->exec($statement);
    }

    /**
     * DO NOT PASS USER INPUT INTO THIS METHOD! EVER! YOU HAVE BEEN WARNED!
     *
     * Convenience method to access the PDO object's query() without
     * having to explicitly call getConnection().
     *
     * @param string $statement The statement to run
     * @return \PDOStatement The result set object
     */
    public static function query(string $statement) {
        return self::getConnection()->query($statement);
    }

    /**
     * Convenience method to return the error code associated with the last
     * operation without having to explicitly call getConnection().
     *
     * @return string|null The error code, if any, e.g. "HY093"
     */
    public static function errorCode(): ?string {
        return self::getConnection()->errorCode();
    }

    /**
     * Convenience method to return the error info associated with the last
     * operation without having to explicitly call getConnection().
     *
     * @return array The error info, if any.
     */
    public static function errorInfo(): array {
        return self::getConnection()->errorInfo();
    }

    /**
     * Convenience method to return the ID associated with the last inserted
     * row, if the database engine supports this feature.
     *
     * @return string
     */
    public static function lastInsertId(): string {
        return self::getConnection()->lastInsertId();
    }

}
