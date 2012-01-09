<?php
class ProjectsModel extends BaseModel
{
    /** Get dataset of all projects */ 
    public function getProjects(){
		 return dibi::select("*")
		 		->from("projects");
     }
     
     /** Get one prject */
     public function getProject($id){
		 return dibi::fetch("select [*] from [projects] where [id_projects] =%i", $id);
     }
     
     /** Add new project */
     public function add($data){
		 return dibi::query("insert into [projects] ", (array)$data);
     }

     /** Save existing project */
     public function save($data, $id){
		 return dibi::query("update [projects] set", (array)$data, "where [id_projects] =%i", $id);
     }

     /** delete existing project */
     public function delete($id){
		 return dibi::query("delete from [projects] where [id_projects]=%i", $id);
     }

}
?>