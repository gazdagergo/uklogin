<?php
class AdomanyController {
	public function show($request) {
	    $request->set('sessionid','0');
		$request->set('lng','hu');
		$view = getView('adomany');
		$data = new stdClass(); 
		$data->option = $request->input('option','default');
		if ($request->sessionGet('user','') != '') {
		    $data->user = $request->sessionGet('user','');
		}
		$view->display($data);
	}
}
?>