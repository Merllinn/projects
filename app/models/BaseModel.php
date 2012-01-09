<?php
class BaseModel extends NObject
{
 	/** get task project */
 	public function getTaskProject($id){
		return dibi::fetchSingle("select [projects_id_projects] from [tasks] where [id_tasks]=%i", $id);
 	}    
 	
 	/** recalculate project */
 	public function recalculateProject($id){
		return dibi::query("update [projects] set [estimate_projects]=(select sum([estimate_tasks]) from [tasks] where [projects_id_projects]=%i", $id, "), [done_projects]=(select sum([done_tasks]) from [tasks] where [projects_id_projects]=%i", $id, ") where [id_projects]=%i", $id);
 	}    
 	
 	/** recalculate task */
 	public function recalculateTask($id){
		return dibi::query("update [tasks] set [done_tasks]=(select sum([duration_times]) from [times] where [tasks_id_tasks]=%i", $id, ") where [id_tasks]=%i", $id);
 	}    
}
?>