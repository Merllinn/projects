<?php
class Front_TimesPresenter extends Front_BasePresenter
{
    
    public $tasksModel = "";
    public $projectsModel = "";
    public $timesModel = "";
    private $editedTime = "";
    
    public $activeProject = "";
    public $activeTask = "";
    
    
    public function startup(){
        parent::startup();
        $this->tasksModel = new TasksModel();
        $this->projectsModel = new ProjectsModel();
        $this->timesModel = new TimesModel();
        
        $runningTask = $this->timesModel->getRunningTask();
        if(isset($runningTask['tasks_id_tasks'])){
			$this->template->runningTask = $runningTask['tasks_id_tasks'];
			$this->template->runningTime = $runningTask['id_times'];
			$this->template->taskDuration = round((time() - $runningTask['start_times'])/60);
        }
    }
    
    public function actionDefault($id=""){
    	if($id<>""){
			$this->basics["activeTask"] = $id;
    	}
    	$this->template->activeTask = $this->basics["activeTask"];
    }

    public function actionAll(){
		$this->basics["activeTask"] = "";
    	$this->redirect("Times:default", array($this->basics["activeTask"]));
    }

    public function actionNew($id=""){
    	if($id<>""){
			$this->basics["activeTask"] = $id;
    	}
    	$this->template->activeTask = $this->basics["activeTask"];
    }
    
    public function renderDefault($id=""){
    	$times = $this->timesModel->getTimes();
    	if($this->basics["activeTask"]){
			$times->where("[tasks_id_tasks]=%i", $this->basics["activeTask"]);
    	}
    	$this->template->times = $times->fetchAll();
    }
    
	public function actionEdit($id){
		$this->editedTime = $id;
	}
	
	public function handleDelete($id){
		 try{
			 $this->timesModel->delete($id);
			 $this->flashMessage("Time deleted");
			 $this->redirect("this");
		 }
		 catch(DibiDriverException $e){
			 $this->flashMessage("Error deleting time! ".$e->getMessage(), "error");
		 }
	}

	public function handleEndTask($taskID, $timeID){
		try{
			$end = time();
			$this->timesModel->endTask($timeID, $end);
			$this->recalculateTask($taskID);
			$this->redirect("this");
		}
		catch(DibiDriverException $e){
			$this->flashMessage("Error saving time! ".$e->getMessage(), "error");
		}
	}
	
	/**  FORMS */
	public function createComponentTimeForm(){
		$form = new NAppForm();
		$tasks = $this->tasksModel->getTasks()->fetchPairs("id_tasks", "name_tasks");
		
		$form->addHidden("id_times");
		$form->addSelect("tasks_id_tasks", "Task name", $tasks);
		$form->addDatePicker('start_date', "Start")
				->addRule(NForm::FILLED, "Start date must be filled");
		$form->addText("start_time", "Start time", 6)
				->addRule(NForm::FILLED, "Start time must be filled")
				->getControlPrototype()->class("timePicker");
		$form->addDatePicker('end_date', "End")
				->addRule(NForm::FILLED, "End date must be filled");
		$form->addText("end_time", "End time", 6)
				->addRule(NForm::FILLED, "End time must be filled")
				->getControlPrototype()->class("timePicker");
		$form->addSubmit("submit", "Save time")
				->getControlPrototype()->class("submit");

        $form->onSuccess[] = callback($this, 'saveTime');

        if($this->editedTime<>""){
			$defaults = $this->timesModel->getTime($this->editedTime);	
			$defaults["start_date"]	= date("Y-m-d", $defaults["start_times"]);
			$defaults["start_time"]	= date("h:i", $defaults["start_times"]);
			$defaults["end_date"]	= date("Y-m-d", $defaults["end_times"]);
			$defaults["end_time"]	= date("h:i", $defaults["end_times"]);
			$form->setDefaults($defaults);
        }
        elseif($this->basics["activeTask"]<>""){
			$defaults["tasks_id_tasks"] = $this->basics["activeTask"];
			$form->setDefaults($defaults);
        }

        return $form;
	}

	public function createComponentChangeTask(){
		$form = new NAppForm();
		$tasks = $this->tasksModel->getTasks()->fetchPairs("id_tasks", "name_tasks");
		
		$form->addSelect("task", "", $tasks)
				->setPrompt('all tasks')
				->getControlPrototype()->onchange("submit();");
        $form->onSuccess[] = callback($this, 'changeTask');

		$defaults["task"] = $this->basics["activeTask"];
		$form->setDefaults($defaults);

        return $form;
	}
	
	/** FORMS CALLBACKS */
	
	public function saveTime(NAppForm $form){
		$values = $form->getValues();
		$data = array();
		$data['tasks_id_tasks'] = $values['tasks_id_tasks'];
		//conversion to correct datetime
		$data['start_times'] = $this->toTimestamp($values['start_date'], $values['start_time']);
		$data['end_times'] = $this->toTimestamp($values['end_date'], $values['end_time']);
		//conversion end
		$data['duration_times'] = round(($data['end_times'] - $data['start_times'])/60);
		
		if($values->id_times == ""){ //add new time
			 try{
				 $this->timesModel->add($data);
				 $this->recalculateTask($values['tasks_id_tasks']);
				 $this->flashMessage("Time added");
				 $this->redirect("Times:default", array($this->basics["activeTask"]));
			 }
			 catch(DibiDriverException $e){
				 $this->flashMessage("Error adding time! ".$e->getMessage(), "error");
			 }
		}
		else{ //save existing
			$id = $values['id_times'];
			unset($values['id_times']);
			 try{
				 $this->timesModel->save($data, $id);
				 $this->recalculateTask($values['tasks_id_tasks']);
				 $this->flashMessage("Time saved");
				 $this->redirect("Times:default", array($this->basics["activeTask"]));
			 }
			 catch(DibiDriverException $e){
				 $this->flashMessage("Error saving time! ".$e->getMessage(), "error");
			 }
		}
	}


	public function changeTask(NAppForm $form){
		$values = $form->getValues();
		if($values['task']==""){
			unset($this->basics["activeTask"]);
			$this->redirect("Times:all");
		}
		else{
			$this->basics["activeTask"] = $values['task'];
			$this->redirect("Times:default", array($this->basics["activeTask"]));
		}
	}
}
?>
