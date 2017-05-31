<?php

class StatelessAuthorization {

    /**
     * @example . 'randomStr1ng:LJHJJsdfsgsddfgDfHGFHFD'
     */
    private $secret = '';
    private $dateFormat = 'YmdHis';
    private $sessionIdLifetimeInSecs = 60 * 60;


    public function __construct($secret, $sessionIdLifetimeInSecs = null) {
        if (!$secret || !is_string($secret)) {
            throw new InvalidArgumentException('A string for "secret" should be provided');
        }
        if (strlen($secret) < 10) {
            throw new LengthException('A string for "secret" should be of length at least 10');
        }
        $this->secret = $secret;

        if ((int) $sessionIdLifetimeInSecs) {
            $this->sessionIdLifetimeInSecs = (int) $sessionIdLifetimeInSecs;
        }
    }


    public function check($sessionId) {
        $timeStrLength = strlen(date($this->dateFormat));
        $timeStr = substr($sessionId, 0, $timeStrLength);
        $startTime = $this->timeFromTimeStr($timeStr);
        $now = time();

        if (($now - $startTime) > $this->sessionIdLifetimeInSecs) {
            return false;
        }
        if ($now < $startTime /*suspicious date! Date in future.*/) {
            return false;
        }

        $hash = substr($sessionId, $timeStrLength);
        $validHash = $this->generateHash($startTime);
        return $hash === $validHash;
    }


    private function generateHash($time) {
        $strToHash = $this->timeToTimeStr($time) . $this->secret;
        $newHash = sha1($strToHash);
        return $newHash;
    }


    public function getSessionId() {
        $now = time();
        return $this->timeToTimeStr($now) . $this->generateHash($now);
    }


    public function getSessionIdLifetime() {
        return $this->sessionIdLifetimeInSecs;
    }

    
    /**
    * @todo: what did i do this for?;) We don't need time formatting at all...
    */
    private function timeToTimeStr($time) {
        return date($this->dateFormat, $time);
    }

    private function timeFromTimeStr($timeStr) {
        return DateTime::createFromFormat($this->dateFormat, $timeStr)->getTimestamp();
    }

}
