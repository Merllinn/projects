<?php
class Front_TasksPresenter extends Front_BasePresenter
{
    
    public $tasksModel = "";
    public $projectsModel = "";
    public $timesModel = "";
    private $editedTask = "";
    
    public $activeProject = "";
    
    
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
			$this->basics["activeProject"] = $id;
    	}
    	$this->template->activeProject = $this->basics["activeProject"];
    }

    public function actionAll(){
		$this->basics["activeProject"] = "";
    	$this->redirect("Tasks:default", array($this->basics["activeProject"]));
    }

    public function actionNew($id=""){
    	if($id<>""){
			$this->basics["activeProject"] = $id;
    	}
    	$this->template->activeProject = $this->basics["activeProject"];
    }
    
    public function renderDefault($id=""){
    	$tasks = $this->tasksModel->getTasks();
    	if($this->basics["activeProject"]){
			$tasks->where("[projects_id_projects]=%i", $this->basics["activeProject"]);
    	}
    	$this->template->tasks = $tasks->fetchAll();
    }
    
	public function actionEdit($id){
		$this->editedTask = $id;
	}
	
	public function handleDelete($id){
		 try{
			 $this->tasksModel->delete($id);
			 $this->flashMessage("Task deleted");
			 $this->redirect("this");
		 }
		 catch(DibiDriverException $e){
			 $this->flashMessage("Error deleting task! ".$e->getMessage(), "error");
		 }
	}
	
	public function handleStartTask($taskID){
		try{
			$data = array();
			$data['tasks_id_tasks'] = $taskID;
			$data['start_times'] = time();
			$this->timesModel->startTask($data);
			$this->redirect("this");
		}
		catch(DibiDriverException $e){
			$this->flashMessage("Error saving time! ".$e->getMessage(), "error");
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
	public function createComponentTaskForm(){
		$form = new NAppForm();
		$projects = $this->projectsModel->getProjects()->fetchPairs("id_projects", "name_projects");
		
		$form->addHidden("id_tasks");
		$form->addText("name_tasks", "Task name", "50");
        if($this->editedTask<>""){
			$form->addSelect("status_tasks", "Task status", $this->taskStatus);
        }
		$form->addText("estimate_tasks", "Estimate time");
		$form->addSelect("projects_id_projects", "Project name", $projects);
		$form->addTextArea("description_tasks", "Task description");
		$form->addSubmit("submit", "Save task")
				->getControlPrototype()->class("submit");

        $form->onSuccess[] = callback($this, 'saveTask');

        if($this->editedTask<>""){
			$defaults = $this->tasksModel->getTask($this->editedTask);		
			$form->setDefaults($defaults);
        }
        elseif($this->basics["activeProject"]<>""){
			$defaults["projects_id_projects"] = $this->basics["activeProject"];
			$form->setDefaults($defaults);
        }

        return $form;
	}

	public function createComponentChangeProject(){
		$form = new NAppForm();
		$projects = $this->projectsModel->getProjects()->fetchPairs("id_projects", "name_projects");
		
		$form->addSelect("project", "", $projects)
				->setPrompt('all projects')
				->getControlPrototype()->onchange("submit();");
        $form->onSuccess[] = callback($this, 'changeProject');

		$defaults["project"] = $this->basics["activeProject"];
		$form->setDefaults($defaults);

        return $form;
	}
	
	/** FORMS CALLBACKS */
	
	public function saveTask(NAppForm $form){
		$values = $form->getValues();
		//convert time string to minutes
		if(strpos($values['estimate_tasks'], "h")!==false){
			$values['estimate_tasks'] = ceil(floatval(str_replace("h", ".", $values['estimate_tasks'])) * 60);
		}
		
		if($values->id_tasks == ""){ //add new tak
			 $values['created_tasks'] = date("Y-m-d H:i:s");
			 try{
				 $this->tasksModel->add($values);
				 $this->recalculateProject($values['projects_id_projects']);
				 $this->flashMessage("Task added");
				 $this->redirect("Tasks:default", array($this->basics["activeProject"]));
			 }
			 catch(DibiDriverException $e){
				 $this->flashMessage("Error adding task! ".$e->getMessage(), "error");
			 }
		}
		else{ //save existing
			$id = $values['id_tasks'];
			unset($values['id_tasks']);
			 try{
				 $this->tasksModel->save($values, $id);
				 $this->recalculateProject($values['projects_id_projects']);
				 $this->flashMessage("Task saved");
				 $this->redirect("Tasks:default", array($this->basics["activeProject"]));
			 }
			 catch(DibiDriverException $e){
				 $this->flashMessage("Error saving task! ".$e->getMessage(), "error");
			 }
		}
	}


	public function changeProject(NAppForm $form){
		$values = $form->getValues();
		if($values['project']==""){
			unset($this->basics["activeProject"]);
			$this->redirect("Tasks:all");
		}
		else{
			$this->basics["activeProject"] = $values['project'];
			$this->redirect("Tasks:default", array($this->basics["activeProject"]));
		}
	}
}
?>
