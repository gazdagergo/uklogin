<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */

/** AdatkezelesController  adatkezelési leírás megjelenitése */
class AdatkezelesController extends Controller {
    
    /**
     * adatkezelési leírás megjelenítése
     * @param Request $request
     */
	public function show(Request $request) {
	    $this->docPage($request, 'adatkezeles');
	}
}
?>