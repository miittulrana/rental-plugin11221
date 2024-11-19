<?php
/**
 * Message stack
 * Abstract class must be inherited later.
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models;

abstract class AbstractStack
{
    protected $debugMessages = array();
    protected $okayMessages = array();
    protected $errorMessages = array();

    public function flushMessages()
    {
        $this->debugMessages = array();
        $this->okayMessages = array();
        $this->errorMessages = array();
    }

    public function getAllMessages()
    {
        return array(
            'debug' => $this->debugMessages,
            'okay' => $this->okayMessages,
            'error' => $this->errorMessages,
        );
    }

    public function getDebugMessages()
    {
        return $this->debugMessages;
    }

    public function getOkayMessages()
    {
        return $this->okayMessages;
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }
}