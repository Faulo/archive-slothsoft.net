<?php
namespace Slothsoft\Core\Lambda;

use Threaded;
use Exception;

abstract class Stackable extends Threaded
{

    private $_result;

    private $_log = '';

    private $hasRun = false;

    public function run()
    {
        ob_start();
        try {
            $this->_result = (array) $this->work();
        } catch (Exception $e) {
            $this->log('Exception?' . PHP_EOL . $e->getMessage());
        }
        $error = ob_get_contents();
        if (strlen($error)) {
            $this->log('Error?' . PHP_EOL . $error);
        }
        ob_end_clean();
        $this->hasRun = true;
    }

    public function isDone()
    {
        return (! $this->isRunning() and $this->hasRun);
    }

    public function getResult()
    {
        return (array) $this->_result;
    }

    public function getLog()
    {
        return $this->_log;
    }

    protected function log($message)
    {
        $this->_log .= sprintf('[%s] %s%s', date(DATE_DATETIME), $message, PHP_EOL);
    }

    abstract protected function work();
}