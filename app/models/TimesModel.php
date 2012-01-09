<?php
class TimesModel extends BaseModel
{

	/** get times resource */
	public function getTimes(){
		return dibi::select("*")
				->from("[times]");
	}

     /** Get one time */
     public function getTime($id){
		 return dibi::fetch("select [*] from [times] where [id_times] =%i", $id);
     }
     
     /** Add new time */
     public function add($data){
		 return dibi::query("insert into [times] ", (array)$data);
     }

     /** Save existing time */
     public function save($data, $id){
		 return dibi::query("update [times] set", (array)$data, "where [id_times] =%i", $id);
     }
     
     /** delete existing time */
     public function delete($id){
		 return dibi::query("delete from [times] where [id_times]=%i", $id);
     }
	
	/** save task start to database */
	public function startTask($data){
		return dibi::query("insert into [times] ", (array)$data);
	}

	/** save task end to database */
	public function endTask($id, $end){
		$startTime = dibi::fetchSingle("select [start_times] from [times] where [id_times] = %i", $id);
		return dibi::query("update [times] set [end_times] = %i", $end, ", [duration_times]=%i", round((time() - $startTime)/60) ,"where [id_times] = %i", $id);
	}
	
	/** get running task */
	public function getRunningTask(){
		return dibi::fetch("select [tasks_id_tasks], [start_times], [id_times] from [times] where [end_times] is null");
	}
}
?>