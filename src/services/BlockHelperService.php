<?php


namespace prometeusweb\craftcontentbuildertwighelpers\services;

use craft\base\Component;

class BlockHelperService extends Component
{

    private $alreadyBeenUsedRegistry = [];
    private $counterRegistry = [];

	/**
	 * Gets the number of time the function is called.
	 * @param $id
	 *
	 * @return int The numer of times the function has been called
	 */
    public function getUsageCount($hash)
    {
    	if(isset($this->counterRegistry[$hash])){
		    $currentCount = $this->counterRegistry[$hash];
		    $this->counterRegistry[$hash]++;
		    return $currentCount;
	    }
	    return $this->counterRegistry[$hash] = 1;
    }

 /*   public function setAccumulator($item)
    {
        $this->accumulator .= $item;
    }

    public function getAccumulator()
    {
        
    }*/

	public function alreadyBeenUsed($variable)
	{
		if(!isset($this->alreadyBeenUsedRegistry[$variable]) || $this->alreadyBeenUsedRegistry[$variable] === null){
			$this->alreadyBeenUsedRegistry[$variable] = true;
			return false;
		}

		return true;
	}

}