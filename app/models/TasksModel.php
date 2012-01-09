<?php
class TasksModel extends BaseModel
{
    /** Get dataset of all tasks */ 
    public function getTasks(){
		 return dibi::select("*")
		 		->from("tasks")
		 		->orderBy("[id_tasks] desc");
     }
     
     /** Get one task */
     public function getTask($id){
		 return dibi::fetch("select [*] from [tasks] where [id_tasks] =%i", $id);
     }
     
     /** Add new task */
     public function add($data){
		 return dibi::query("insert into [tasks] ", (array)$data);
     }

     /** Save existing task */
     public function save($data, $id){
		 return dibi::query("update [tasks] set", (array)$data, "where [id_tasks] =%i", $id);
     }
     
     /** delete existing task */
     public function delete($id){
		 return dibi::query("delete from [tasks] where [id_tasks]=%i", $id);
     }
}
?>