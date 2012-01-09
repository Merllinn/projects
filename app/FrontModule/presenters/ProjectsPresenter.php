<?php
class Front_ProjectsPresenter extends Front_BasePresenter
{
    
    public $projectsModel = "";
    private $editedProject = "";
    
    public function startup(){
        parent::startup();
        $this->projectsModel = new ProjectsModel();
    }
    
    public function actionDefault(){
    
    }

    public function renderDefault(){
    	$projects = $this->projectsModel->getProjects();
    	// where
    	$statuses = array(1=>1, 2=>1, 3=>1, 10=>1);
    	$status_cond = array();
    	foreach($statuses as $key=>$val){
			if($val==1){
				$status_cond[] = "status_projects = '$key'";
			}
    	}
    	$projects->where(implode(" or ", $status_cond));
    	$this->template->projects = $projects->fetchAll();
    }
    
	public function actionEdit($id){
		$this->editedProject = $id;
	}

	public function handleDelete($id){
		 try{
			 $this->projectsModel->delete($id);
			 $this->flashMessage("Project deleted");
			 $this->redirect("this");
		 }
		 catch(DibiDriverException $e){
			 $this->flashMessage("Error deleting project! ".$e->getMessage(), "error");
		 }
	}

	public function handleFinish($id){
		 try{
			 $this->projectsModel->save(array('status_projects'=>'10'), $id);
			 $this->flashMessage("Project finished");
			 $this->redirect("this");
		 }
		 catch(DibiDriverException $e){
			 $this->flashMessage("Error finishing project! ".$e->getMessage(), "error");
		 }
	}
    
	public function handleClose($id){
		 try{
			 $this->projectsModel->save(array('status_projects'=>'90'), $id);
			 $this->flashMessage("Project closed");
			 $this->redirect("this");
		 }
		 catch(DibiDriverException $e){
			 $this->flashMessage("Error closing project! ".$e->getMessage(), "error");
		 }
	}
    

	/**  FORMS */
	public function createComponentProjectForm(){
		$form = new NAppForm();
		
		$form->addHidden("id_projects");
		$form->addText("name_projects", "Project name", "70");
		$form->addTextArea("description_projects", "Project description");
		$form->addText("budget_projects", "Project budget");
		$form->addText("hourprice_projects", "Planned hour price");
		$form->addSubmit("submit", "Save project")
				->getControlPrototype()->class("submit");

        $form->onSuccess[] = callback($this, 'saveProject');

        if($this->editedProject<>""){
			$defaults = $this->projectsModel->getProject($this->editedProject);		
			$form->setDefaults($defaults);
        }

        return $form;
	}
	
	/** FORMS CALLBACKS */
	
	public function saveProject(NAppForm $form){
		$values = $form->getValues();
		if($values->id_projects == ""){ //add new project
			 $values['created_projects'] = date("Y-m-d H:i:s");
			 try{
				 $this->projectsModel->add($values);
				 $this->flashMessage("Project added");
				 $this->redirect("Projects:default");
			 }
			 catch(DibiDriverException $e){
				 $this->flashMessage("Error adding project! ".$e->getMessage(), "error");
			 }
		}
		else{ //save existing
			$id = $values['id_projects'];
			unset($values['id_projects']);
			 try{
				 $this->projectsModel->save($values, $id);
				 $this->flashMessage("Project saved");
				 $this->redirect("Projects:default");
			 }
			 catch(DibiDriverException $e){
				 $this->flashMessage("Error saving project! ".$e->getMessage(), "error");
			 }
		}
	}
}
?>