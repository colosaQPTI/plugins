 <?php 
  try {  	

    $form = $_POST['form'];
    $UsrUsername = $form['USR_USERNAME'];

  require_once ( PATH_PLUGINS . 'elock' . PATH_SEP . 'class.elock.php');
  $pluginObj = new elockClass ();

    require_once ( "classes/model/ElockUsers.php" ); 
 
    //if exists the row in the database propel will update it, otherwise will insert.
    $tr = ElockUsersPeer::retrieveByPK( $UsrUsername );
    if ( ( is_object ( $tr ) &&  get_class ($tr) == 'ElockUsers' ) ) {
      $tr->delete();
    }

    G::Header('location: elockUsersList');   
  
  }
  catch ( Exception $e ) {
    $G_PUBLISH = new Publisher;
    $aMessage['MESSAGE'] = $e->getMessage();
    $G_PUBLISH->AddContent('xmlform', 'xmlform', 'login/showMessage', '', $aMessage );
    G::RenderPage( 'publish', 'blank' );
  }      
   