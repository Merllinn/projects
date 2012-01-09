<?php                                                                                      

/**
 * My NApplication
 *
 * @copyright  Copyright (c) 2011 Tomas hubicka
 */
abstract class Front_BasePresenter extends NPresenter
{
	
	public $baseModel = "";
	public $projectStatus = array("0"=>"not realised", "1"=>"prospect", "2"=>"project", "3"=>"running", "10"=>"finished", "90"=>"closed", "99"=>"cancelled");
	public $taskStatus = array("1"=>"new", "2"=>"running", "3"=>"finished", "4"=>"closed", "5"=>"reopened");
	public $basics = "";
	
	
	public function startup(){
		parent::startup();
		$this->baseModel = new BaseModel();
		$this->template->projectStatus = $this->projectStatus;
		$this->template->taskStatus = $this->taskStatus;
		$this->basics = $this->getSession("basics");
		$this->template->registerHelper('dayHourMinute', function ($s, $dayDelim = ":", $hourDelim = ":") {
		    $days = floor($s/(60*24));
		    $afterDays = $s%(60*24);
		    $hours = floor($afterDays/60);
		    $minutes = $afterDays%60;
		    $time = "";
		    if($days>0){
		    	$time.=$days.$dayDelim;
		    }
		    if($hours>0||$days>0){
		    	$time.=$hours.$hourDelim;
		    }
		    if($minutes>0||$hours>0||$days>0){
		    	$time.=$minutes;
		    }
		    return $time;
		});		
	}
	
	
	/* PUBLIC TOOL METHODS */
	/** recalculate project */
	public function recalculateProject($id){
        try{
			$this->baseModel->recalculateProject($id);
        }
        catch(DibiDriverException $e){
			$this->flashMessage("Error recalculating project! ".$e->getMessage(), "error");
        }
	}

	/** recalculate task */
	public function recalculateTask($id){
        try{
	        $this->baseModel->recalculateTask($id);
			$project = $this->baseModel->getTaskProject($id);
			$this->recalculateProject($project);
        }
        catch(DibiDriverException $e){
			$this->flashMessage("Error recalculating task! ".$e->getMessage(), "error");
        }
		
	}
	
	/** convert date and time to MySQL DATETIME */
	public function toDatetime($date, $time){
		$date = $date->format("d-m-Y");
		$time = $time.":00";
		return $date." ".$time;
	}

	/** convert date and time to timestamp */
	public function toTimestamp($date, $time){
		$date = $date->getTimestamp();
		$time = $time.":00";
		$timeArr = explode(":", $time);
		$newTime = $timeArr[0]*(60*60) + $timeArr[1]*60;
		return $date+$newTime;
	}
}
?>
