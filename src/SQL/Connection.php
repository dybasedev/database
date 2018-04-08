<?php
/**
 * Connection.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Database\SQL;

use Closure;
use Dybasedev\Database\Connection as BaseConnection;
use Exception;
use PDO;
use PDOStatement;
use Throwable;

abstract class Connection extends BaseConnection
{
    const CONNECTION_LOST_MESSAGES = [
        'server has gone away',
        'no connection to the server',
        'Lost connection',
        'is dead or not enabled',
        'Error while sending',
        'decryption failed or bad record mac',
        'server closed the connection unexpectedly',
        'SSL connection has been closed unexpectedly',
        'Error writing data to the connection',
        'Resource deadlock avoided',
        'Transaction() on null',
        'child connection forced to terminate due to client_idle_limit',
        'query_wait_timeout',
        'reset by peer',
    ];

    const CONNECTION_CAUSED_BY_DEAD_LOCK_MESSAGES = [
        'Deadlock found when trying to get lock',
        'deadlock detected',
        'The database file is locked',
        'database is locked',
        'database table is locked',
        'A table in the database is locked',
        'has been chosen as the deadlock victim',
        'Lock wait timeout exceeded; try restarting transaction',
        'WSREP detected deadlock/conflict and aborted the transaction. Try restarting the transaction',
    ];

    /**
     * @var PDO
     */
    protected $pdoInstance;

    /**
     * @var int
     */
    protected $transactions = 0;

    /**
     * Create PDO instance
     *
     * @return PDO
     */
    abstract protected function createDriverInstance(): PDO;

    /**
     * @return PDO
     */
    public function getDriverInstance()
    {
        return $this->pdoInstance;
    }

    public function connect()
    {
        if (!$this->pdoInstance) {
            $this->pdoInstance = $this->createDriverInstance();
        }
    }

    public function reconnect()
    {
        $this->disconnect();
        $this->connect();
    }

    public function disconnect()
    {
        $this->pdoInstance = null;
    }

    /**
     * Get last insert id
     *
     * @param null $name
     *
     * @return string|int
     */
    public function lastInsertId($name = null)
    {
        return $this->getDriverInstance()->lastInsertId($name);
    }

    abstract protected function createSavePoint();

    abstract protected function rollbackSavePoint($toLevel);


    /**
     * Create a transaction within the database.
     *
     * @return void
     * @throws Exception
     */
    protected function createTransaction()
    {
        if ($this->transactions == 0) {
            try {
                $this->getDriverInstance()->beginTransaction();
            } catch (Exception $e) {
                $this->checkIfLostConnection($e, function () {
                    $this->getDriverInstance()->beginTransaction();
                });
            }
        } elseif ($this->transactions >= 1) {
            $this->createSavepoint();
        }
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param  \Closure $callback
     * @param  int      $attempts
     *
     * @return mixed
     *
     * @throws \Exception|\Throwable
     */
    public function transaction(Closure $callback, $attempts = 1)
    {
        for ($currentAttempt = 1; $currentAttempt <= $attempts; $currentAttempt++) {
            $this->beginTransaction();

            try {
                $callback($this);

                $this->commit();
            } catch (Exception $e) {
                $this->handleTransactionException(
                    $e, $currentAttempt, $attempts
                );
            } catch (Throwable $e) {
                $this->rollBack();

                throw $e;
            }
        }
    }

    /**
     * Start a new database transaction.
     *
     * @return void
     * @throws \Exception
     */
    public function beginTransaction()
    {
        $this->createTransaction();

        ++$this->transactions;
    }

    /**
     * Handle an exception encountered when running a transacted statement.
     *
     * @param  \Exception $e
     * @param  int        $currentAttempt
     * @param  int        $maxAttempts
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function handleTransactionException($e, $currentAttempt, $maxAttempts)
    {
        if ($this->causedByDeadlock($e) &&
            $this->transactions > 1) {
            --$this->transactions;

            throw $e;
        }

        $this->rollBack();

        if ($this->causedByDeadlock($e) &&
            $currentAttempt < $maxAttempts) {
            return;
        }

        throw $e;
    }

    /**
     * @param Exception $e
     * @param Closure   $callback
     *
     * @return mixed
     * @throws Exception
     */
    public function checkIfLostConnection($e, Closure $callback)
    {
        if ($this->causedByLostConnection($e)) {
            $this->reconnect();

            return ($callback)();
        }

        throw $e;
    }

    protected function causedByLostConnection(Throwable $e)
    {
        $message = $e->getMessage();

        foreach (self::CONNECTION_LOST_MESSAGES as $needle) {
            if (mb_strpos($message, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function causedByDeadlock(Throwable $e)
    {
        $message = $e->getMessage();

        foreach (self::CONNECTION_CAUSED_BY_DEAD_LOCK_MESSAGES as $needle) {
            if (mb_strpos($message, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit()
    {
        if ($this->transactions == 1) {
            $this->getDriverInstance()->commit();
        }

        $this->transactions = max(0, $this->transactions - 1);
    }

    /**
     * Rollback the active database transaction.
     *
     * @param  int|null $toLevel
     *
     * @return void
     */
    public function rollBack($toLevel = null)
    {
        $toLevel = is_null($toLevel)
            ? $this->transactions - 1
            : $toLevel;

        if ($toLevel < 0 || $toLevel >= $this->transactions) {
            return;
        }

        $this->performRollBack($toLevel);

        $this->transactions = $toLevel;
    }

    /**
     * Perform a rollback within the database.
     *
     * @param  int $toLevel
     *
     * @return void
     */
    protected function performRollBack($toLevel)
    {
        if ($toLevel == 0) {
            $this->getDriverInstance()->rollBack();
        } else {
            $this->rollbackSavePoint($toLevel + 1);
        }
    }

    /**
     * Get the number of active transactions.
     *
     * @return int
     */
    public function transactionLevel()
    {
        return $this->transactions;
    }

    /**
     * @param                $statement
     * @param array|callable $binder
     *
     * @return mixed
     * @throws Throwable
     */
    public function execute($statement, $binder = [])
    {
        return $this->process($statement, function (PDOStatement $statement) use ($binder) {
            $this->bindValues($statement, $binder);

            return $statement->execute();
        });
    }

    /**
     * @param         $statement
     * @param Closure $callback
     *
     * @return mixed
     * @throws Throwable
     */
    public function process($statement, Closure $callback)
    {
        $prepared = $this->pdoInstance->prepare($statement);

        try {
            $result = ($callback)($prepared);
        } catch (Throwable $e) {
            if ($this->transactions >= 1) {
                throw $e;
            }

            $result = $this->checkIfLostConnection($e, function () use ($prepared, $callback) {
                ($callback)($prepared);
            });
        }

        return $result;
    }

    /**
     * @param PDOStatement  $statement
     * @param array|Closure $binder
     */
    protected function bindValues(PDOStatement $statement, $binder)
    {
        if (is_null($binder)) {
            return;
        }

        if (is_array($binder)) {
            $binder = function (PDOStatement $statement) use ($binder) {
                foreach ($binder as $key => $value) {
                    $statement->bindValue(
                        is_string($key) ? $key : $key + 1, $value,
                        is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
                    );
                }
            };
        }

        $binder($statement);
    }
}