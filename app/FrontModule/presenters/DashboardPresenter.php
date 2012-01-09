<?php
class Front_DashboardPresenter extends Front_BasePresenter
{
    
    public function startup(){
        parent::startup();
        $this->baseModel = new BaseModel();
    }
    
    public function actionDefault(){
    }

    public function renderDefault(){
    }
    

}
?>