<?php
namespace Slothsoft\Core\Lambda;

use Pool as PThreadsPool;
use Threaded;

class Pool extends PThreadsPool
{

    const FETCHING_UTIME = 1000;

    const STATUS_RUNNING = 1;

    const STATUS_TERMINATED = 2;

    const STATUS_WAITING = 4;

    const STATUS_DONE = 8;

    protected $_submittedWorkList = [];

    protected $_doneWorkList = [];

    public function submit(Threaded $work)
    {
        if ($work instanceof Stackable) {
            $this->_submittedWorkList[] = &$work;
            return parent::submit($work);
        }
    }

    public function submitList(array $workList)
    {
        foreach ($workList as &$work) {
            $this->submit($work);
        }
        unset($work);
    }

    public function getWorkList($status = null)
    {
        if (! $status) {
            return $this->_submittedWorkList;
        }
        $retList = [];
        foreach ($this->_submittedWorkList as $i => &$work) {
            $add = false;
            if ($status & self::STATUS_RUNNING) {
                $add |= $work->isRunning();
            }
            if ($status & self::STATUS_TERMINATED) {
                $add |= $work->isTerminated();
            }
            if ($status & self::STATUS_WAITING) {
                $add |= $work->isWaiting();
            }
            if ($status & self::STATUS_DONE) {
                $add |= $work->isDone();
            }
            if ($add) {
                $retList[$i] = &$work;
            }
        }
        unset($work);
        return $retList;
    }

    public function hasUnfetchedWork()
    {
        return (bool) count($this->_submittedWorkList);
    }

    public function fetchUnfetchedWork($wait = false)
    {
        $ret = null;
        while (! count($this->_doneWorkList)) {
            // $this->collect(Closure::fromCallable[$this, 'collectCallback']); //PHP 7.1
            $this->collect(function ($work) {
                $this->collectCallback($work);
            });
            
            if (! $wait) {
                break;
            }
            usleep(self::FETCHING_UTIME);
        }
        if (count($this->_doneWorkList)) {
            $work = array_shift($this->_doneWorkList);
            foreach ($this->_submittedWorkList as $i => $tmp) {
                if ($tmp === $work) {
                    $ret = &$work;
                    unset($this->_submittedWorkList[$i]);
                    break;
                }
            }
        }
        return $ret;
    }

    public function collectCallback($work)
    {
        $ret = false;
        if ($work->isDone()) {
            // my_dump(count($this->_doneWorkList));
            $this->_doneWorkList[] = &$work;
            $ret = true;
        }
        return $ret;
    }
}