<?php
ob_start();

$request = isset($_POST['request'])? $_POST['request'] : (isset($_GET['request'])? $_GET['request'] : null);
$nameDoublonnage = 'Duplicate Rules';
if(SYS_LANG == 'fr')
	$nameDoublonnage = 'D&eacute;doublonnage Rules';
switch ($request) {
  case 'loadMenu':
    if (!isset($_GET['menu'])) {
      exit(0);
    }

    global $G_TMP_MENU;
    
    $oMenu = new Menu();
    $items = array();
      $items[] = array(
          'id'  => 'ID_DOUBLONB',
          'url' => '../ProductionAS400/productionAS',
          'text' => 'Production',
          'loaded'  => true,
          'leaf'    => true,
          'cls'     => 'pm-tree-node',
          'iconCls' => 'ICON_INBOX' 
        );
        $items[] = array(
          'id'  => 'ID_DOUBLORULES',
          'url' => '../ProductionAS400/dedoublonageRules',
          'text' => $nameDoublonnage,
          'loaded'  => true,
          'leaf'    => true,
          'cls'     => 'pm-tree-node',
          'iconCls' => 'ICON_DEDOUBLO' 
        );
	$x = ob_get_contents();
    ob_end_clean();
   
    
    ///////
    echo G::json_encode($items);
  break;
}
?>