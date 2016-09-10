<?php

class RedisCli
{
    const TIMEOUT = 20;
    const SLEEP_MICRO = 100000;

    private static $instance = null;

    public static function getInstance()
    {
        if (RedisCli::$instance == null) {
            RedisCli::$instance = new Redis();
            RedisCli::$instance->connect("127.0.0.1", "6379");
        }

        return RedisCli::$instance;
    }

    /**
     * Stores the expire time of the currently held lock
     * @var int
     */
    protected static $expire;

    /**
     * Gets a lock or waits for it to become available
     * @param mixed $key Item to lock
     * @param int $timeout Time to wait for the key (seconds)
     * @return mixed The key
     * @throws LockException If the key is invalid
     * @throws LockTimeoutException If the lock is not acquired before the method times out
     */
    public static function getLock($key, $timeout = null)
    {
        if (!$key) throw new LockException("Invalid Key");

        $start = time();

        do {
            self::$expire = self::timeout();
            if ($acquired = (RedisCli::getInstance()->setnx("lock.{$key}", self::$expire))) break;
            if ($acquired = (self::recover($key))) break;
            if ($timeout === 0) break;

            usleep(self::SLEEP_MICRO);
        } while (!is_numeric($timeout) || time() < $start + $timeout);

        if (!$acquired) throw new LockTimeoutException("Timeout exceeded");

        return $key;
    }

    /**
     * Releases the lock
     * @param mixed $key Item to lock
     * @throws LockException If the key is invalid
     */
    public static function releaseLock($key)
    {
        if (!$key) throw new LockException("Invalid Key");

        // Only release the lock if it hasn't expired
        if (self::$expire > time()) RedisCli::getInstance()->del("lock.{$key}");
    }

    /**
     * Generates an expire time based on the current time
     * @return int timeout
     */
    protected static function timeout()
    {
        return (int)(time() + self::TIMEOUT + 1);
    }

    /**
     * Recover an abandoned lock
     * @param mixed $key Item to lock
     * @return bool Was the lock acquired?
     */
    protected static function recover($key)
    {
        if (($lockTimeout = RedisCli::getInstance()->get("lock.{$key}")) > time()) return false;

        $timeout = self::timeout();
        $currentTimeout = RedisCli::getInstance()->getSet("lock.{$key}", $timeout);

        if ($currentTimeout != $lockTimeout) return false;

        self::$expire = $timeout;
        return true;
    }
}

class LockException extends RuntimeException
{
}

class LockTimeoutException extends LockException
{
}
