<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */

/**
 * frontPage model
 * @author utopszkij
 *
 */
class FrontpageModel {
   
    /**
     * applikációk számának lekérése
     * @return int
     */
    public function getAppCount(): int {
        $table = new table('apps');
        return $table->count();
    }
    
    /**
     * regisztrált user fiokok számának lekérése
     * @return int
     */
    public function getUserCount(): int {
        $table = new table('oi_users');
        return $table->count();
    }
    
  
} // class
?>