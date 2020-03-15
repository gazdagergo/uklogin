<?php
class DefaultController extends Controller {
	public function default(RequestObject $request) {
      // echo frontpage
	    $request->set('sessionid','0');
		$request->set('lng','hu');
		$view = $this->getView('frontpage');
		$model = $this->getModel('frontpage');
		$data = new stdClass(); //  $data = $model->getData(....);
		$data->option = $request->input('option','default');
		$data->appCount = $model->getAppCount();
		$data->userCount = $model->getUserCount();
		$data->adminNick = $request->sessionget('adminNick','');
		if ($request->sessionGet('user','') != '') {
		    $data->user = $request->sessionGet('user','');
		}
		$view->display($data);
	}
}
?>